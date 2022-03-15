# PHP Web Socket

Simple wrapper over Workerman.

# Installastion

```bash
composer require chipslays/socket
```

# Usage

```php
use Socket\Worker;
use Socket\Server;
use Socket\Terminal;
use Socket\Types\Event;
use Workerman\Connection\TcpConnection;

require_once __DIR__ . '/../vendor/autoload.php';

$worker = new Worker('websocket://0.0.0.0:2346');
$server = new Server($worker);

$server->onConnected(function (TcpConnection $connection) {
    Terminal::print('{green}Connected: ' . $connection->getRemoteAddress());
});

$server->onDisconnected(function (TcpConnection $connection) use ($server) {
    Terminal::print('{light_red}Disconnected: ' . $connection->getRemoteAddress());

    $server->broadcast('new chat message', [
        'nickname' => 'СИСТЕМА',
        'text' => "Пользователь {$connection->nickname} покинул чат.",
    ]);
});

$server->onError(function (TcpConnection $connection) {
    Terminal::print('Error: ' . $connection->getRemoteAddress());
});

$server->on('user joined to chat', function (TcpConnection $connection, Event $event, Server $server) {
    $connection->nickname = $event->data['nickname'];
    $server->broadcast('new chat message', [
        'nickname' => 'СИСТЕМА',
        'text' => "Пользователь {$connection->nickname} зашел в чат.",
    ]);
});

$server->on('new chat message', function (TcpConnection $connection, Event $event, Server $server) {
    if ($event->data['group'] ?? null) {
        $server->groups->to($event->data['group'])->broadcast('new chat message', [
            'nickname' => $connection->nickname,
            'text' => $event->data['text'],
        ]);
    } else {
        $server->broadcast('new chat message', [
            'nickname' => $connection->nickname,
            'text' => $event->data['text'],
        ]);
    }
});

$server->on('join group', function (TcpConnection $connection, Event $event, Server $server) {
    foreach ($connection->groups as $group) {
        $server->groups->to($group)->broadcast('new chat message', [
            'nickname' => 'СИСТЕМА',
            'text' => "{$connection->nickname} покинул группу.",
        ], $connection);
    }

    $server->groups->leaveAll($connection)->join($event->data['group'], $connection);

    $server->groups->to($event->data['group'])->broadcast('new chat message', [
        'nickname' => 'СИСТЕМА',
        'text' => "{$connection->nickname} присоединился к группе.",
    ]);
});

$server->onStart(function (Worker $worker) use ($server) {
    $server->timer(1, function () use ($server) {
        $server->broadcast('online', [
            'count' => count($server->getWorker()->connections),
        ]);
    });
});

$server->start();
```

See `client` code in [examples](/examples) folder.