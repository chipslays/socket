<?php

namespace Socket\Types;

use Workerman\Connection\TcpConnection;

class Group
{
    public function __construct(protected array $group)
    {

    }

    public function count(): int
    {
        return count($this->group['connections']);
    }

    public function broadcast(string $name, array $data = [], TcpConnection $except = null)
    {
        foreach ($this->group['connections'] as $target) {
            if ($except && $target->id == $except->id) {
                continue;
            }

            $event = new Event(compact('name', 'data'));
            $target->send(json_encode($event));
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->group[$key] ?? $default;
    }
}