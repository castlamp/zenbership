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
$task     = 'content-folder-add';
$admin    = new admin;
$employee = $admin->check_employee($task);
$check    = $db->check_path($_POST['path']);
if ($check == '1') {
    echo '<img src="imgs/icon-save.png" width="16" height="16" border="0" class="icon" /> Folder found!';
    exit;

} else if ($check == '2') {
    echo '<img src="imgs/icon-warning.png" width="20" height="16" border="0" class="icon" /> You can only secure folders outside of the program\'s folder. You also cannot secure the base directory of your website.';
    exit;

} else if ($check == '3') {
    echo '<img src="imgs/icon-warning.png" width="20" height="16" border="0" class="icon" /> The folder exists, however it is not writable. This means that the program cannot write the necessary file to make the folder secure. Please set the permissions on the folder to "777" before continuing.';
    exit;

} else {
    echo '<img src="imgs/icon-warning.png" width="20" height="16" border="0" class="icon" /> Folder does not exist.';
    exit;

}



