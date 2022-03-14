<?php

namespace Socket;

use Socket\Types\Group;
use Workerman\Connection\TcpConnection;

class Groups
{
    protected array $groups = [];

    public function __construct()
    {

    }

    public function join(string $name, TcpConnection $connection): self
    {
        $this->groups[$name][$connection->id] = $connection;
        $connection->groups[$name] = $name;

        return $this;
    }

    public function leave(string $name, TcpConnection $connection): self
    {
        unset($this->groups[$name][$connection->id]);
        unset($connection->groups[$name]);

        return $this;
    }

    public function countBy(string $room): int
    {
        return count($this->rooms[$room] ?? []);
    }

    public function leaveAll(TcpConnection $connection): self
    {
        foreach ($connection->groups as $group) {
            $this->leave($group, $connection);
        }

        return $this;
    }

    public function to(string $name): Group
    {
        return new Group($this->groups[$name] ?? []);
    }
}