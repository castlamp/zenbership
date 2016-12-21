<?php

/**
 * Zenbership Admin Control Panel
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

/* -- Load Functions -- */
require "sd-system/config.php";
$admin = new admin;

/* -- Admin Session -- */
$employee = $admin->check_employee('', '0');

/* -- IP LOCK -- */
include "sd-system/ip_whitelist.php";
if (! empty($whitelist_ips)) {
    if (! in_array(get_ip(), $whitelist_ips)) {
        echo "You are not permitted to access this location.";
        exit;
    }
}

/* -- Correct URL format? -- */
if (strpos(PP_ADMIN, 'https://') !== false) {
    if ($db->check_ssl() != '1') {
        // Some servers cause a redirect loop if
        // there is no trailing forward slash "/"
        // at the end of the URL. So we make sure
        // we add one here.
        $use = rtrim(PP_ADMIN, '/');
        header('Location: ' . $use . '/');
        exit;
    }
}


/* -- Generate Page -- */
ob_start();
$extension = false;

// Redirect to the control panel
if (empty($_GET['l'])) {
    $lit       = PP_ADMINPATH . "/cp-includes/home.php";
    $_GET['l'] = 'home';
}

// If no location was submitted, redirect
// to the control panel homepage.
else {
    if (! empty($_GET['plugin'])) {
        $lit = PP_PATH . "/custom/plugins/" . htmlentities($_GET['plugin']) . "/admin/views/" . htmlentities($_GET['l']) . ".php";
        if (! file_exists($lit)) {
            $lit       = PP_ADMINPATH . "/cp-includes/error.php";
            $_GET['l'] = 'error';
        } else {
            $ae = new admin_extensions($_GET['l'], $employee, $_GET['plugin']);
            $content = $ae->runTask($_GET['l']);

            if (empty($content)) {
                $lit       = PP_ADMINPATH . "/cp-includes/error.php";
                $_GET['l'] = 'error';
            } else {
                $extension = true;
            }
        }
    } else {
        $lit = PP_ADMINPATH . "/cp-includes/" . $_GET['l'] . ".php";
        if (! file_exists($lit)) {
            $lit       = PP_ADMINPATH . "/cp-includes/error.php";
            $_GET['l'] = 'error';
        }
    }
}

// Print
if (! empty($_GET['print'])) {
    $printing = '1';
    $header = 'header-print';
    $footer = 'footer-print';
} else {
    if (! check_mobile()) {
        $printing = '0';
        $header = 'header';
        $footer = 'footer';
        //$header = 'header-mobile';
        //$footer = 'footer-mobile';
    } else {
        $printing = '0';
        $header = 'header-mobile';
        $footer = 'footer-mobile';
    }
}

// Continue
$task_id = $db->start_task('load_page', 'staff', $_GET['l'], $employee['username']);

if (! $extension) {
    include($lit);
    $content = ob_get_contents();
}

ob_end_clean();

$task = $db->end_task($task_id, '1');

include PP_ADMINPATH . "/cp-includes/" . $header . ".php";
echo $content;
include PP_ADMINPATH . "/cp-includes/" . $footer . ".php";
exit;