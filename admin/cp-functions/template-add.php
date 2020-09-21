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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
if ($_POST['edit'] == '1') {
    $type = 'edit';

} else {
    $type = 'add';

}
$task = 'template-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// ----------------------------
if (!empty($_POST['lang'])) {
    $lang = $_POST['lang'];
} else {
    $lang = $db->get_option('language');
}
if ($type == 'edit') {

    $theme     = $db->get_option('theme');
    $path      = PP_PATH . '/pp-templates/html/' . $theme . '/' . $lang . '/';
    $file      = $_POST['id'] . '.php';
    $full_path = $path . $file;
    if (!is_writable($full_path)) {
        echo "0+++File ($full_path) is not writable. Please set the permissions to 777 using an FTP client and try again.";
        exit;
    } else {
        $q1   = $db->update("
            UPDATE `ppSD_templates`
            SET
                `title`='" . $db->mysql_cleans($_POST['name']) . "',
                `meta_title`='" . $db->mysql_cleans($_POST['meta_title']) . "'
            WHERE
                `id`='" . $db->mysql_cleans($_POST['id']) . "'
            LIMIT 1
        ");
        $temp = str_replace("\r", "", $_POST['template']);
        $db->write_file($path, $file, $temp);
    }

} else {

    //$history = new history($_POST['order']['id'],'','','','','','ppSD_cart_sessions');
    //$return_cell = $history->{'table_cells'};
}

$task   = $db->end_task($task_id, '1');
$return = array();
if ($type != 'edit') {
    $return['close_popup'] = '1';
}
$return['show_saved'] = 'Update template.';

echo "1+++" . json_encode($return);
exit;
