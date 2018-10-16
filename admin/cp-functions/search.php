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

$employee = $admin->check_employee('', '1');

$permission = $admin->check_permissions('members', $employee);

if ($permission == '1') {

    $type = (! empty($_GET['type'])) ? $_GET['type'] : 'member';

    /*
    $array = $admin->get_scope_fields('member', 'array');
    $final = array();
    foreach ($array as $item) {
        if (
            $item['id'] != 'id' &&
            $item['id'] != 'joined' &&
            $item['id'] != 'last_login' &&
            $item['id'] != 'source' &&
            $item['id'] != 'account' &&
            $item['id'] != 'status' &&
            $item['id'] != 'last_updated' &&
            $item['id'] != 'member_type' &&
            $item['id'] != 'cell_carrier' &&
            $item['id'] != 'facebook' &&
            $item['id'] != 'twitter' &&
            $item['id'] != 'email_output' &&
            $item['id'] != 'linkedin' &&
            $item['id'] != 'fax' &&
            $item['id'] != 'sms_output'
        ) {
            $final[] = $item['id'];
        }
    }

    $filters = array();
    $filter_type = array();
    $filter_tables = array();
    foreach ($final as $find) {
        $filters[$find] = $_GET['query'];
        $filter_type[$find] = 'like';
        if ($find == 'username' || $find == 'email') {
            $filter_tables[$find] = 'ppSD_members';
        } else {
            $filter_tables[$find] = 'ppSD_member_data';
        }
    }

    $fields = array(
        'filter' => $filters,
        'filter_type' => $filter_type,
        'filter_tables' => $filter_tables,
    );
    */

    $fields = array(
        'filter' => array(
            'first_name' => $_GET['query'],
            'last_name' => $_GET['query'],
            'email' => $_GET['query'],
            'username' => $_GET['query'],
            'phone' => $_GET['query'],
            'zip' => $_GET['query'],
        ),
        'filter_type' => array(
            'first_name' => 'like',
            'last_name' => 'like',
            'email' => 'like',
            'username' => 'like',
            'phone' => 'like',
            'zip' => 'like',
        ),
        'filter_tables' => array(
            'first_name' => 'ppSD_member_data',
            'last_name' => 'ppSD_member_data',
            'email' => 'ppSD_members',
            'username' => 'ppSD_members',
            'phone' => 'ppSD_member_data',
            'zip' => 'ppSD_member_data',
        ),
    );

    $filters = $admin->build_criteria_filters($fields, $type);

    $criteria = new criteria();
    $id = $criteria->create($filters, 'Search', 0, 'or', $type, 'search', 0);

    header('Location: ' . PP_ADMIN . '/index.php?l=members&criteria_id=' . $id);
    exit;

} else {

    header('Location: ' . PP_ADMIN . '/index.php');
    exit;

}
