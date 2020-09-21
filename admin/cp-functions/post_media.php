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

if (! empty($_GET['permission'])) {
    $employee = $admin->check_employee('announcements');
}

$allowedExtensions = array(
    'jpg',
    'jpeg',
    'png',
);

$sizeLimit         = '5242880'; // 5 Mb

$uploader          = new qqFileUploader($allowedExtensions, $sizeLimit);

$uploader->setFilename($_GET['id']);
$uploader->setSkipDb(true);

$result = $uploader->handleUpload(PP_PATH . '/custom/uploads', true);

echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
exit;
