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

require "../admin/sd-system/config.php";

$session = new session;
$ses     = $session->check_session();

// Get the form data.
$form     = new form($use_ses);
$formdata = $form->formdata;

// Check CAPTCHA
$holdcaptcha = '';
$captcha_bypass = $form->captcha_bypass();

/*
if (! empty($formdata['captcha']) && $formdata['captcha'] == '1') {
    $check = $db->check_captcha(get_ip(), 'user', $_POST['captcha']);
    if ($check == '1') {
        $cook_name = md5(get_ip() . md5(date('Y-m')));
        $cook_value = md5(get_ip() . ZEN_SECRET_PHRASE . PP_PATH);

        $db->create_cookie($cook_name, $cook_value);

        $holdcaptcha = $_POST['captcha'];

        unset($_POST['captcha']);
    } else {
        $db->show_error_page('F040');
        exit;
    }
}
*/