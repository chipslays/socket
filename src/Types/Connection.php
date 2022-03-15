<?php

namespace Socket\Types;

use Workerman\Connection\TcpConnection;

/**
 * @method int|string getStatus(bool $raw_output = true)
 * @method bool|null send(mixed $send_buffer, bool $raw = false)
 * @method string getRemoteIp()
 * @method int getRemotePort()
 * @method string getRemoteAddress()
 * @method string getLocalIp()
 * @method int getLocalPort()
 * @method string getLocalAddress()
 * @method int getSendBufferQueueSize()
 * @method int getRecvBufferQueueSize()()
 * @method bool isIpV4()
 * @method bool isIpV6()
 * @method void pauseRecv()
 * @method void baseRead(resource $socket, bool $check_eof = true)
 * @method void|bool baseWrite()
 * @method bool doSslHandshake(resource $socket)
 * @method void pipe(self $dest)
 * @method void consumeRecvBuffer(int $length)
 * @method void close(mixed $data = null, bool $raw = false)
 * @method resource getSocket()
 * @method void checkBufferWillFull()
 * @method bool bufferIsFull()
 * @method bool bufferIsEmpty()
 * @method void destroy()
 */
class Connection
{
    public function __construct(public TcpConnection $instance)
    {

    }

    public function __call(string $method, array $arguments = []): mixed
    {
        return call_user_func_array([$this->instance, $method], $arguments);
    }
}