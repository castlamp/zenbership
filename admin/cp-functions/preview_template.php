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
 * Preview's a template
 * Source: JS preview_template()

 */
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
// Check permissions and employee
$perm     = 'preview_template';
$employee = $admin->check_employee($perm);
$task_id  = $db->start_task('preview_template', 'staff', '', $employee['username']);
$cid      = generate_id('random', '40');
if ($_POST['type'] == 'email') {
    $eid      = generate_id('random', '35');
    $changes  = array();
    $data     = array(
        'message' => $_POST['template'],
        'save'    => $_POST['save'],
        'subject' => $_POST['subject'],
    );
    $template = new email($eid, '', '', $data, '', '', '2');

} else {
    $changes = array();
    if (empty($POST['id'])) {
        $prevData = array(
            'template' => $_POST['template'],
            'title'    => $_POST['name'],
            'desc'     => '',
        );
        $template = new template('', $changes, '1', '', $prevData);

    } else {
        $template = new template($_POST['id'], $changes, '1');

    }

}
$q1   = $db->insert("

    INSERT INTO `ppSD_temp` (`id`,`data`)

    VALUES ('" . $db->mysql_cleans($cid) . "','" . $db->mysql_cleans($template) . "')

");
$task = $db->end_task($task_id, '1', '');
echo "1+++$cid";
exit;



