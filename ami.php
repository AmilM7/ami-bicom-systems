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

use PAMI\Client\Impl\ClientImpl as PamiClient;
use PAMI\Message\Event\EventMessage;
use PAMI\Listener\IEventListener;
use PAMI\Message\Event\DialEvent;

$pamiClient = new PamiClient($pamiClientOptions);
$pamiClient->open();

$action = new \PAMI\Message\Action\CoreStatusAction();
$result = $pamiClient->send($action);
if (!$result->isSuccess()) {
    echo "Failed number of active calls";
}

$callsOnGoing = $result->getKeys()['corecurrentcalls'];
$callsOnGoing = (integer)$callsOnGoing;
$callsOnGoing /= 2;

$action2 = new \PAMI\Message\Action\numberOfUsers();
$result2 = $pamiClient->send($action2);
if (!$result2->isSuccess()) {
    echo "Failed number of users";
}

$registeredUsers = 0;
$activeUsers = 0;
$result2 = (array)$result2->getEvents();
foreach ($result2 as $key => $value) {
    $stepBelow = $value->getkeys();
    if (!empty($stepBelow['objectname'])) {
        $registeredUsers++;
    }
    if (!empty($stepBelow['contacts'])) {
        $activeUsers++;
    }
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PAMI applicatio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <link rel="stylesheet" href="PAMI.css">
</head>
<body>
<header>
    <i class="phone volume icon"></i>
    AMI APPLICATION
    <i class="phone volume icon"></i>
</header>
<div id="MainOne">
    <div>
        <h1 class="ui header">Dashboard</h1>
    </div>
    <main>
        <section>
            <h3 class="ui medium header">Number of users</h3>
            <p id="registeredUsers" class="ui large header addMargin"><?php echo $registeredUsers ?></p>
        </section>
        <section>
            <h3 class="ui medium header">Number of online users</h3>
            <p id="activeUser" class="ui large header addMargin"><?php echo $activeUsers ?></p>
        </section>
        <section>
            <h3 class="ui medium header">Ongoing calls</h3>
            <p id="calls" class="ui large header addMargin"><?php echo $callsOnGoing ?></p>
        </section>
    </main>
</div>
<footer>
    <i class="bosnia flag"></i>
    <p>Bicom systems - Sarajevo</p>
</footer>

<?php
$pamiClient->close();
?>

<script>
    var conn = new WebSocket('ws://localhost:8080');
    conn.onopen = function (e) {
        console.log("Connection established!");
    };

    var forCalls = 0;
    var forPersons = 0;

    conn.onmessage = function (e) {
        console.log(e.data);

        if (e.data == 'Call made') {
            forCalls++;
            if (forCalls % 2 == 0) {
                var p = document.getElementById('calls');
                var text = p.textContent;
                var number = Number(text);
                number++;
                p.innerHTML = number;
                forCalls = 0;
            }
        }
        if (e.data == 'Call finished') {
            forCalls++;
            if (forCalls % 2 == 0) {
                var p = document.getElementById('calls');
                var text = p.textContent;
                var number = Number(text);
                number--;
                p.innerHTML = number;
                forCalls = 0;
            }
        }
        if (e.data == 'User logged') {
            var p = document.getElementById('activeUser');
            var text = p.textContent;
            var number = Number(text);
            number++;
            p.innerHTML = number;
            forPersons = 0;
        }
        if (e.data == 'User left') {
            var p = document.getElementById('activeUser');
            var text = p.textContent;
            var number = Number(text);
            number--;
            p.innerHTML = number;
            forPersons = 0;
        }
    };
</script>
</body>
</html>
