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
if (empty($_POST['id'])) {
    $admin->show_popup_error('No member selected.');
} else {
    $user = new user;
    $data = $user->get_user($_POST['id']);
    $qz   = $db->get_eav_value('options', 'member_quick_view');
    ?>

    <div id="popupsave">
        <input type="submit" value="View Full Profile" class="save"
               onclick="return load_page('member','view','<?php echo $_POST['id']; ?>');"/>
    </div>
    <h1>Member Card</h1>

    <div class="popupbody">
        <div class="pad">

        <dl class="horizontal">
            <?php
            $sp = new special_fields('member');
            $fields = explode(',', $qz);
            foreach ($fields as $item) {
                $sp->update_row($item);
                $return = $sp->process($item, $data['data'][$item]);
                $name   = $sp->clean_name($item);
                echo "
                    <dt>" . $name . "</dt>
                    <dd>" . $return . "</dd>
                ";
            }
            ?>
        </dl>
        <div class="clear"></div>
        </div>
    </div>

<?php
}
?>