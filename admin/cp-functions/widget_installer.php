<?php

// Load the basics
require "../sd-system/config.php";
$admin = new admin;
// Include the file
$file = PP_PATH . '/custom/widgets/install.php';
if (!file_exists($file)) {
    echo "0+++No installer file (install.php) found in the /custom/widgets/ directory.";
    exit;

}
require $file;
// Check permissions and employee
$task     = 'install_widget';
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $widget['id'], $employee['username']);
// Widget Exist?
$find = $db->get_array("
    SELECT COUNT(*)
    FROM `ppSD_widgets`
    WHERE `id`='" . $db->mysql_clean($widget['id']) . "'
");
// Widget already exists.
if ($find['0'] > 0) {
    echo "0+++Could not install widget: widget ID already exists.";
    exit;

} else {
    $widget_id = preg_replace("/[^A-Za-z0-9_]/", '', $widget['id']);
    $widget_id = str_replace(' ', '_', $widget_id);
    // Widget
    $in = $db->insert("
        INSERT INTO `ppSD_widgets` (
            `id`,
            `name`,
            `type`,
            `content`,
            `active`
        )
        VALUES (
            '" . $db->mysql_clean($widget_id) . "',
            '" . $db->mysql_clean($widget['name']) . "',
            '" . $db->mysql_clean($widget['type']) . "',
            '" . $db->mysql_clean($widget['content']) . "',
            '1'
        )
    ");
    // Options
    foreach ((array)$options as $item) {
        // Format ID.
        $id = preg_replace("/[^A-Za-z0-9_]/", '', $item['id']);
        $id = str_replace(' ', '_', $id);
        $id = substr(strtolower('wg_' . $widget_id . '_' . $id), 0, 50);
        // Make the option
        $make = $db->make_option($id, $item['name'], $item['type'], 'widgets', $item['value'], $item['description'], $item['width'], $item['maxlength'], $item['options']);

    }
    // Hooks
    foreach ((array)$hooks as $item) {
        $make = $db->make_hook($item['trigger'], $item['specific_trigger'], $item['type'], $item['data'], $item['when'], $widget_id);

    }
    // End the task.
    $task = $db->end_task($task_id, '1');
    // Delete installed.
    if (@unlink(PP_PATH . '/custom/widgets/install.php')) {
        echo "1+++Widget has been installed. ID <u>$widget_id</u>.";
        exit;

    } else {
        echo "1+++Widget has been installed. ID <u>$widget_id</u>. Please take a moment to delete the /custom/widgets/install.php file.";
        exit;

    }

}
