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
// Check permissions and employee
$employee = $admin->check_employee();
$task_id  = $db->start_task('view_saved_email', 'staff', $_GET['id'], $employee['username']);
if (!empty($_GET['queue'])) {
    $data    = new history($_GET['id'], '', '', '', '', '', 'ppSD_saved_email_content');
    $content = $data->{'final_content'}['message'];

} else {
    $data    = new history($_GET['id'], '', '', '', '', '', 'ppSD_saved_emails');
    $content = $data->{'final_content'}['content'];

}
if (empty($content)) {
    $final_com = '<span class="weak">Message was not saved.</span>';

} else {
    // Prevent trackback.
    $content   = str_replace('etc.php', 'null.php', $content);
    $final_com = $content;

}
$task      = $db->end_task($task_id, '1');
$final_com = str_replace('+++', '&#43;&#43;&#43;', $final_com);
echo $final_com;
exit;



