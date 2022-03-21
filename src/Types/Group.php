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

    public function set(string $key, mixed $value): self
    {
        $this->group[$key] = $value;

        return $this;
    }

    public function __get(mixed $property): mixed
    {
        return $this->group[$property] ?? null;
    }

    public function __set(mixed $property, mixed $value): void
    {
        $this->group[$property] = $value;
    }

    public function toArray(): array
    {
        return $this->group;
    }
}