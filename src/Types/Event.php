<?php

namespace Socket\Types;

use Workerman\Connection\TcpConnection;
use JsonSerializable;

class Event implements JsonSerializable
{
    public TcpConnection $connection;

    public function __construct(protected array $event = [])
    {

    }

    public function __get($property): mixed
    {
        return $this->event[$property] ?? null;
    }

    public function __set($property, $value): void
    {
        $this->event[$property] = $value;
    }

    public function __unset($property): void
    {
        unset($this->event[$property]);
    }

    public function __isset($property): bool
    {
        return isset($this->event[$property]);
    }

    public function only(array $properties) {
        $result = [];

        foreach ($properties as $property) {
            $result[$property] = $this->{$property};
        }

        return $result;
    }

    public function reply(string $name, array $data = [])
    {
        $event = new static(compact('name', 'data'));
        $this->connection->send(json_encode($event));
    }

    public function jsonSerialize(): mixed
    {
        return $this->event;
    }
}