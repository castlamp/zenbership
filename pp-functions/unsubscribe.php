<?php

/**
 * Email trackback detector
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
$error = 0;
if (!empty($_GET['id']) && !empty($_GET['eid'])) {
    require "../admin/sd-system/config.php";
    $campaign = new campaign($_GET['id']);
    $data     = $campaign->get_campaign();
    if ($data['error'] == '1') {
        $error = '1';
        $code  = 'E007';
    } else {
        $dataA = new history($_GET['eid'], '', '', '', '', '', 'ppSD_saved_emails');
        if (!empty($dataA->{'final_content'}['user_id']) && !empty($dataA->{'final_content'}['user_type'])) {
            $unsub   = $campaign->unsubscribe($dataA->{'final_content'}['user_id'], $dataA->{'final_content'}['user_type'], 'user');
            $changes = array(
                'campaign' => $data,
            );
            $temp    = new template('campaign_unsubscribed', $changes, '1');
            echo $temp;
            exit;
        } else {
            $error = '1';
            $code  = 'E008';
        }
    }

} else {
    $error = '1';
    $code  = 'E007';
}
if ($error == '1') {
    $changes = array(
        'details' => $db->get_error($code),
    );
    $temp    = new template('error', $changes, '1');
    echo $temp;
    exit;
}
