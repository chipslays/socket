<?php

namespace Socket;

use Socket\Types\Connection;
use Socket\Types\Event;
use Workerman\Connection\TcpConnection;
use Workerman\Timer;

class Server
{
    protected array $events = [];

    public Groups $groups;

    public function __construct(protected Worker $worker)
    {
        $this->getWorker()->name = $this->getWorker()->name !== 'none' ?: 'Socket Server';
        $this->groups = new Groups;
    }

    public function getWorker(): Worker
    {
        return $this->worker;
    }

    public function onConnected(callable $handler): void
    {
        $this->getWorker()->onConnect = function (TcpConnection $connection) use ($handler) {
            $connection->groups = [];
            // присоединяемся в дефолтную группу
            // $this->groups->join('default', $connection);
            call_user_func_array($handler, [$connection]);
        };
    }

    public function onDisconnected(callable $handler): void
    {
        $this->getWorker()->onClose = function (TcpConnection $connection) use ($handler) {
            // выходим из всех групп
            $this->groups->leaveAll($connection);
            call_user_func_array($handler, [$connection]);
        };
    }

    public function onError(callable $handler): void
    {
        $this->getWorker()->onError = function (TcpConnection $connection, $code, $message) use ($handler) {
            call_user_func_array($handler, [$connection, $code, $message]);
        };
    }

    public function onStart(callable $handler): void
    {
        $this->getWorker()->onWorkerStart = $handler;
    }

    public function onStop(callable $handler): void
    {
        $this->getWorker()->onWorkerStop = $handler;
    }

    public function onReload(callable $handler): void
    {
        $this->getWorker()->onWorkerReload = $handler;
    }

    public function on(string $name, callable $handler)
    {
        $this->events[$name] = $handler;
    }

    public function to(TcpConnection|int $connection, string $name, array $data = [])
    {
        if (is_int($connection)) {
            $connection = $this->getWorker()->connections[$connection];
        }

        $event = new Event(compact('name', 'data'));
        $connection->send(json_encode($event));
    }

    public function broadcast(string $name, array $data = [], TcpConnection $except = null)
    {
        foreach ($this->worker->connections as $target) {
            if ($except && $target->id == $except->id) {
                continue;
            }

            $this->to($target, $name, $data);
        }
    }

    public function timer(int|float $interval, callable $handler, mixed $args = [], bool $persistent = true): int|bool
    {
        return Timer::add($interval, $handler, $args, $persistent);
    }

    public function start()
    {
        $worker = $this->getWorker();

        $worker->onMessage = function (TcpConnection $connection, string $data) {
            $event = new Event(json_decode($data, true));

            $event->connection = $connection;
            $event->data = $event->data ?? null;

            $handler = $this->events[$event->name] ?? null;

            if (!$handler) {
                return;
            }

            call_user_func_array($handler, [$connection, $event, $this]);
        };

        $worker->runAll();
    }

    public function stop()
    {
        $this->getWorker()->stopAll();
    }
}