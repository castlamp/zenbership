<?php

/**
 * Controls all user submitted uploads.
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
if ($ses['error'] == '1') {
    $result = array('error' => 'You must be logged in to upload files.');
    echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
    exit;
}
$task_id = $db->start_task('upload_file', 'user', '', $ses['member_id']);
$exts    = $db->get_option('uploads_exts');
$size    = $db->get_option('uploads_max_size');
if (empty($size)) {
    $size = '5242880';
}
$allowedExtensions = explode(',', $exts);

// max file size in bytes
$sizeLimit          = $size; // 5 Mb
$_GET['type']       = '';

// Uploading to account or to
// the membership?
if ($_GET['account'] == sha1(md5($ses['member_id']) . md5($ses['id']))) {
    $user = new user;
    $account = $user->get_member_account($ses['member_id']);
    if (! empty($account)) {
        $_GET['id'] = $account;
    } else {
        $_GET['id'] = $ses['member_id'];
    }
} else {
    $_GET['id'] = $ses['member_id'];
}

$_GET['permission'] = '';
if (empty($_GET['label'])) {
    $_GET['label']      = 'user_upload';
}
$_GET['scope']      = '0';
$uploader           = new qqFileUploader($allowedExtensions, $sizeLimit);
$result             = $uploader->handleUpload(PP_PATH . '/custom/uploads');

// to pass data through iframe you will need to encode all html tags
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
exit;
