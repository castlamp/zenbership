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
 * Create Event
 * From admin

 */
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
$task  = 'theme_select';
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
if ($employee['permissions']['admin'] != '1') {
    echo "You are not permitted to perform this task.";
    exit;

} else {
    // Event
    if ($_POST['type'] == 'html') {
        $type = 'html';
        $opt  = 'theme';

    } else if ($_POST['type'] == 'email') {
        $type = 'email';
        $opt  = 'theme_emails';

    }
    $db->update_option($opt, $_POST['id']);
    $up  = $db->update("

        UPDATE `ppSD_themes`

        SET `active`='0'

        WHERE `active`='1' AND `type`='" . $type . "'

        LIMIT 1

    ");
    $up1 = $db->update("

        UPDATE `ppSD_themes`

        SET `active`='1'

        WHERE `id`='" . $db->mysql_clean($_POST['id']) . "' AND `type`='" . $type . "'

        LIMIT 1

    ");

}
$task = $db->end_task($task_id, '1');
echo "1+++";
exit;



