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
$user = new user;
$data = $user->get_user($_POST['id']);
if (empty($data['data']['email'])) {
    $admin->show_no_permissions('', 'There is no email on file for this user.', '1');
} else if ($data['data']['email_optout'] == '1') {
    $admin->show_no_permissions('', 'User has been opted out of e-mail services.', '1');
} else {
    $id        = $_POST['id'];
    $crit_id   = '';
    $etype     = 'email';
    $type      = 'member';
    $user_id   = $data['data']['id'];
    $user_type = 'member';
    $to_name   = $data['data']['last_name'] . ", " . $data['data']['first_name'] . " &lt;" . $data['data']['email'] . "&gt;";
    include 'email-send.php';

}

?>