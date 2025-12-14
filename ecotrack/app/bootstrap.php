<?php
require_once dirname(__DIR__) . '/config/config.php';

spl_autoload_register(function ($class) {
    $paths = [
        APP_ROOT . '/app/core/',
        APP_ROOT . '/app/models/',
        APP_ROOT . '/app/controllers/',
        APP_ROOT . '/app/helpers/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once APP_ROOT . '/app/core/Database.php';
require_once APP_ROOT . '/app/core/Model.php';
require_once APP_ROOT . '/app/core/Controller.php';
require_once APP_ROOT . '/app/core/Router.php';
require_once APP_ROOT . '/app/core/App.php';
