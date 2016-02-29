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
$account  = new account;
$data     = $account->get_account($_POST['id']);
$id       = $_POST['id'];
$etype    = 'targeted';
$type     = 'account';
$criteria = new criteria;
if ($_POST['type'] == 'contacts') {
    $edata    = array(
        'account' => $id . '||account||eq||ppSD_contacts'
    );
    $type = "contact";
} else {
    $edata    = array(
        'account' => $id . '||account||eq||ppSD_members'
    );
    $type = "member";
}
$crit_id  = $criteria->build_filters($edata, $type, 'email');
$to_name  = $data['name'] . " Account";
include 'email-send.php';