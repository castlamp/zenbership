<?php

/**
 * Campaign subscription confirmation.
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
$check = md5($_GET['campaign_id'] . md5($_GET['id']) . SALT1);
if ($check != $_GET['s']) {
    $error = '1';
    $code  = 'N001';
} else {
    if (!empty($_GET['campaign_id']) && !empty($_GET['id'])) {
        $campaign = new campaign($_GET['campaign_id']);
        $data     = $campaign->get_campaign();
        if ($data['error'] != '1') {
            $confirm = $campaign->confirm_optin($_GET['id']);
            if ($confirm == '1') {
                $error = '0';
            } else {
                $code  = 'N001';
                $error = '1';
            }
        } else {
            $code  = 'N002';
            $error = '1';
        }
    } else {
        $code  = 'N001';
        $error = '1';
    }
}
if ($error == '1') {
    $changes = array(
        'details' => $this->get_error($code),
    );
    $temp    = new template('error', $changes, '1');
    echo $temp;
    exit;
} else {
    $changes = array(
        'campaign' => $data,
    );
    $temp    = new template('campaign_subscription', $changes, '1');
    echo $temp;
    exit;
}
