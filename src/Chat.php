<?php

namespace Lorenzomene\RealtimeChat;

use Exception;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        echo "New connection ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $connectionsNumber = count($this->clients) - 1;

        echo sprintf(
            'Connection %d sent message "%s" to %d other connections%s' . "\n",
            $from->resourceId,
            $msg,
            $connectionsNumber,
            $connectionsNumber == 1 ? '' : 's'
        );

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        echo "An error has occured: {$e->getMessage()}\n";

        $conn->close();
    }
}
