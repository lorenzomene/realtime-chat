<?php

namespace Lorenzomene\RealtimeChat;

use Exception;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface
{
    protected $clients;
    protected int $activeUserCount;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        echo "New connection ({$conn->resourceId})\n";

        $conn->user = null;
        $conn->country = null;
        $conn->personalConfig = null;
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        if ($from->user === null) {
            $setup = $this->processUserSetup($from, $msg);
            if (!$setup) {
                $from->send(
                    "Follow the error instructions for setting up your connection"
                );
            }
        }

        $success = $this->processMessage($from, $msg);
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

    private function processUserSetup(ConnectionInterface $conn, $msg): bool
    {
        $data = explode('|', $msg);

        if (count($data) !== 2) {
            $conn->send(
                "Error: your first message must be your user and country in the format: user|country"
            );
            return false;
        }

        $conn->user = trim($data[0]);
        $conn->country = trim($data[1]);

        $conn->send("Welcome {$conn->user} from {$conn->country}!\n");
        return true;
        //TODO: broadcast function between clients
    }

    private function processMessage(ConnectionInterface $conn, $msg): bool
    {
        $formattedMessage = "{$conn->user}({$conn->country}): {$msg}\n";
        //TODO: broadcast

        echo "Message from {$conn->user} sent";
        return true;
    }

    private function broadcastMessage()
    {
        $connectionsNumber = count($this->clients) - 1;
    }
}
