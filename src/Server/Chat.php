<?php


namespace App\Server;


use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Chat implements \Ratchet\MessageComponentInterface
{

    private $clients;
    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }
    /**
     * @inheritDoc
     */
    function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $conn->send(sprintf('New connection: Hello #%d', $conn->resourceId));
    }

    /**
     * @inheritDoc
     */
    function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo sprintf('Connection #%d has disconnected\n', $conn->resourceId);
    }

    /**
     * @inheritDoc
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->send('An error has occurred: '.$e->getMessage());
        $conn->close();
    }

    /**
     * @inheritDoc
     */
    function onMessage(ConnectionInterface $from, $msg)
    {
        $totalClients = count($this->clients) - 1;
        echo vsprintf(
            'Connection #%1$d sending message "%2$s" to %3$d other connection%4$s'."\n", [
            $from->resourceId,
            $msg,
            $totalClients,
            $totalClients === 1 ? '' : 's'
        ]);
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($msg);
            }
        }
    }
}