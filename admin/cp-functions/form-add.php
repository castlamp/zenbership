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
if ($_POST['edit'] == '1') {
    $type = 'edit';
    $edit = true;
} else {
    $type = 'add';
    $edit = false;
}
$task = 'form-' . $type;
// Check permissions and employee
$employee     = $admin->check_employee($task);
$task_id      = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
$form_builder = new form_builder;
if ($type == 'edit') {
    // Delete current form,
    // products on form,
    // and form conditions
    $delete = new delete('register-' . $_POST['id'], 'ppSD_forms');
}
// ----------------------------
// EVENT FORM
// register-free
// register-paid
// contact
if (empty($_POST['data']['code_required'])) {
    $_POST['data']['code_required'] = '0';
}
if (empty($_POST['data']['reg_status'])) {
    $_POST['data']['reg_status'] = '0';
}
if (empty($_POST['data']['public_list'])) {
    $_POST['data']['public_list'] = '0';
}
$final_forward = str_replace("\r\n", ',', $_POST['data']['email_forward']);
$final_forward = str_replace("\n", ',', $final_forward);
if (!empty($_POST['form']['col1'])) {
    $current_page = 1;
    if (!empty($_POST['data']['account'])) {
        $acc = $_POST['data']['account'];

    } else {
        $acc = '';

    }
    if (!empty($_POST['data']['source'])) {
        $source = $_POST['data']['source'];

    } else {
        $source = '';

    }
    if (empty($_POST['data']['account_create'])) {
        $_POST['data']['account_create'] = '0';
    }
    if (empty($_POST['data']['member_type'])) {
        $_POST['data']['member_type'] = '';
    }

    $form_data = array(
        'id'             => 'register-' . $_POST['id'],
        'act_id'         => '',
        'type'           => $_POST['type'],
        'name'           => $_POST['data']['name'],
        'redirect'       => $_POST['data']['redirect'],
        'source'         => $source,
        'account'        => $acc,
        'preview'        => $_POST['data']['preview'],
        'code_required'  => $_POST['data']['code_required'],
        'account_create' => $_POST['data']['account_create'],
        'reg_status'     => $_POST['data']['reg_status'],
        'description'    => $_POST['data']['description'],
        'disabled'       => $_POST['data']['disabled'],
        'public_list'    => $_POST['data']['public_list'],
        'captcha'        => $_POST['data']['captcha'],
        'email_thankyou' => $_POST['data']['email_thankyou'],
        'email_forward'  => $final_forward,
        'template'       => $_POST['data']['template'],
        'member_type'    => $_POST['data']['member_type'],
    );

    switch ($_POST['type']) {
        case 'contact':
            $scope = 'contact';
            break;
        case 'register-free';
        case 'register-paid';
        case 'update':
            $scope = 'member';
            break;
        default:
            $scope = '';
    }

    $add = $form_builder->create_form($form_data, $_POST['form']['col1'], $edit, $scope);
}

// ----------------------------
// CONDITIONS?
if (!empty($_POST['condition'])) {
    $condition = new conditions;
    foreach ($_POST['condition'] as $aCond) {
        $condition->create_condition($aCond, $_POST['id']);

    }

}
// ----------------------------
// PRODUCTS?
if (! empty($_POST['products'])) {
    $order = 0;
    foreach ($_POST['products'] as $aProd) {
        $order++;
        $form_builder->add_product_to_form($_POST['id'], $aProd, $order);
    }
}

// ----------------------------
// CONTENT
if (!empty($_POST['content'])) {
    $admin->create_product_access_granting($_POST['id'], $_POST['content']);

}
$task = $db->end_task($task_id, '1');
// Recache
$form    = new form();
$content = $form->get_form($_POST['id'], '1');
//$link = PP_URL . '/register.php?id=' . $_POST['id'];
//$return_cell = 'Your form has been created. You can access the form online from:<br /><br />' . $link;
$scope                 = 'form';
$table                 = 'ppSD_forms';
$return                = array();
$table_format          = new table($scope, $table);
$return['close_popup'] = '1';
if ($type == 'add') {
    $cell                       = $table_format->render_cell($content);
    $return['append_table_row'] = $cell;
    $return['show_saved']       = 'Created Form';

} else {
    $cell                 = $table_format->render_cell($content, '1');
    $return['update_row'] = $cell;
    $return['show_saved'] = 'Updated Form';

}
echo "1+++" . json_encode($return);
exit;
