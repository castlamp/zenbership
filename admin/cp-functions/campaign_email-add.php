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
$task = 'campaign_msg-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['campaign_id'], $employee['username']);
// ----------------------------
if (empty($_POST['campaign_id'])) {
    echo "0+++Campaign ID is required.";
    exit;

}
// Get the campaign
$campaign = new campaign($_POST['campaign_id']);
$data     = $campaign->get_campaign();
if ($data['owner'] != $employee['id'] && $data['public'] != '1' && $employee['permissions']['admin'] != '1') {
    echo "0+++You don't have permission to alter this campaign.";
    exit;

}
if ($type == 'edit') {

    // --------------------------
    // Update the basic overview
    $table      = 'ppSD_campaign_items';
    $primary    = array('name', 'campaign_id', 'template_id', 'when_date');
    $ignore     = array(
        'template', 'subject', 'from', 'cc',
        'bcc', 'track', 'track_units', 'save',
        'update_activity', 'edit', 'id',
        'when_timeframe', 'when_date', 'name',
    );
    $query_form = $admin->query_from_fields($_POST, $type, $ignore, $primary);
    if ($data['when_type'] == 'after_join') {
        $value = $admin->construct_timeframe($_POST['when_timeframe']['number'], $_POST['when_timeframe']['unit']);
        $field = 'when_timeframe';

    } else {
        $value = $_POST['when_date'];
        $field = 'when_date';

    }
    $query1 = $db->update("
        UPDATE
            `ppSD_campaign_items`
        SET
            `title`='" . $db->mysql_cleans($_POST['name']) . "',
            `" . $field . "`='" . $db->mysql_cleans($value) . "'" . $query_form['u1'] . "
        WHERE
            `id`='" . $db->mysql_cleans($_POST['id']) . "'
        LIMIT 1
    ");
    // --------------------------
    // Update the message
    $item_id    = $_POST['id'];
    $msg        = $campaign->get_msg($_POST['id']);
    $table      = 'ppSD_saved_email_content';
    $primary    = array(
        'subject', 'from', 'cc',
        'bcc', 'save', 'update_activity', 'name',
    );
    $ignore     = array(
        'name', 'campaign_id', 'template_id',
        'when_date', 'id', 'edit', 'template',
        'when_timeframe', 'when_date', 'track',
        'track_units'
    );
    $query_form = $admin->query_from_fields($_POST, $type, $ignore, $primary);
    $query      = $db->update("
        UPDATE
            `ppSD_saved_email_content`
        SET
            `message`='" . $db->mysql_cleans($_POST['template']) . "'" . $query_form['u1'] . ",
            `trackback`='" . $db->mysql_clean($_POST['track']) . "',
            `track_links`='" . $db->mysql_clean($_POST['track_links']) . "'
        WHERE
            `id`='" . $db->mysql_cleans($msg['message']['id']) . "'
        LIMIT 1
    ");

} else {

    $eid = generate_id('random', '35');
    // Create the email
    $email_id = $db->insert("
        INSERT INTO `ppSD_saved_email_content` (
            `id`,
            `message`,
            `subject`,
            `from`,
            `cc`,
            `bcc`,
            `trackback`,
            `track_links`,
            `save`,
            `criteria_id`,
            `update_activity`,
            `owner`,
            `date`
        )

        VALUES (
          '" . $db->mysql_cleans($eid) . "',
          '" . $db->mysql_cleans($_POST['template']) . "',
          '" . $db->mysql_cleans($_POST['subject']) . "',
          '" . $db->mysql_cleans($_POST['from']) . "',
          '" . $db->mysql_cleans($_POST['cc']) . "',
          '" . $db->mysql_cleans($_POST['bcc']) . "',
          '" . $db->mysql_cleans($_POST['track']) . "',
          '" . $db->mysql_cleans($_POST['track_links']) . "',
          '" . $db->mysql_cleans($_POST['save']) . "',
          '" . $db->mysql_cleans($data['criteria_id']) . "',
          '" . $db->mysql_cleans($_POST['update_activity']) . "',
          '" . $db->mysql_cleans($employee['id']) . "',
          '" . current_date() . "'
        )

    ");
    if ($data['when_type'] == 'after_join') {
        $tf = $admin->construct_timeframe($_POST['when_timeframe']['number'], $_POST['when_timeframe']['unit']);
        $td = '';
    } else {
        $td = $_POST['when_date'];
        $tf = '';
    }
    // Create the campaign item
    $item_id = $db->insert("
        INSERT INTO `ppSD_campaign_items` (
          `title`,
          `campaign_id`,
          `msg_id`,
          `when_timeframe`,
          `when_date`,
          `template_id`
        )
        VALUES (
          '" . $db->mysql_clean($_POST['name']) . "',
          '" . $db->mysql_clean($_POST['campaign_id']) . "',
          '" . $db->mysql_clean($eid) . "',
          '" . $db->mysql_clean($tf) . "',
          '" . $db->mysql_clean($td) . "',
          '" . $db->mysql_clean($_POST['template_id']) . "'
        )
    ");

}
$task                  = $db->end_task($task_id, '1');
$table                 = 'ppSD_campaign_items';
$history               = new history($item_id, '', '', '', '', '', $table);
$content               = $history->final_content;
$table_format          = new table('campaign', $table);
$return                = array();
$return['close_popup'] = '1';
if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Created';

} else {
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
    $return['show_saved'] = 'Updated';

}
echo "1+++" . json_encode($return);
exit;



