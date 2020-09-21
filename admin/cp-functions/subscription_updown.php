<?php


/**
 * Upgrades/downgrades a subscription based on the
 * product option, not the subscription package.
 */

require "../sd-system/config.php";

$admin = new admin;
$task  = 'sub_updown';

// Check permissions and employee
$employee = $admin->check_employee($task);

$updown = new subupdown($_POST['id'], $_POST['direction']);

if ($updown->error) {
    echo "0+++" . $updown->errorMsg;
    exit;
} else {
    $return = array(
        'show_saved' => 'Done. ' . $updown->charge['zen_order_id'],
        'refresh_slider' => '1',
    );

    echo "1+++" . json_encode($return);
    exit;
}
