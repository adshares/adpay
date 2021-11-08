<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
    return false;
} else {
    $_SERVER['SCRIPT_FILENAME'] = './public/index.php';
    include './public/index.php';
}
