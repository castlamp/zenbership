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

require "admin/sd-system/config.php";

$session = new session;
$check = $session->check_session();

if ($check['error'] == '1') {

    $changes = array();
    if (!empty($_GET['r'])) {
        $options = array(
            'https://',
            'http://',
            '://',
            '//',
        );
        if (substr(PP_URL, 0, 8) == 'https://') {
            $rep = 'https://';
        } else {
            $rep = 'http://';
        }
        $r = $rep . str_replace($options, '', $_GET['r']);
        $changes['url'] = $r;
    } else {
        $changes['url'] = '';
    }

    $template = new template('login', $changes, '1');

    echo $template;
    exit;

} else {

    header('Location: ' . PP_URL . '/manage');
    exit;

}