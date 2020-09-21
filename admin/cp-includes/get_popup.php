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

require "../sd-system/config.php";
$admin = new admin;
/**
 *    Gets a page to be populated
 *    into the popup element.
 */
if (! empty($_POST['p'])) {

    // Check permissions and employee
    $permission = $_POST['p']; // . '-popup';
    $employee   = $admin->check_employee($permission);
    $task_id    = $db->start_task($permission, 'staff', '', $employee['username']);

    $af = new adminFields();

    // Get popup
    $lit = 'popups/' . $_POST['p'] . '.php';

    if (! file_exists($lit)) {
        $edit = explode('-', $_POST['p']);

        $e1 = (! empty($edit['1'])) ? $edit['1'] : ''; // Action
        $e2 = (! empty($edit['0'])) ? $edit['0'] : ''; // Plugin ID

        $ae = new admin_extensions($e1, $employee, $e2);

        $content = $ae->runTask($edit['1'], 'views/popup');
    } else {
        ob_start();
        include($lit);
        $content = ob_get_contents();
        ob_end_clean();
    }

    $task = $db->end_task($task_id, 'staff', $employee['username'], '1');

    $fcontent = str_replace('+++', '&#43;&#43;&#43;', $content);

    echo "1+++" . $fcontent;
    exit;
}
