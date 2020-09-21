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
$employee = $admin->check_employee();
/*

// Generate the table entry.

if (! empty($_POST['criteria'])) {

	$criteria = unserialize(html_entity_decode($_POST['criteria']));

} else {

	$criteria = array();

}

*/
$criteria = array();
// Additional sorting options.
if (!empty($_POST['filter'])) {
    $add_criteria = $admin->build_subslider_criteria($_POST['filter'], $_POST['filter_type'], $_POST['filter_tables']);
    $add_fields   = $add_criteria['fields'];
    $criteria     = array_merge($criteria, $add_criteria['criteria']);
    //$get_crit = addslashes(serialize($criteria));
    $get_crit = '';

} else {
    $add_fields = '';
    $get_crit   = '';
    if (!empty($_POST['criteria'])) {
        $criteria = unserialize(html_entity_decode($_POST['criteria']));

    }

}
$history                    = new history('', $criteria, $_POST['page'], $_POST['display'], $_POST['order'], $_POST['dir'], $_POST['scope'], $_POST['join']);
$history->table_cells['td'] = str_replace('+++', '&#43;&#43;&#43;', $history->table_cells['td']);
echo "1+++" . $history->table_cells['td'] . "+++" . $history->table_cells['pages'] . "+++" . $history->table_cells['results'];
exit;



?>
