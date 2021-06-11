<?php

declare(strict_types=1);

use Swoole\Timer;
use Xielei\Swoole\Api;

use Xielei\Swoole\Helper\WorkerEvent as HelperWorkerEvent;

class WorkerEvent extends HelperWorkerEvent
{
    public function onWorkerStart()
    {
        if ($this->worker->getServer()->worker_id == 0) {
            $this->xtimer = Timer::tick(5000, function () {
                Api::sendToAll(json_encode([
                    'event' => 'groupList',
                    'data' => $this->getGroupList(),
                ]));
            });
        }
    }

    public function onWorkerExit()
    {
        if (isset($this->xtimer)) {
            Timer::clear($this->xtimer);
        }
    }

    public function onConnect(string $client)
    {
        Api::sendToClient($client, json_encode([
            'event' => 'connect',
            'data' => $client,
        ]));
    }

    public function onReceive(string $client, string $data)
    {
        try {
            if (substr($data, 0, 1) !== '{') {
                return;
            }
            $data = json_decode($data, true);
            $event = $data['event'];
            $cmd = $data['cmd'];
            $param = $data['param'];
            switch ($cmd) {
                case 'getGroupListInfo':
                    $res = $this->getGroupList();
                    break;

                default:
                    $res = Api::$cmd(...$param);
                    break;
            }
            if ($event) {
                if ($res instanceof Iterator) {
                    $res = iterator_to_array($res);
                }
                Api::sendToClient($client, json_encode([
                    'event' => $event,
                    'data' => $res,
                ]));
            }
        } catch (\Throwable $th) {
            Api::sendToClient($client, json_encode([
                'event' => 'error',
                'data' => $th->getMessage(),
            ]));
        }
    }

    public function onClose(string $client, array $bind)
    {
        Api::sendToAll(json_encode([
            'event' => 'close',
            'data' => [
                'client' => $client,
                'group_list' => $bind['group_list'],
            ],
        ]));
    }

    private function getGroupList(): array
    {
        $res = [
            '大厅' => 0,
        ];
        foreach (Api::getGroupList(true) as $group) {
            $res[$group] = Api::getClientCountByGroup($group);
        }
        return $res;
    }
}
