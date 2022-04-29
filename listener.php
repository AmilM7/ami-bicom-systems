<!DOCTYPE html>
<html>
<body>

<?php
require 'vendor/autoload.php';
$pamiClientOptions = array(
    'host' => '127.0.0.1',
    'scheme' => 'tcp://',
    'port' => 5038,
    'username' => 'admin',
    'secret' => 'mysecret',
    'connect_timeout' => 10000,
    'read_timeout' => 10000
);

use WebSocket\Client;
use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Message\Event\EventMessage;
use PAMI\Listener\IEventListener;
use PAMI\Message\Event\DialEvent;

$pamiClient = new PamiClient($pamiClientOptions);
$pamiClient->open();
$clientWeb = new Client("ws://127.0.0.1:8080");

$pamiClient->registerEventListener(function (EventMessage $events) {
    global $clientWeb;

    if ($events->getKeys()['event'] == "Newchannel") {
        $clientWeb->send("Call made");
    }

    if (isset($events->getKeys()['contactstatus'])) {
        if ($events->getKeys()['contactstatus'] == 'Removed') {
            $clientWeb->send("User left");
        }

        if ($events->getKeys()['contactstatus'] == 'NonQualified') {
            $clientWeb->send("User logged");
        }
    }

    if ($events->getKeys()['event'] == "Hangup") {
        $clientWeb->send("Call finished");
    }
});
?>

</body>
</html>
<?php

$n = 0;
do {
    $pamiClient->process();
    sleep(2);
    $n++;
    if ($n == 1000) {
        break;
    }
} while (true);

$pamiClient->close();
?>

