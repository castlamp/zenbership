<?php

header('Content-Type: application/json');

$host = (! empty($_GET['host'])) ? $_GET['host'] : '';
$db = (! empty($_GET['db'])) ? $_GET['db'] : '';
$user = (! empty($_GET['user'])) ? $_GET['user'] : '';
$pass = (! empty($_GET['pass'])) ? $_GET['pass'] : '';

try {
    $DBH = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);

    echo json_encode(array(
        'error' => false,
        'msg' => true,
    ));
}
catch (PDOException $e) {
    echo json_encode(array(
        'error' => true,
        'msg' => $e->getMessage()
    ));
    exit;
}