<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Env;
use App\Core\Logger;
use PDO;

/**
 * Advertising / partnership enquiries from the public contact form.
 *
 * The contact page is publicly cached, so spam protection is stateless: a honeypot field,
 * an HMAC-signed render timestamp (rejects bots that post instantly or replay old forms)
 * and a per-IP rate limit in the controller. No session cookie is set for visitors.
 */
class EnquiryService
{
    public const TYPES = [
        'banner' => 'Display banner advertising',
        'sponsor' => 'Price-card / section sponsorship',
        'widget' => 'Widget or API sponsorship',
        'content' => 'Sponsored article or listing',
        'partnership' => 'Business partnership',
        'other' => 'Something else',
    ];

    public const BUDGETS = [
        '' => 'Prefer not to say',
        'lt50k' => 'Under LKR 50,000 / month',
        '50-150k' => 'LKR 50,000 - 150,000 / month',
        '150-500k' => 'LKR 150,000 - 500,000 / month',
        'gt500k' => 'Over LKR 500,000 / month',
    ];

    private const MIN_FILL_SECONDS = 3;
    private const MAX_FORM_AGE_SECONDS = 86400;

    public static function formToken(?int $time = null): string
    {
        $time ??= time();
        return $time . '.' . self::sign((string) $time);
    }

    public static function tokenValid(string $token, ?int $now = null): bool
    {
        $now ??= time();
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2 || !ctype_digit($parts[0])) {
            return false;
        }
        if (!hash_equals(self::sign($parts[0]), $parts[1])) {
            return false;
        }
        $age = $now - (int) $parts[0];
        return $age >= self::MIN_FILL_SECONDS && $age <= self::MAX_FORM_AGE_SECONDS;
    }

    /**
     * @return array{data: array<string, string>, errors: array<string, string>}
     */
    public static function validate(array $input): array
    {
        $clean = static fn (string $key, int $max): string => mb_substr(trim(preg_replace('/\s+/u', ' ', (string) ($input[$key] ?? '')) ?? ''), 0, $max);

        $data = [
            'name' => $clean('name', 120),
            'email' => $clean('email', 190),
            'phone' => $clean('phone', 40),
            'company' => $clean('company', 160),
            'enquiry_type' => (string) ($input['enquiry_type'] ?? ''),
            'budget' => (string) ($input['budget'] ?? ''),
            'message' => mb_substr(trim(str_replace("\r\n", "\n", (string) ($input['message'] ?? ''))), 0, 3000),
        ];

        $errors = [];
        if (mb_strlen($data['name']) < 2) {
            $errors['name'] = 'Please enter your name.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if ($data['phone'] !== '' && !preg_match('/^\+?[0-9 ()-]{7,20}$/', $data['phone'])) {
            $errors['phone'] = 'Please enter a valid phone number.';
        }
        if (!array_key_exists($data['enquiry_type'], self::TYPES)) {
            $errors['enquiry_type'] = 'Please choose what you are interested in.';
        }
        if (!array_key_exists($data['budget'], self::BUDGETS)) {
            $data['budget'] = '';
        }
        if (mb_strlen($data['message']) < 20) {
            $errors['message'] = 'Please tell us a little more (at least 20 characters).';
        }

        return ['data' => $data, 'errors' => $errors];
    }

    public static function store(array $data, string $ip): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO ad_enquiries (name, email, phone, company, enquiry_type, budget, message, ip_hash)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'] ?: null,
            $data['company'] ?: null,
            $data['enquiry_type'],
            $data['budget'] ?: null,
            $data['message'],
            hash('sha256', $ip . '|' . self::key()),
        ]);
        return (int) $pdo->lastInsertId();
    }

    public static function notifyAdmin(array $data): void
    {
        $to = Env::get('CONTACT_EMAIL') ?: Env::get('ADMIN_EMAIL');
        $from = Env::get('MAIL_FROM');
        if (!$to || !$from) {
            return;
        }

        // Header-safe values only: validate() already collapsed whitespace, so no CR/LF can reach headers.
        $subject = '[' . Seo::siteName() . '] Advertising enquiry: ' . (self::TYPES[$data['enquiry_type']] ?? 'Other');
        $body = "New advertising enquiry\n\n"
            . 'Name: ' . $data['name'] . "\n"
            . 'Email: ' . $data['email'] . "\n"
            . 'Phone: ' . ($data['phone'] ?: '-') . "\n"
            . 'Company: ' . ($data['company'] ?: '-') . "\n"
            . 'Interest: ' . (self::TYPES[$data['enquiry_type']] ?? '-') . "\n"
            . 'Budget: ' . (self::BUDGETS[$data['budget']] ?? '-') . "\n\n"
            . $data['message'] . "\n\n"
            . 'Received: ' . date('c') . "\nView all enquiries at /admin";
        $headers = "From: {$from}\r\nReply-To: {$data['email']}\r\nContent-Type: text/plain; charset=UTF-8";

        try {
            @mail($to, $subject, $body, $headers);
        } catch (\Throwable $e) {
            Logger::warning('enquiry_mail_failed', ['error' => $e->getMessage()]);
        }
    }

    /** @return array<int, array<string, mixed>> */
    public static function recent(int $limit = 25): array
    {
        try {
            $stmt = Database::connection()->prepare('SELECT * FROM ad_enquiries ORDER BY id DESC LIMIT ?');
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Table missing until migrate.php is re-run after upgrading.
            return [];
        }
    }

    private static function sign(string $value): string
    {
        return substr(hash_hmac('sha256', 'enquiry|' . $value, self::key()), 0, 32);
    }

    private static function key(): string
    {
        $key = (string) Env::get('APP_KEY', '');
        if ($key === '') {
            // Fall back to something install-specific but not public.
            $key = hash('sha256', APP_DIR . '|' . (string) Env::get('DB_PASSWORD', '') . '|' . (string) Env::get('CRON_TOKEN', ''));
        }
        return $key;
    }
}
