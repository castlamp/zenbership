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
// Check permissions and employee
$employee = $admin->check_employee('conditions');
$scope    = $_POST['type'];
if (!empty($_POST['id']) && $_POST['id'] != 'undefined') {
    $id = $_POST['id'];
} else {
    $id = '';
}
if (!empty($_POST['value']) && $_POST['value'] != 'undefined') {
    $value = $_POST['value'];
} else {
    $value = '';
}
if (!empty($_POST['eq']) && $_POST['eq'] != 'undefined') {
    $eq = $_POST['eq'];
} else {
    $eq = '';
}
$data = $admin->cell_criteria($scope, $id, $value, $eq);
echo $data;
exit;





