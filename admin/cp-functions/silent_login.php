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
$admin    = new admin;
$task     = 'silent_login';
$employee = $admin->check_employee($task);
$user     = new user;
$data     = $user->get_user($_GET['id']);
if ($data['data']['owner'] != $employee['id'] && $employee['permissions']['admin'] != '1' && $data['data']['public'] != '1') {
    echo "You cannot perform this task for this member.";
    exit;

}
$task_id = $db->start_task($task, 'staff', $_GET['id'], $employee['username']);
// Create the session at this point.
$session       = new session;
$check_session = $session->check_session();
if ($check_session['error'] != '1') {
    $kill_session = $session->kill_session($check_session['id']);

}
$create = $session->create_session($data['data']['id'], '0');
// Log user into his/her content?
// mod_rewrite directories only. CMS
// stuff is handled when it is loaded.
foreach ($data['areas'] as $content) {
    if ($content['type'] == 'folder') {
        $session->folder_login($create, $content['content_id']);

    }

}
$redirect = PP_URL . '/manage';
$task     = $db->end_task($task_id, '1');
header('Location: ' . $redirect);
exit;



