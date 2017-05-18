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

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Load the basics
require "admin/sd-system/config.php";

// Check a user's session
$session = new session;
$ses     = $session->check_session();

// Reset?
if (! empty($_GET['action']) && $_GET['action'] == 'reset') {
    /*
        foreach ($_COOKIE as $name => $value) {
            if (strlen($name) > 25) {
                $db->delete_cookie($name);
            }
        }
        if (! empty($_GET['id'])) {
            $url = $db->current_url(0) . '?id=' . $_GET['id'];
        } else {
            $url = PP_URL . '/register.php';
        }
        header('Location: ' . $url);
        exit;
     */
    if (!empty($_GET['redirect'])) {
        $redirect = '1';
    } else {
        $redirect = '0';
    }
    $form = new form();
    if (! empty($_GET['session'])) {
        // Destroy specific
        $form->kill_session($redirect, $_GET['session'], $_GET['sp']);
    } else {
        // Destory by IP
        $form->kill_session($redirect);
    }
}

// ID?
if (empty($_GET['id'])) {

    $form      = new form();
    $get_forms = $form->public_list();

    if (sizeof($get_forms['forms']) == 1) {
        header('Location: register.php?id=' . str_replace('register-', '', $get_forms['forms']['0']));
        exit;
    }

    $changes   = array(
        'forms' => (! empty($get_forms['forms'])) ? $get_forms['list'] : '',
    );

    $wrapper   = new template('register_list', $changes, '1');
    echo $wrapper;
    exit;

} else {

    // Load stuff
    // if (strpos($_GET['id'],'register') === false) {
    //     $_GET['id'] = 'register-' . $_GET['id'];
    // }
    $this_form = new form('', '', $_GET['id']);

    if ($this_form->formdata['error'] == '1') {
        $db->show_error_page('F028');
        exit;
    } else if ($this_form->formdata['disabled'] == '1') {
        $db->show_error_page('F029');
        exit;
    } else {
        if ($this_form->formdata['type'] == 'contact') {
            $temp = 'contact';
            //$scope = 'register';
            $req_login = '0';
        } else if ($this_form->formdata['type'] == 'register-free' || $this_form->formdata['type'] == 'register-paid') {
            $temp = 'register';
            //$scope = 'register';
            $req_login = '0';
        } else if ($this_form->formdata['type'] == 'dependency') {
            $temp = 'dependency';
            //$scope = 'register';
            $req_login = '1';
        } else if ($this_form->formdata['type'] == 'update') {
            $temp = 'update';
            //$scope = 'update';
            $req_login = '1';
            // Send from admin?
        }
    }

    // Send from admin?
    if (!empty($_GET['mid']) && !empty($_GET['s']) && ($this_form->formdata['type'] == 'dependency' || $this_form->formdata['type'] == 'update')) {
        //$user = new user;
        //$check_hash = $user->build_confirmation_hash($_GET['mid']);
        //if ($check_hash == $_GET['s']) {
        if (empty($ses['member_id'])) {
            $current_url = $db->current_url();
            header('Location: ' . PP_URL . '/login.php?code=L029&r=' . urlencode($current_url));
            exit;
        }
        // }
    }

    if (empty($mem_id)) {
        $mem_id = $ses['member_id'];
    }

    $_GET['id'] = str_replace('register-', '', $_GET['id']);
    $form       = new form('', 'register', $_GET['id'], $mem_id, '1', $req_login);
    $check      = $form->check_session();

    if ($check != 1) {
        $form->start_session();
    }

    $form_session = $form->get_session();
    $use_id       = $form_session['form_id'];

    if ($req_login == '1') {
        if (empty($ses['member_id'])) {
            $db->show_error_page('F035');
            exit;
        }
    }

}

if (empty($_GET['step'])) {
    $step = $form->step;
} else {
    if ($_GET['step'] == 'membership_option') {
        $step = $_GET['step'];
    } else {
        if ($_GET['step'] > $form->step) {
            $step = $form->step;
        } else {
            $step = $_GET['step'];
        }
    }
}

if (empty($step)) {
    $step = '1';
}


// Select a registration option!
if ($this_form->formdata['type'] == 'register-paid' && (empty($form_session['products']) || $step == 'membership_option')) {

    $all_products = '';
    $cart         = new cart;
    $all_products = $form->format_products($this_form->formdata['products'], '1');
    $addons       = $form->format_products($this_form->formdata['products'], '2');

    /*
    foreach ($this_form->{'formdata'}['products'] as $aProd) {
        $aprod = $cart->get_product($aProd);
        $aChange = $aprod['data'];
        $all_products .= new template('reg_select_product_entry',$aChange,'0');
    }
    */

    /*
    $len = sizeof($all_products);
    $len1 = sizeof($addons);
    $totalProducts = $len + $len1;
    if ($totalProducts == 1) {

    }
    */

    $step_ul  = $form->generate_step_array($this_form->formdata, 'product');

    $template = 'reg_select_product';

    $changes  = array(
        'products'       => $all_products,
        'addon_products' => $addons,
        'session'        => $form->session_id,
        'salt'           => md5($form->salt),
        'step_list'      => $step_ul,
    );

    $wrapper  = new template($template, $changes, '1');

    echo $wrapper;
    exit;

} else {

    $sn      = 's' . $step;
    $data    = unserialize($form_session[$sn]);
    $page    = $use_id . '-' . $step;

    $field   = new field;
    $genform = $field->generate_form($page, $data);
    $step_ul = $form->generate_step_array($this_form->formdata, $step);

    if ($this_form->formdata['captcha'] == '1') {
        $id       = $db->issue_captcha(get_ip(), 'user');
        $cap_url  = PP_ADMIN . "/cp-functions/generate_captcha.php?c=" . $id;
        $changesX = array(
            'captcha_url' => $cap_url,
            'captcha_id'  => $id,
        );
        $captcha  = new template('captcha_block', $changesX, '0');
    } else {
        $captcha = '';
    }

    $changes = array(
        'meta_title'    => $this_form->formdata['name'],
        'form'          => $genform,
        'data'          => $this_form->formdata,
        'session'       => $form->session_id,
        'salt'          => md5($form->salt),
        'step'          => $step,
        'captcha'       => $captcha,
        'step_list'     => $step_ul,
        'pass_strength' => $db->get_option('required_password_strength'),
    );

    $wrapper = new template($temp, $changes, '1');
    echo $wrapper;
    exit;

}