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
$permission = $_POST['permission'];
$employee   = $admin->check_employee($permission);
// $menu = unserialize(html_entity_decode($_POST['menu']));
//  echo "0+++";print_r($_POST['filter']);exit;
// Filters
$query_string = '';
$filters      = array();
foreach ($_POST['filter'] as $name => $value) {
    if (!empty($value)) {
        // Blank URL?
        if ($value == 'http://') {
            $value = '';

        }
        // Continue...
        $check_name = str_replace('_low', '', $name);
        $check_name = str_replace('_high', '', $check_name);
        $data       = '';
        if (!empty($_POST['filter_type'][$name])) {
            $f_type = $_POST['filter_type'][$name];

        } else {
            $f_type = 'like';

        }
        if (!empty($_POST['filter_tables'][$check_name])) {
            $f_table = $_POST['filter_tables'][$check_name];

        } else {
            $f_table = '';

        }
        if (strpos($name, '_low') !== false) {
            $f_type = 'gte';
            $name   = str_replace('_low', '', $name);
            if (empty($_POST['filter'][$name . '_high'])) {
                $f_type = $_POST['filter_type'][$name];
            }
        } else if (strpos($name, '_high') !== false) {
            $f_type = 'lte';
            $name   = str_replace('_high', '', $name);
        }

        $name  = str_replace('||', '&#124;&#124;', $name);
        $value = str_replace('||', '&#124;&#124;', $value);
        $data  = $value . "||" . $name . "||" . $f_type . "||" . $f_table;
        $query_string .= '&filters[]=' . $value . "||" . $name . "||" . $f_type . "||" . $f_table;
        $filters[] = $data;

    }

    $query_string = ltrim($query_string, '&');

}
if (!empty($_POST['hide_checkbox'])) {
    $hideboxes = $_POST['hide_checkbox'];

} else {
    $hideboxes = '0';

}
// Generate the table entry.
// $gen_table = $admin->get_table($_POST['table'],$menu,$filters,$_POST['order'],$_POST['dir'],$_POST['display'],$_POST['page'],'',$hideboxes);
/*

$table = $_POST['table'];

$default_sort = $_POST['order'];

$default_order = $_POST['dir'];

$default_filters = array('-||status||neq||ppSD_cart_sessions');

$force_filters = array();

$gen_table = $admin->get_table($table,$menu,$default_sort,$default_order,$default_filters,$force_filters);

*/
$table           = $_POST['table'];
$order           = $_POST['order'];
$dir             = $_POST['dir'];
$display         = $_POST['display'];
$page            = $_POST['page'];
$defaults        = array(
    'sort'    => $order,
    'order'   => $dir,
    'page'    => $page,
    'display' => $display,
    'filters' => $filters,
);
$force_filters   = array();

$merge = array_merge($_GET, $_POST);

$gen_table       = $admin->get_table($table, $merge, $defaults, $force_filters, null, null, null, $query_string);

$gen_table['td'] = str_replace('+++', '&#43;&#43;&#43;', $gen_table['td']);

echo "1+++" . $gen_table['td'] . "+++";
echo $gen_table['pages'] . "+++";
echo $gen_table['total'] . "+++";
echo serialize($filters) . "+++";
echo $query_string;
echo "+++" . $gen_table['math'];
echo "+++" . $gen_table['math1'];
echo "+++" . $gen_table['math2'];
echo "+++" . $gen_table['math3'];
echo "+++" . $gen_table['show_next'];
echo "+++" . $gen_table['next_page'];
echo "+++" . $gen_table['next_link'];
echo "+++" . $gen_table['show_prev'];
echo "+++" . $gen_table['prev_page'];
echo "+++" . $gen_table['prev_link'];
exit;
