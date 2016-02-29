<?php

/**
 * Reset password.
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
require "../admin/sd-system/config.php";
$user  = new user;
$check = $user->check_pwd_reset($_POST['s']);
if ($check == '0') {
    $array = array('code' => 'L024');
    $link  = build_link('lost_password.php', $array);
    header('Location: ' . $link);
    exit;
} else {

    $task_id = $db->start_task('password_reset', 'user', '', $check);

    if (empty($_POST['password']) || empty($_POST['s']) || ($_POST['password'] != $_POST['repeat_pwd'])) {
        $array = array(
            'code' => 'L025',
            's'    => $_POST['s'],
        );
        $link  = build_link('pp-functions/reset_password.php', $array);
        header('Location: ' . $link);
        exit;
    } else {
        $salt       = $db->generate_salt();
        $encode_pwd = $user->encode_password($_POST['password'], $salt);

        $q1         = $db->update("
			UPDATE `ppSD_members`
			SET `salt`='" . $db->mysql_clean($salt) . "',`password`='" . $encode_pwd . "'
			WHERE `id`='" . $db->mysql_clean($check) . "'
			LIMIT 1
		");
        $del        = $user->delete_pwd_reset($_POST['s'], $check);
        $array      = array(
            'scode' => 'L026',
        );
        $link       = build_link('login.php', $array);


        // Finalize hooks and stuff.
        $indata = array(
            'member_id' => $check,
            'password' => $_POST['password'],
            'salt' => $salt,
            'encoded_password' => $encode_pwd,
        );
        $task = $db->end_task($task_id, '1', '', 'password_reset', $check, $indata);

        header('Location: ' . $link);
        exit;
    }

}
