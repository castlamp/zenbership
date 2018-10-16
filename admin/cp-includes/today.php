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

$permission = 'today';
$check = $admin->check_permissions($permission, $employee);
if ($check != '1') {
    $admin->show_no_permissions();
} else {

?>


<div id="topblue" class="fonts small">
    <div class="holder">
        <div class="floatright" id="tb_right">
            <?php
            echo '<span><a href="' . $nextprev['prev_link'] . '">' . $nextprev['prev_month'] . '</a></span>';
            echo '<span class="div"></span>';
            echo '<span><b>' . $nextprev['title'] . '</b></span>';
            echo '<span class="div"></span>';
            echo '<span><a href="' . $nextprev['next_link'] . '">' . $nextprev['next_month'] . '</a></span>';
            ?>
        </div>
        <div class="floatleft" id="tb_left">
            <b>Daily Planner</b>
            <?php
            include PP_PATH . "/admin/cp-includes/user_link_menu.php";
            ?>
        </div>
        <div class="clear"></div>
    </div>
</div>

<link rel="stylesheet" type="text/css" media="all" href="css/calendar.css"/>
<div id="mainsection">
    <div class="nontable_section">
        <div class="pad24">
            <h1>Daily Planner (<?php echo $nextprev['title']; ?>)</h1>
            <div class="nontable_section_inner">
                <div class="col66l"><div class="pad24_fs_l">
                    <?php

                    ?>
                </div></div>
                <div class="col33r"><div class="pad24_fs_r">
                    <h2>Notes</h2>
                    <?php

                    ?>
                </div></div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</div>

<?php
}
?>
