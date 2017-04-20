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

require "sd-system/config.php";

if ($_GET['code'] == SALT1) {
    $admin = new admin;
    $employee = $admin->get_employee($_GET['username']);

    if (! empty($employee['id'])) {
        $db->run_query("
            UPDATE
                `ppSD_staff`
            SET
                `locked`='1920-01-01 00:01:01',
                `locked_ip`='',
                `login_attempts`='0'
            WHERE
                `id`='" . $db->mysql_clean($employee['id']) . "'
            LIMIT 1
        ");

        header('Location: ' . PP_URL . '/admin/login.php?incode=u01');
        exit;
    } else {
        header('Location: ' . PP_URL . '/admin/login.php?incode=u99');
        exit;
    }
} else {
    header('Location: ' . PP_URL . '/admin/login.php?incode=u99');
    exit;
}