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
 * Print based on criteria.
 * GET:scope = 'member', 'contact', 'rsvp', 'account'
 * GET:act_id = member ID, contact ID, rsvp ID, account ID
 * GET:type = build
 *  -> Builds criteria based on GET:data
 * GET:type = other
 *  -> Uses pre-build criteria based on GET:criteria_id
 */

require "../sd-system/config.php";
$task     = 'print';
$admin    = new admin;
$employee = $admin->check_employee($task);

if ($_GET['type'] == 'build') {
    $data     = unserialize($_GET['data']);
    $criteria = new criteria;
    $crit_id  = $criteria->build_filters($data, $_GET['scope'], 'print');
} else {
    $crit_id = $_GET['criteria_id'];
}

$task_id = $db->start_task($task, 'staff', $crit_id, $employee['username']);

if (! empty($_GET['title'])) {
    $title = $_GET['title'];
} else {
    $title = '';
}
if (! empty($_GET['order'])) {
    $order = $_GET['order'];
    if (! empty($_GET['dir'])) {
        $order .= ' ' . $_GET['dir'];
    }
} else {
    $order = '';
}

$print   = new printer($crit_id, $title, $order);

echo $print;
$task = $db->end_task($task_id, '1');
exit;



