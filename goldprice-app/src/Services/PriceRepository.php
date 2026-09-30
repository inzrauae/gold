<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class PriceRepository
{
    public function latest(): ?array
    {
        $stmt = Database::connection()->query('SELECT * FROM prices ORDER BY id DESC LIMIT 1');
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function insert(array $data): int
    {
        $pdo = Database::connection();
        $columns = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql = 'INSERT INTO prices (' . implode(', ', $columns) . ') VALUES (' . $placeholders . ')';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));
        return (int) $pdo->lastInsertId();
    }

    public function log(string $status, ?string $reason = null, array $details = []): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO update_log (status, reason, details_json) VALUES (?, ?, ?)'
        );
        $stmt->execute([$status, $reason, json_encode($details, JSON_UNESCAPED_SLASHES)]);
    }

    /** @return array<int, array<string, mixed>> */
    public function recentLogs(int $limit = 50): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM update_log ORDER BY id DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function upsertDailyHistory(string $date, array $row): void
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT id FROM daily_history WHERE date = ?');
        $stmt->execute([$date]);
        $existing = $stmt->fetch();

        if ($existing) {
            $upd = $pdo->prepare(
                'UPDATE daily_history SET spot_usd_per_oz = ?, usd_lkr = ?, price_24k_gram = ?, price_22k_gram = ?, price_21k_gram = ?, price_18k_gram = ? WHERE date = ?'
            );
            $upd->execute([
                $row['spot_usd_per_oz'], $row['usd_lkr'],
                $row['price_24k_gram'], $row['price_22k_gram'], $row['price_21k_gram'], $row['price_18k_gram'],
                $date,
            ]);
            return;
        }

        $ins = $pdo->prepare(
            'INSERT INTO daily_history (date, spot_usd_per_oz, usd_lkr, price_24k_gram, price_22k_gram, price_21k_gram, price_18k_gram, is_imported) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $ins->execute([
            $date, $row['spot_usd_per_oz'], $row['usd_lkr'],
            $row['price_24k_gram'], $row['price_22k_gram'], $row['price_21k_gram'], $row['price_18k_gram'],
            $row['is_imported'] ?? 0,
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public function historyBetween(string $from, string $to): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM daily_history WHERE date BETWEEN ? AND ? ORDER BY date ASC'
        );
        $stmt->execute([$from, $to]);
        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> */
    public function historyForMonth(int $year, int $month): array
    {
        $from = sprintf('%04d-%02d-01', $year, $month);
        $to = date('Y-m-t', strtotime($from));
        return $this->historyBetween($from, $to);
    }

    /** Most recent daily_history row strictly before the given date (handles weekends/holiday gaps). */
    public function previousDayHistory(string $beforeDate): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM daily_history WHERE date < ? ORDER BY date DESC LIMIT 1'
        );
        $stmt->execute([$beforeDate]);
        return $stmt->fetch() ?: null;
    }

    /** @return array<int, int> years that have at least one row, descending */
    public function historyYears(): array
    {
        $stmt = Database::connection()->query(
            "SELECT DISTINCT substr(date, 1, 4) AS y FROM daily_history ORDER BY y DESC"
        );
        return array_map('intval', array_column($stmt->fetchAll(), 'y'));
    }

    public function firstHistoryDate(): ?string
    {
        $stmt = Database::connection()->query('SELECT MIN(date) AS d FROM daily_history');
        $row = $stmt->fetch();
        return $row['d'] ?? null;
    }

    public function createPendingChange(string $reason, array $payload): int
    {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO pending_changes (payload_json, reason) VALUES (?, ?)');
        $stmt->execute([json_encode($payload, JSON_UNESCAPED_SLASHES), $reason]);
        return (int) $pdo->lastInsertId();
    }

    /** @return array<int, array<string, mixed>> */
    public function unresolvedPendingChanges(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM pending_changes WHERE resolved = 0 ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function resolvePendingChange(int $id): void
    {
        $stmt = Database::connection()->prepare('UPDATE pending_changes SET resolved = 1 WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM pending_changes WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
