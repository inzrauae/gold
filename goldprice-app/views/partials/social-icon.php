<?php

use App\Core\View;

/** @var string $network Facebook | LinkedIn | Instagram */
/** @var string $url */

$paths = [
    'Facebook' => '<path d="M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v7h4v-7h3l1-4h-4V8.5c0-.3.2-.5.5-.5z" fill="currentColor" stroke="none"/>',
    'LinkedIn' => '<path d="M4 9h4v11H4zM6 3.5a2.2 2.2 0 1 1 0 4.4 2.2 2.2 0 0 1 0-4.4zM10 9h3.8v1.6c.6-1 1.9-1.9 3.8-1.9 3.4 0 4.4 2 4.4 5.4V20h-4v-5.2c0-1.5-.3-2.8-1.9-2.8-1.7 0-2.1 1.2-2.1 2.8V20h-4z" fill="currentColor" stroke="none"/>',
    'Instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>',
];
?>
<a class="social-link" href="<?= View::e($url) ?>" target="_blank" rel="noopener" aria-label="<?= View::e($network) ?>" title="<?= View::e($network) ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><?= $paths[$network] ?? '' ?></svg></a>
