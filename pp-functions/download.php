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

if (! empty($_GET['id'])) {
    require "../admin/sd-system/config.php";
    $session = new session;
    $ses     = $session->check_session();
    if ($ses['error'] == '1') {
        $result = array('error' => 'You must be logged in to upload files.');
        echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
        exit;
    }
    $task_id = $db->start_task('download', 'user', '', $ses['member_id']);

    $upload = new uploads();
    $get = $upload->get_upload($_GET['id']);

    $path = PP_PATH . '/custom/uploads/' . $get['filename'];

    // Download the file.
    $mm_type = "application/octet-stream";
    header("Content-type: application/force-stream");
    header("Content-Transfer-Encoding: Binary");
    header("Content-length: " . filesize($path) );
    header("Content-disposition: attachment; filename=\"" . basename($get['name']) . "\"");
    readfile($path);
    exit;

}