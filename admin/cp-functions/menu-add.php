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
$task = 'menu-' . $type;

// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);

if ($type == 'add') {

    $id = preg_replace("/[^A-Za-z0-9 ]/", '', $_POST['name']);
    $id = str_replace(' ', '_', $id);
    $id = substr(strtolower($id), 0, 25);

    $find = $db->get_array("
        SELECT COUNT(*)
        FROM `ppSD_widgets`
        WHERE `id`='" . $db->mysql_clean($id) . "'
    ");

    if ($find['0'] > 0) {
        echo "0+++Widget ID already exists. Please rename this menu.";
        exit;
    }

    $add_it = $db->insert("
        INSERT INTO `ppSD_widgets` (
          `id`,
          `name`,
          `type`,
          `menu_type`,
          `add_class`,
          `add_id`,
          `active`
        ) VALUES (
          '" . $db->mysql_clean($id) . "',
          '" . $db->mysql_clean($_POST['name']) . "',
          'menu',
          'vertical',
          '" . $db->mysql_clean($_POST['add_class']) . "',
          '" . $db->mysql_clean($_POST['add_id']) . "',
          '1'
        )
    ");

} else {

    $add_it = $db->update("
        UPDATE `ppSD_widgets`
        SET
          `name`='" . $db->mysql_clean($_POST['name']) . "',
          `add_class`='" . $db->mysql_clean($_POST['add_class']) . "',
          `add_id`='" . $db->mysql_clean($_POST['add_id']) . "'
        WHERE
          `id`='" . $db->mysql_clean($_POST['id']) . "'
        LIMIT 1
    ");

    $q1 = $db->delete("
        DELETE FROM `ppSD_widgets_menus`
        WHERE `widget_id`='" . $db->mysql_clean($_POST['id']) . "'
    ");
    $id = $_POST['id'];

}

$pos = 0;
foreach ($_POST['nav'] as $item) {


    // Content?
    if (! empty($item['content_id'])) {
        $content = new content();
        // $cont = $content->get_content($item['content_id']);
        $typeA = '1';
        $cid = $item['content_id'];
        $link = '';
    } else {
        $cid = '';
        $typeA = '2';
        $link = $item['menu_dud'];
        /*
        if (strpos($link, 'http://') === false && strpos($link, 'https://') === false) {
            $link = 'http://' . $link;
        }
        */
    }

    // 1 = cms page, 2 = full url, 3 = onsite build url
    $pos++;
    $in_item = $db->insert("
        INSERT INTO `ppSD_widgets_menus` (
          `widget_id`,
          `title`,
          `link`,
          `link_type`,
          `link_target`,
          `position`,
          `content_id`
        ) VALUES (
          '" . $id . "',
          '" . $db->mysql_clean($item['name']) . "',
          '" . $db->mysql_clean($link) . "',
          '" . $typeA . "',
          '" . $db->mysql_clean($item['link_target']) . "',
          '" . $pos . "',
          '" . $cid . "'
        )
    ");
}


// Reply
$task                  = $db->end_task($task_id, '1');
$table                 = 'ppSD_widgets';
$scope                 = '';
$history               = new history($id, '', '', '', '', '', $table);
$content               = $history->final_content;
$table_format          = new table($scope, $table);
$return                = array();
$return['close_popup'] = '1';

if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Created Menu';
} else {
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
    $return['show_saved'] = 'Updated Menu';

}
echo "1+++" . json_encode($return);
exit;
