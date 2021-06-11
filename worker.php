<?php

declare(strict_types=1);

use Xielei\Swoole\Worker;

require_once __DIR__ . '/vendor/autoload.php';

$worker = new Worker();

$worker->worker_file = __DIR__ . '/event_worker.php';

$worker->start();
