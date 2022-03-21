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

    public function create(string $name, array $data = []): self
    {
        $data['id'] = $name;
        $data['connections'] = $data['connections'] ?? [];
        $this->groups[$name] = $data;

        return $this;
    }

    public function delete(string $name): void
    {
        unset($this->groups[$name]);
    }

    public function exists(string $name): bool
    {
        return isset($this->groups[$name]);
    }

    public function join(string $name, TcpConnection $connection): self
    {
        $this->groups[$name]['connections'] = $this->groups[$name]['connections'] ?? [];
        $this->groups[$name]['connections'][$connection->id] = $connection;
        $this->groups[$name]['id'] = $name;
        $connection->groups[$name] = $name;

        return $this;
    }

    public function leave(string $name, TcpConnection $connection): self
    {
        unset($this->groups[$name]['connections'][$connection->id]);
        unset($connection->groups[$name]);

        return $this;
    }

    public function countBy(string $name): int
    {
        return count($this->groups[$name]['connections'] ?? []);
    }

    public function leaveAll(TcpConnection $connection): self
    {
        foreach ($connection->groups as $group) {
            $this->leave($group, $connection);
        }

        return $this;
    }

    public function group(string $name): Group
    {
        return new Group($this->groups[$name] ?? []);
    }

    public function set(string $name, string $key, mixed $value): void
    {
        $this->groups[$name][$key] = $value;
    }

    public function get(string $name, string $key, mixed $default = null): mixed
    {
        return $this->groups[$name][$key] ?? $default;
    }

    public function update(Group $group): void
    {
        foreach ($group->toArray() as $key => $value) {
            dump($key, $value);
            // $this->set($group->id, $key, $value);
        }
    }

    /**
     * Alias for `group` method.
     *
     * @param string $name
     * @return Group
     */
    public function to(string $name): Group
    {
        return $this->group($name);
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