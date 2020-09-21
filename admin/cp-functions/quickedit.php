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
 * This can only be used with items in the
 * DB that have a "owner".
 * Called from JS quickedit() function.

 */
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
// Check permissions and employee
$employee = $admin->check_employee();
$task_id  = $db->start_task('quickedit', 'staff', $_POST['search_for'], $employee['username']);
// First get thing to check owner
$get = $db->get_array("

	SELECT `owner`

	FROM `" . $db->mysql_cleans($_POST['table']) . "`

	WHERE `" . $db->mysql_clean($_POST['search_col']) . "`='" . $db->mysql_clean($_POST['search_for']) . "'

	LIMIT 1

");
if ($get['owner'] == $employee['id'] || $employee['permissions']['admin'] == '1') {
    $update  = '';
    $explode = explode(',', $_POST['changes']);
    foreach ($explode as $name => $value) {
        $expitem = explode('=', $value);
        $update .= ",`" . $db->mysql_cleans($expitem['0']) . "`='" . $db->mysql_clean($expitem['1']) . "'";

    }
    if (!empty($update)) {
        $update = substr($update, 1);
        $up     = $db->update("

			UPDATE `" . $db->mysql_cleans($_POST['table']) . "`

			SET $update

			WHERE `" . $db->mysql_clean($_POST['search_col']) . "`='" . $db->mysql_clean($_POST['search_for']) . "'

			LIMIT 1

		");

    }
    $task = $db->end_task($task_id, '0', 'No permissions.');
    echo "1+++Success.";
    exit;

} else {
    $task = $db->end_task($task_id, '0', 'No permissions.');
    echo "0+++No permissions.";
    exit;

}





