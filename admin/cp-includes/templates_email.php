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
$theme = $db->get_option('theme_emails');
$permission = 'theme_emails';
$check = $admin->check_permissions('theme_emails', $employee);
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

                <span><b>E-Mail Templates</b></span>

                <span class="div">|</span>

        <span id="innerLinks">

            <a href="null.php" onclick="return popup('theme-email','','1');">Theme</a>

            <a href="index.php?l=templates_html">HTML Templates</a>

            <a href="null.php" onclick="return popup('template_email_add','','1');">Create Custom Template</a>

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

                <th>Subject</th>

                <th width="24">&nbsp;</th>

                </thead>

                <tbody>


                <?php



                $STH = $db->run_query("

                SELECT *

                FROM `ppSD_templates_email`

                WHERE `theme`='" . $db->mysql_clean($theme) . "' OR (`theme`='' OR `theme` IS NULL)

                ORDER BY `custom` ASC, `title` ASC

            ");

                while ($row = $STH->fetch()) {
                    if ($row['custom'] != '1') {
                        $type = 'Template';
                        $del  = '';

                    } else {
                        $type = 'Custom Template';
                        $del  = '<a href="return_null.php" onclick="return delete_item(\'ppSD_templates_email\',\'' . $row['template'] . '\');"><img src="imgs/icon-delete.png" width="16" height="16" border="0" class="option_icon" alt="Delete" title="Delete" /></a>';

                    }
                    echo "<tr id=\"td-cell-" . $row['template'] . "\">

                <td><a href=\"returnnull.php\" onclick=\"return popup('template_email_edit','id=" . $row['template'] . "','1');\">" . $row['title'] . "</a></td>

                <td>$type</td>

                <td>" . $row['subject'] . "</td>

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
