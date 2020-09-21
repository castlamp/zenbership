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
$employee = $admin->check_employee('product-add');
if ($_POST['action'] == 'content_entry') {
    if (!empty($_POST['id'])) {
        $id = $_POST['id'];

    } else {
        $id = '';

    }
    $list = $admin->cell_content_grant($_POST['number'], $id);
    echo $list;
    exit;

} else if ($_POST['action'] == 'menu_item') {
    $list = $admin->cell_menu_item($_POST['id']);
    echo $list;
    exit;

} else if ($_POST['action'] == 'inner_option') {
    $list = $admin->cell_product_option_inner($_POST['main_option'], $_POST['number'], '', '');
    echo $list;
    exit;

} else if ($_POST['action'] == 'option') {
    if (!empty($_POST['id']) && $_POST['id'] != 'undefined') {
        $id = $_POST['id'];

    } else {
        $id = '';

    }
    $list = $admin->cell_product_option($_POST['number'], $id);
    echo $list;
    exit;

} else if ($_POST['action'] == 'tier') {
    if (!empty($_POST['id']) && $_POST['id'] != 'undefined') {
        $id = $_POST['id'];

    } else {
        $id = '';

    }
    $list = $admin->cell_product_tier($_POST['number'], $id);
    echo $list;
    exit;

} else if ($_POST['action'] == 'upsell_option') {
    if (!empty($_POST['id']) && $_POST['id'] != 'undefined') {
        $id = $_POST['id'];

    } else {
        $id = '';

    }
    if (!empty($_POST['when']) && $_POST['when'] != 'undefined') {
        $when = $_POST['when'];

    } else {
        $when = '';

    }
    $list = $admin->cell_upsell($id, $when);
    echo $list;
    exit;

}

