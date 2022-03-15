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

    public function create(string $name, array $data = []): void
    {
        $this->groups[$name] = $data;
    }

    public function delete(string $name): void
    {
        unset($this->groups[$name]);
    }

    public function join(string $name, TcpConnection $connection): self
    {
        $this->groups[$name]['connections'] = $this->groups[$name]['connections'] ?? [];
        $this->groups[$name]['connections'][$connection->id] = $connection;
        $connection->groups[$name] = $name;

        return $this;
    }

    public function leave(string $name, TcpConnection $connection): self
    {
        unset($this->groups[$name]['connections'][$connection->id]);
        unset($connection->groups[$name]);

        return $this;
    }

    public function countBy(string $room): int
    {
        return count($this->rooms[$room]['connections'] ?? []);
    }

    public function leaveAll(TcpConnection $connection): self
    {
        foreach ($connection->groups as $group) {
            $this->leave($group, $connection);
        }

        return $this;
    }

    public function room(string $name): Group
    {
        return new Group($this->groups[$name] ?? []);
    }

    public function set(string $name, string $key, mixed $value): void
    {
        $this->groups[$name][$key] = $value;
    }

    public function get(string $name, string $key, mixed $default): mixed
    {
        return $this->groups[$name][$key] ?? $default;
    }

    /**
     * Alias for `get` method.
     *
     * @param string $name
     * @return Group
     */
    public function to(string $name): Group
    {
        return $this->room($name);
    }

    public function all(): array
    {
        $result = [];

        foreach ($this->groups as $group) {
            $result[] = new Group($group);
        }

        return $result;
    }
}