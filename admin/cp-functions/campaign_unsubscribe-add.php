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
$task = 'campaign_unsubscribe-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['campaign_id'], $employee['username']);
// ----------------------------
if (empty($_POST['campaign_id'])) {
    echo "0+++Campaign ID is required.";
    exit;

}
if (empty($_POST['user_id'])) {
    echo "0+++User ID is required.";
    exit;

}
// Get the campaign
$campaign = new campaign($_POST['campaign_id']);
$data     = $campaign->get_campaign();
if ($data['owner'] != $employee['id'] && $data['public'] != '1' && $employee['permissions']['admin'] != '1') {
    echo "0+++You don't have permission to alter this campaign.";
    exit;

}
if ($data['optin_type'] == 'criteria') {
    $force = '1';

} else {
    $force = '0';

}
$item_id = $campaign->unsubscribe($_POST['user_id'], $_POST['user_type'], 'staff', $employee['id'], $force);
$task    = $db->end_task($task_id, '1');
/*

$history = new history($item_id,'','','','','','ppSD_campaign_unsubscribe');

$return_cell = $history->{'table_cells'};



$task = $db->end_task($task_id,'1');



echo "1+++" . $_POST['id'] . "+++" . $return_cell;

exit;

*/
if ($item_id == '0') {
    echo "0+++Could not find that user on the subscription list.";
    exit;

} else {
    $table                      = 'ppSD_campaign_unsubscribe';
    $history                    = new history($item_id, '', '', '', '', '', $table);
    $content                    = $history->final_content;
    $table_format               = new table('campaign', $table);
    $return                     = array();
    $return['close_popup']      = '1';
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Unsubscribed User';
    echo "1+++" . json_encode($return);
    exit;

}
