<?php

/**
 * Lost Password Recovery
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
$user = new user;
// Check Captcha
$check = $db->check_captcha(get_ip(), 'user', $_POST['captcha']);
if ($check != '1') {
    $array = array('code' => 'L013');
    $link  = build_link('lost_password.php', $array);
    header('Location: ' . $link);
    exit;
} else {
    if (empty($_POST['email'])) {
        $array = array('code' => 'L020');
        $link  = build_link('lost_password.php', $array);
        header('Location: ' . $link);
        exit;
    } else {
        // Generate reset link
        // Email link
        // Allow user to select new password on page.
        $total         = 0;
        $use_member_id = '';
        $STH           = $db->run_query("
			SELECT `id`
			FROM `ppSD_members`
			WHERE
			    `username`='" . $db->mysql_clean($_POST['email']) . "' OR
			    `email`='" . $db->mysql_clean($_POST['email']) . "' OR
			    `id`='" . $db->mysql_clean($_POST['email']) . "'
		");
        // `email`='" . $db->mysql_clean($_POST['email']) . "' OR
        while ($row = $STH->fetch()) {
            $total++;
            $use_member_id = $row['id'];
        }
        if ($total <= 0) { // || $total > 1
            $array = array('code' => 'L021');
            $link  = build_link('lost_password.php', $array);
            header('Location: ' . $link);
            exit;
        } else {
            // Get the member.
            $user   = new user;
            $member = $user->get_user($use_member_id);
            if (empty($member['data']['email'])) {
                $array = array('code' => 'L023');
                $link  = build_link('lost_password.php', $array);
                header('Location: ' . $link);
                exit;
            }
            // Add entry
            $mid = $user->issue_pwd_reset($member['data']['id'], $member['data']['email']);
            $put = 'password_recovery';
            $db->put_stats($put);
            $history = $db->add_history('logout', '2', $member['data']['id'], '', '', '');
            // Redirect with success
            $array = array('scode' => 'L022');
            $link  = build_link('lost_password.php', $array);
            header('Location: ' . $link);
            exit;
        }

    }
}
