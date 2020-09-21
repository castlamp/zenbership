<?php


/**
 * This is an experimental feature which can be used but
 * may or may not be placed into production at this time.
 *
 * Use with caution and at your own risk!
 */

$permission = 'report';
$check = $admin->check_permissions('report', $employee);
if ($check != '1') {
    $admin->show_no_permissions();
} else {
    $reports = new reports();
    $set = $reports->setReport($_GET['id']);

    if ($set) {
        $data = $reports->run()->getData();
        pa($data);
    } else {
        echo "Report invalid or does not exist";
    }

    exit;
}
