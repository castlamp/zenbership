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

if (!empty($_POST['type'])) {
    $type = $_POST['type'];
} else {
    $type = '';
}

// Make sure it is just the filename
if (strpos($_POST['filename'], 'http://') !== false || strpos($_POST['filename'], 'https://') !== false) {
    $nf = explode('/',$_POST['filename']);
    $filename = array_pop($nf);
    $full_url = $_POST['filename'];
} else {
    if ($type == 'attachment') {
        $full_url = PP_URL . '/admin/sd-system/attachments/' . $_POST['filename'];
    } else {
        $full_url = PP_URL . '/custom/uploads/' . $_POST['filename'];
    }
    $filename = $_POST['filename'];
}

// If the image is too large, reduce size.
// Save the changes on the server.
list($width, $height, $img_type, $attr) = getimagesize($full_url);
if ($width > 1020) {
    $ratio = 1020 / $width;
    $new_width = '1020';
    $new_height = ceil($height * $ratio);
    $img = new img($filename, $new_width, $new_height, '1');
} else {
    $new_width = $width;
    $new_height = $height;
}

?>

<script type="text/javascript" src="js/jquery.imgareaselect.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        cropping = $('img#cropping').imgAreaSelect({
            handles: true,
            instance: true,
            <?php
            if ($type == 'profile-picture') {
                echo "aspectRatio: '1:1',";
            }
            else if ($type == 'event_cover_photo') {
                echo "aspectRatio: '4:1',";
            }
            else if ($type == 'event_photo') {
                echo "aspectRatio: '1.33:1',";
            }
            ?>
            onSelectEnd: function (img, selection) {
                $('input[name="x1"]').val(selection.x1);
                $('input[name="y1"]').val(selection.y1);
                $('input[name="x2"]').val(selection.x2);
                $('input[name="y2"]').val(selection.y2);
            }
        });
    });
</script>

<script type="text/javascript">
    $.ctrl('S', function () {
        return crop_image('crop', '<?php echo $_POST['id']; ?>');
    });
</script>

<form action="cp-functions/crop_image.php" id="crop_form" method="post"
      onsubmit="return crop_image('crop','<?php echo $_POST['id']; ?>');">
    <div id="popupsave">
        <input type="hidden" name="filename" value="<?php echo $filename; ?>"/>
        <input type="hidden" name="x1" value=""/>
        <input type="hidden" name="y1" value=""/>
        <input type="hidden" name="x2" value=""/>
        <input type="hidden" name="y2" value=""/>
        <a href="null.php" onclick="return crop_image('rotate','<?php echo $_POST['id']; ?>','270');"><img
                src="imgs/icon-rotate-clockwise.png" width="24" height="24" alt="Rotate Clockwise"
                title="Rotate Clockwise"/></a> <a href="null.php"
                                                  onclick="return crop_image('rotate','<?php echo $_POST['id']; ?>','90');"><img
                src="imgs/icon-rotate-counter.png" width="24" height="24" alt="Rotate Counterclockwise"
                title="Rotate Counterclockwise" style="padding-right: 16px;"/></a> <input type="submit" value="Crop"
                                                                                          class="save"/> <input
            type="button" onclick="return close_large_popup();" value="Done"/>
    </div>
</form>
<h1>Editing Image</h1>

<div class="pad24 popupbody">
    <img id="cropping" src="<?php echo $full_url; ?>" border="0" width="<?php echo $new_width; ?>" height="<?php echo $new_height; ?>" alt="Cropping Image" />
</div>