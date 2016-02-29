<?php

/**
 * Activation Code
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
$check = md5(date('Y-m-d-H') . get_ip() . SALT1);
if ($check != $_GET['s']) {
    exit;
} else {
    if (! empty($_GET['id'])) {
        // Get the user data
        $member = $user->get_user($_GET['id'], '', '0');
        if ($member['data']['status'] == 'I') {
            // Issue code
            $new_code = md5($member['data']['salt'] . md5(time()) . $member['data']['id'] . rand(100, 999999));
            // Update DB
            $up = $db->update("
				UPDATE `ppSD_members`
				SET `activation_code`='" . $db->mysql_clean($new_code) . "',`status`='P'
				WHERE `id`='" . $db->mysql_clean($member['data']['id']) . "'
				LIMIT 1
			");
            // Create link
            $link = PP_URL . '/pp-functions/activate.php?c=' . $new_code . '&u=' . $member['data']['id'];
            // E-mail the user
            $changes = array(
                'member' => $member['data'],
                'code'   => $new_code,
                'link'   => $link,
            );
            $email   = new email('', $member['data']['member_id'], 'member', '', $changes, 'email_activation_code');
            // Display template
            $changes = array(
                'member' => $member['data']
            );
            $temp    = new template('activation_code_sent', $changes, '1');
            echo $temp;
            exit;
        } else {
            echo "Your account is not inactive.";
            exit;
        }
    } else {
        echo "Account not found.";
        exit;
    }
}
