<?php

namespace App\Core;

class View
{
    public static function render(string $template, array $data = []): string
    {
        $file = APP_DIR . '/views/' . $template . '.php';
        if (!is_file($file)) {
            return "View not found: {$template}";
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return ob_get_clean();
    }

    public static function layout(string $template, array $data = [], string $layout = 'layout'): string
    {
        $content = self::render($template, $data);
        $data['content'] = $content;
        return self::render($layout, $data);
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
