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
$event    = new event;
$data     = $event->get_event($_POST['id']);
$id       = $_POST['id'];
$etype    = 'targeted';
$type     = 'event';
$criteria = new criteria;
if (!empty($_POST['pd']) && $_POST['pd'] != 'undefined') {
    $crit_id = $criteria->build_filters(unserialize($_POST['pd']), 'rsvp', 'email');
    $to_name = "Specific attendees of " . $data['data']['name'] . " (<a href=\"return_null.php\" onclick=\"return popup('preview_criteria','id=" . $crit_id . "');\">View</a>)";
} else {
    $edata   = array(
        'x' => $id . '||event_id||eq||ppSD_event_rsvps'
    );
    $crit_id = $criteria->build_filters($edata, 'rsvp', 'email');
    $to_name = "Attendees of " . $data['data']['name'];
}
include 'email-send.php';

?>