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
/**
 *

 */
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
if ($_POST['edit'] == '1') {
    $type = 'edit';

} else {
    $type = 'add';

}
$task = 'template_email-' . $type;
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
// ----------------------------
if ($type == 'edit') {
    $gettemplate = $db->get_array("
        SELECT *
        FROM `ppSD_templates_email`
        WHERE `template`='" . $db->mysql_clean($_POST['id']) . "'
        LIMIT 1
    ");
    $add_set     = '';
    if ($gettemplate['custom'] == '1') {
        $add_set .= ",`content`='" . $db->mysql_cleans($_POST['template']) . "'";
        $theme = '';
    } else {
        $theme = $db->get_option('theme_emails');
    }
    $path = PP_PATH . '/pp-templates/email/' . $theme . '/';
    $file = $_POST['id'] . '.html';
    if (!is_writable($path . $file) && $gettemplate['custom'] != '1') {
        echo "0+++File ($path) is not writable. Please set the permissions to 777 using an FTP client and try again.";
        exit;

    } else {
//  $cid = generate_id('random','15');
// `template`='" . $db->mysql_cleans($cid) . "',
        $q1 = $db->update("
            UPDATE `ppSD_templates_email`
            SET
                `owner`='" . $db->mysql_cleans($employee['id']) . "',
                `public`='" . $db->mysql_cleans($_POST['public']) . "',
                `title`='" . $db->mysql_cleans($_POST['name']) . "',
                `track`='" . $db->mysql_cleans($_POST['track']) . "',
                `track_links`='" . $db->mysql_cleans($_POST['track_links']) . "',
                `subject`='" . $db->mysql_cleans($_POST['subject']) . "',
                `from`='" . $db->mysql_cleans($_POST['from']) . "',
                `cc`='" . $db->mysql_cleans($_POST['cc']) . "',
                `bcc`='" . $db->mysql_cleans($_POST['bcc']) . "',
                `save`='" . $db->mysql_cleans($_POST['save']) . "',
                `status`='" . $db->mysql_cleans($_POST['status']) . "',
                `header_id`='" . $db->mysql_cleans($_POST['header_id']) . "',
                `footer_id`='" . $db->mysql_cleans($_POST['footer_id']) . "',
                `format`='" . $db->mysql_cleans($_POST['format']) . "',
                `theme`='" . $theme . "'
                $add_set
            WHERE
                `template`='" . $db->mysql_cleans($_POST['id']) . "'
            LIMIT 1
        ");

        if ($gettemplate['custom'] != '1') {
            $temp = str_replace("\r", "", $_POST['template']);
            $db->write_file($path, $file, $temp);

        }

    }

} else {
    $q2 = $db->insert("
        INSERT INTO `ppSD_templates_email` (`template`,`title`,`content`,`track`,`track_links`,`subject`,`from`,`cc`,`bcc`,`save`,`custom`,`public`,`status`)
        VALUES (
          '" . $db->generate_id('random', '27') . "',
          '" . $db->mysql_clean($_POST['name']) . "',
          '" . $db->mysql_clean($_POST['template']) . "',
          '" . $db->mysql_clean($_POST['track']) . "',
          '" . $db->mysql_clean($_POST['track_links']) . "',
          '" . $db->mysql_clean($_POST['subject']) . "',
          '" . $db->mysql_clean($_POST['from']) . "',
          '" . $db->mysql_clean($_POST['cc']) . "',
          '" . $db->mysql_clean($_POST['bcc']) . "',
          '" . $db->mysql_clean($_POST['save']) . "',
          '1',
          '" . $db->mysql_clean($_POST['public']) . "',
          '1'
        )
    ");
}

$task                  = $db->end_task($task_id, '1');

$return                = array();
$return['close_popup'] = '1';
$return['reload']      = '1';

echo "1+++" . json_encode($return);
exit;

