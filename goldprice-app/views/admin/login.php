<?php
use App\Core\Csrf;
use App\Core\View;
/** @var string|null $error */
?>
<section class="wrap admin-login">
    <h1>Admin Login</h1>
    <?php if (!empty($error)): ?>
    <p class="alert alert-error"><?= View::e($error) ?></p>
    <?php endif; ?>
    <form method="post" action="/admin/login">
        <?= Csrf::field() ?>
        <label>Email <input type="email" name="email" required autofocus></label>
        <label>Password <input type="password" name="password" required></label>
        <button type="submit">Log in</button>
    </form>
</section>
