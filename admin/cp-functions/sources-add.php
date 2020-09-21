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

$task = 'source-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);


$rules = array(
    'source' => array('required'),
    'redirect' => array('url'),
);
if ($type == 'edit') {
    $options = array(
        'skip_default' => '1',
        'edit' => '1',
    );
} else {
    $options = array(
        'skip_default' => '0',
        'edit' => '0',
    );
}
$validate = new ValidatorV2($_POST, $rules, $options);
if ($validate->error_found == '1') {
    echo "0+++" . $validate->plain_english;
    exit;
}


if ($_POST['id'] == 'new') {

    $source = new source;
    $trigger = $source->clear_origin($_POST['source']) . '_' . rand(0,9999);

    $last_id = $db->insert("
        INSERT INTO `ppSD_sources` (`source`,`type`,`trigger`,`redirect`, `redirect_b`)
        VALUES (
          '" . $db->mysql_clean($_POST['source']) . "',
          'custom',
          '" . $db->mysql_clean($trigger) . "',
          '" . $db->mysql_clean($_POST['redirect']) . "',
          '" . $db->mysql_clean($_POST['redirect_b']) . "'
        )
    ");

} else {
    $q1 = $db->update("
        UPDATE `ppSD_sources`
        SET
          `source`='" . $db->mysql_clean($_POST['source']) . "',
          `redirect`='" . $db->mysql_clean($_POST['redirect']) . "',
          `redirect_b`='" . $db->mysql_clean($_POST['redirect_b']) . "'
        WHERE
            `id`='" . $db->mysql_clean($_POST['id']) . "'
        LIMIT 1

    ");
    $last_id = $_POST['id'];
}

$task                  = $db->end_task($task_id, '1');

$return                = array();
$return['close_popup'] = '1';

$history = new history($last_id, '', '', '', '', '', 'ppSD_sources');
$content = $history->final_content;
$table_format          = new table($scope, 'ppSD_sources');

if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Added Source';
} else {
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
    $return['show_saved'] = 'Update Source';
}

echo "1+++" . json_encode($return);
exit;
