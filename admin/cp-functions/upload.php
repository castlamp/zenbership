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
if (!empty($_GET['permission'])) {
    $employee = $admin->check_employee($_GET['permission']);

}
// $allowedExtensions = array('jpg','jpeg','png','gif','zip','pdf','doc','docx','odt','xlsx','csv','xltx','xml','xls','ods','txt','rtf');
// max file size in bytes
//$sizeLimit = 9437184; // 9 Mb
$exts = $db->get_option('uploads_exts');
$size = $db->get_option('uploads_max_size');
if (empty($size)) {
    $size = '5242880';
}
$allowedExtensions = explode(',', $exts);
$sizeLimit         = $size; // 5 Mb
$uploader          = new qqFileUploader($allowedExtensions, $sizeLimit);
if (! empty($_GET['attachment'])) {
    $result = $uploader->handleUpload(PP_PATH . '/admin/sd-system/attachments');
} else {
    $result = $uploader->handleUpload(PP_PATH . '/custom/uploads');
}
// to pass data through iframe you will need to encode all html tags
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
exit;



