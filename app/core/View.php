<?php

namespace App\Core;

class View
{
    public static function render(string $view, array $args = [])
    {
        extract($args, EXTR_SKIP);

        $dirAppBase = dirname(__DIR__);

        $file = "$dirAppBase/views/$view.php";

        if (is_readable($file)) {
            require_once $dirAppBase . "/views/base/header.php";
            require $file;
            require_once $dirAppBase . "/views/base/footer.php";
        } else {
            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found", true, 404);
            http_response_code(404);

            require_once $dirAppBase . "/views/error/404.php";
        }
    }
}