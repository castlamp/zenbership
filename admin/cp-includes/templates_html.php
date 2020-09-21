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
$theme = $db->get_option('theme');
$permission = 'theme';
$check = $admin->check_permissions('theme', $employee);
if ($check != '1') {
    $admin->show_no_permissions();

} else {
    ?>



    <div id="topblue" class="fonts small">
        <div class="holder">

            <div class="floatright" id="tb_right">

                &nbsp;

            </div>

            <div class="floatleft" id="tb_left">

                <span><b>HTML Templates</b></span>

                <span class="div">|</span>

        <span id="innerLinks">

            <a href="returnnull.php" onclick="return popup('theme','','1');">Theme</a>

            <a href="index.php?l=templates_email">E-Mail Templates</a>

            <!--<a href="returnnull.php" onclick="return popup('template_html-add','','1');">Create Custom Template</a>-->

            <a href="index.php?l=content&filters[]=page||type||eq||ppSD_content">Custom Pages</a>

        </span>

            </div>

            <div class="clear"></div>

        </div>
    </div>



    <div id="mainsection">

        <form id="table_checkboxes">

            <table class="tablesorter listings" id="active_table" border="0">
                <thead>

                <th class="first">Template</th>

                <th>Type</th>

                <th>Section</th>

                <th width="24">&nbsp;</th>

                </thead>

                <tbody>


                <?php



                $STH = $db->run_query("

                SELECT *

                FROM `ppSD_templates`

                WHERE `theme`='" . $db->mysql_clean($theme) . "' AND `type`!='4'

                ORDER BY `section` ASC, `type` DESC, `title` ASC

            ");

                while ($row = $STH->fetch()) {
                    if ($row['type'] == '1') {
                        $type = 'Header';
                        $del  = '';

                    } else if ($row['type'] == '2') {
                        $type = 'Footer';
                        $del  = '';

                    } else if ($row['type'] == '3') {
                        $type = 'Custom Template';
                        $del  = '<a href="return_null.php" onclick="return delete_item(\'ppSD_templates\',\'' . $row['id'] . '\');"><img src="imgs/icon-delete.png" width="16" height="16" border="0" class="option_icon" alt="Delete" title="Delete" /></a>';

                    } else {
                        $type = 'Template';
                        $del  = '';

                    }
                    // (<a href=\"returnnull.php\" onclick=\"return popup('template_edit','id=" . $row['id'] . "','1');\">Overview</a> | <a href=\"returnnull.php\" onclick=\"window.open('" . PP_URL . "/admin/cp-includes/editor/window.php?id=" . $row['id'] . "','','width=1020px,height=850px');return false;\">Edit HTML</a>)
                    echo "<tr>

                <td><a href=\"null.php\" onclick=\"return popup('template_edit','id=" . $row['id'] . "','1')\">" . $row['title'] . "</a></td>

                <td>$type</td>

                <td>" . $row['section'] . "</td>

                <td>$del</td>

                </tr>";

                }

                /*

            <tr>

            <td></td>

            <td></td>

            <td></td>

            <td></td>

            </tr>

                */


                ?>


                </tbody>

            </table>

        </form>


    </div>



<?php

}

?>
