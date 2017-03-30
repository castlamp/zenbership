<?php

header('Content-Type: application/json');

$host = (! empty($_GET['host'])) ? $_GET['host'] : '';
$db = (! empty($_GET['db'])) ? $_GET['db'] : '';
$user = (! empty($_GET['user'])) ? $_GET['user'] : '';
$pass = (! empty($_GET['pass'])) ? $_GET['pass'] : '';

try {
    $DBH = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);

    // Strict mode conflicts?
    $error = false;

    $STH = $DBH->prepare("SELECT @@sql_mode");
    $result = $STH->execute();
    $array = $STH->fetch();

    if (is_array($array)) {
        $exp = explode(',', $array['@@sql_mode']);

        $problems = array(
            // 'NO_ENGINE_SUBSTITUTION',
            'NO_ZERO_DATE',
            'NO_ZERO_IN_DATE',
            'STRICT_TRANS_TABLES',
        );

        if (in_array($problems, $exp))
            $error = true;

        if ($error) {
            echo json_encode(array(
                'error' => true,
                'msg' => 'Your MySQL appears to be in STRICT mode. Please request that this be turned off with your web hosting provider or server administrator.',
            ));
        } else {
            echo json_encode(array(
                'error' => false,
                'msg' => true,
            ));
        }
    } else {
        echo json_encode(array(
            'error' => false,
            'msg' => json_encode($array),
        ));
    }
}
catch (PDOException $e) {
    echo json_encode(array(
        'error' => true,
        'msg' => $e->getMessage()
    ));
    exit;
}