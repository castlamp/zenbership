<?php


/**
 *
 *
 * Zenbership Membership Software
 * Copyright (C) 2013-2016 Castlamp, LLC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Castlamp
 * @link        http://www.castlamp.com/
 * @link        http://www.zenbership.com/
 * @copyright   (c) 2013-2016 Castlamp
 * @license     http://www.gnu.org/licenses/gpl-3.0.en.html
 * @project     Zenbership Membership Software
 */

// Load the basics
require "../sd-system/config.php";
$admin = new admin;

// Check permissions and employee
$task = 'uninstall_plugin';
$employee = $admin->check_employee($task);
$task_id = $db->start_task($task, 'staff', '', $employee['username']);

// Loop directory
$install = 0;
$success = '';
$errors = '';
$msgs = '';

$plugin_id = $_POST['id'];
$plugin_path = PP_PATH . '/custom/plugins/' .  $_POST['id'];
$check_path = $plugin_path . '/install.php';

if (file_exists($check_path)) {

    require $check_path;

    // Options
    foreach ( (array)$options as $item ) {

        $id = preg_replace("/[^A-Za-z0-9_]/", '', $item['id']);
        $id = str_replace(' ', '_', $id);
        $id = substr( strtolower('pg_' . $plugin_id . '_' . $id), 0, 50);

        $find = $db->run_query("
            DELETE FROM `ppSD_options`
            WHERE `id`='" . $db->mysql_clean($id) . "'
        ");

    }

    // Hooks
    foreach ( (array)$hooks as $item ) {

        $path = $plugin_path . '/hooks/' . $item['trigger'] . '.php';
        //if (! unlink($path)) {
            $msgs .= '<br />' . $item['trigger'] . '.php could not be deleted - please manually delete it.';
        //}

    }

    // Hooks
    $find = $db->run_query("
        DELETE FROM `ppSD_custom_actions`
        WHERE `plugin`='" . $db->mysql_clean($plugin_id) . "'
    ");

    // Plugin
    $find = $db->run_query("
        DELETE FROM `ppSD_routes`
        WHERE `plugin`='" . $db->mysql_clean($plugin_id) . "'
    ");

    // Drop tables
    foreach ( (array)$tables as $id => $item ) {
        $find = $db->run_query("
            DROP TABLE " . $db->mysql_cleans($id) . "
        ");
    }

    // Widget Entry
    $find = $db->run_query("
        DELETE FROM `ppSD_widgets`
        WHERE `id`='" . $db->mysql_clean($plugin_id) . "' AND `type`='plugin'
    ");

} else {
    $errors = 'Could not find plugin folder.';
}


$task = $db->end_task($task_id,'1');


if (! empty($errors)) {
    echo "0+++" . $errors;
} else {
    if (empty($msgs)) {
        echo "1+++Uninstalled plugin.";
    } else {
        echo "0+++Uninstalled plugin, but with warnings: " . $msgs;
    }
}
