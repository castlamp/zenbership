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
$admin = new admin;
$task  = 'criteria-add';

// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id  = $db->start_task($task, 'staff', 'new', $employee['username']);

if ($_POST['criteria'] == '1') {
    $filters        = $admin->build_criteria_filters($_POST, $_POST['type']);
    $filters['all'] = '0';
} else {
    $filters = array(
        'all' => '1',
    );
}

$criteria = new criteria();
$id       = $criteria->create($filters, $_POST['name'], $_POST['save'], $_POST['inclusive'], $_POST['type'], $_POST['act']);

if ($criteria->errorResults) {
    $preview = '<div id="crit_preview">No results found.</div>';
} else {
    $preview  = $criteria->preview();
    $query      = $criteria->getQuery();
    $task     = $db->end_task($task_id, '1');
}

$del = $criteria->delete_criteria($id);

echo $preview;
exit;
