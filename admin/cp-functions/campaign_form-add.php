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
$task         = 'campaign_form-add';
$admin        = new admin;
$employee     = $admin->check_employee($task);
$task_id      = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
$id           = 'campaign-' . $_POST['id'];
$delete       = new delete($id, 'ppSD_forms');
$form_data    = array(
    'id'            => $id,
    'type'          => 'campaign',
    'act_id'        => $_POST['id'],
    'name'          => 'Campaign Signup Form',
    'preview'       => '0',
    'pages'         => '1',
    'code_required' => '0',
);

// Delete existind forms.

$form_builder = new form_builder;
$add          = $form_builder->create_form($form_data, $_POST['form']['col1'], false, 'member');
/*

 * [condition] => Array

        (

            [0] => Array

                (

                    [field] => zen_fixed_autopop

                    [eq] => eq

                    [value] =>

                    [type] => campaign

                    [campaign_id] => 38b92ea1852

                    [expected_value] =>

                    [content_id] =>

                    [content_timeframe] => Array

                        (

                            [number] =>

                            [unit] => year

                        )



                    [product_id] =>

                    [product_qty] =>

                )



        )

 */
$aCond     = array(
    'field'       => 'zen_fixed_autopop',
    'eq'          => 'eq',
    'value'       => '',
    'type'        => 'campaign',
    'campaign_id' => $_POST['id'],
);

$condition = new conditions;
$condition->create_condition($aCond, $id);
$task = $db->end_task($task_id, '1');
// Generate the form's code.
$field = new field('', '1');
$form  = $field->generate_form($id);
// Reply.
$return                     = array();
$return['update_cells']     = array(
    'cp_form_data' => $form
);
$return['change_popup_tab'] = '0';
$return['show_saved']       = 'Form Updated';
echo "1+++" . json_encode($return);
exit;



