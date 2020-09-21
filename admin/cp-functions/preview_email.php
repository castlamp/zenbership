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
 * Preview's an email
 * Source: JS previewEmail()

 */
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
// Check permissions and employee
$perm     = 'preview_email';
$employee = $admin->check_employee($perm);
$task_id  = $db->start_task('quickedit', 'staff', $_POST['user_id'], $employee['username']);
// Standard email
$data    = $db->email_data($_POST);
$cid     = generate_id('random', '35');
$changes = array();
$output  = new email($cid, $_POST['user_id'], $_POST['user_type'], $data, $changes, $_POST['templateid'], '1');
if (empty($output) || $output == 'Failed') {
    $output = '<center><iframe width="420" height="315" src="http://www.youtube.com/embed/kQKbX7x3o_Y" frameborder="0" allowfullscreen></iframe>
	<p>Did you forget to include content in your email?</p></center>';
}
$cid  = generate_id('random', '40');
$q    = $db->insert("
	INSERT INTO `ppSD_temp` (`id`,`data`)
	VALUES ('$cid','" . $db->mysql_clean($output) . "')
");
$task = $db->end_task($task_id, '0', 'No permissions.');
echo "1+++$cid";
exit;



