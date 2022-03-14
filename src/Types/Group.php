<?php

namespace Socket\Types;

use Workerman\Connection\TcpConnection;

class Group
{
    public function __construct(protected array $connections)
    {

    }

    public function count(): int
    {
        return count($this->connections);
    }

    public function broadcast(string $name, array $data = [], TcpConnection $except = null)
    {
        foreach ($this->connections as $target) {
            if ($except && $target->id == $except->id) {
                continue;
            }

            $event = new Event(compact('name', 'data'));
            $target->send(json_encode($event));
        }
    }
}