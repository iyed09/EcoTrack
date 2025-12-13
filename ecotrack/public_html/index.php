<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once dirname(__DIR__) . '/app/bootstrap.php';

$app = new App();
$app->run();
