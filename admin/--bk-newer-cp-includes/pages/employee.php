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
// Check permissions, ownership,
// and if it exists.
$show = '1';
$permission = 'employee';
$check = $admin->check_permissions($permission, $employee);
if ($check != '1') {
    $show  = '0';
    $error = 'permissions';
} else {
    // Ownership
    $data = $admin->get_employee('', $_POST['id']);
    if (empty($data['id'])) {
        $show  = '0';
        $error = 'noexists';
    }
}
// Show?
if ($show != '1') {
    $admin->show_no_permissions($error, '', '1');
} else {
    ?>

    <div id="slider_submit">
        <div class="pad24tb">

            <div id="topicons">
                <a href="null.php" onclick="return delete_item('ppSD_staff','<?php echo $data['id']; ?>','','1');"><img
                        src="imgs/icon-delete-on.png" border="0" title="Delete" alt="Delete" class="icon" width="16"
                        height="16"/> Delete</a>
            </div>

            <ul id="slider_tabs">
                <li id="overview" class="on">Overview</li>
                <li id="notes">Notes<a class="topright_bubble" href="returnnull.php"
                                       onclick="return popup('note-add','user_id=<?php echo $data['data']['id']; ?>&scope=employee');">+</a>
                </li>
                <li id="files">Files</li>
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

                if (!empty($data['twitter']) && $data['twitter'] != 'http://') {
                    echo "<li id=\"social_media\"><img src=\"imgs/icon-twitter.png\" width=\"16\" height=\"16\" alt=\"Twitter Feed\" title=\"Twitter Feed\" border=0 style=\"margin-top:10px;\" /></li>";
                }
                ?>
            </ul>
            <div id="slider_left">
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

                if (!empty($data['profile_picture'])) {
                    echo "<a href=\"#\" onclick=\"return popup('crop_image','id=" . $data['id'] . "&type=profile-picture&filename=" . $data['profile_picture'] . "','1');\"><img src=\"" . PP_URL . "/custom/uploads/" . $data['profile_picture'] . "\" width=48 height=48 border=0 alt=\"" . $data['last_name'] . ", " . $data['first_name'] . "\" title=\"" . $data['last_name'] . ", " . $data['first_name'] . "\" class=\"profile_pic border\" /></a>";
                } else {
                    echo "<a href=\"#\" onclick=\"return popup('profile_picture','id=" . $data['id'] . "&type=employee');\"><img src=\"" . PP_ADMIN . "/imgs/anon.png\" width=48 height=48 border=0 alt=\"" . $data['last_name'] . ", " . $data['first_name'] . "\" title=\"" . $data['last_name'] . ", " . $data['first_name'] . "\" class=\"profile_pic border\" /></a>";
                }
                ?>
                <span class="title"><?php echo $data['username']; ?></span>
                <span class="data"><?php echo $data['last_name'] . ', ' . $data['first_name']; ?></span>
            </div>
            <div class="clear"></div>
        </div>
    </div>

    <div id="primary_slider_content">
        %inner_content%
    </div>

    <script type="text/javascript" src="<?php echo PP_ADMIN; ?>/js/forms.js"></script>
    <script type="text/javascript" src="<?php echo PP_ADMIN; ?>/js/sliders.js"></script>


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

}
?>