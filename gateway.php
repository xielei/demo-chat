<?php

declare(strict_types=1);

use Xielei\Swoole\Gateway;

require_once __DIR__ . '/vendor/autoload.php';

$gateway = new Gateway();

$gateway->listen('127.0.0.1', 8000, [
    'open_websocket_protocol' => true,
    'open_websocket_close_frame' => true,

    'heartbeat_idle_time' => 60,
    'heartbeat_check_interval' => 3,
]);

$gateway->start();
