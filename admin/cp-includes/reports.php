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
$permission = 'form';
$check = $admin->check_permissions($permission, $employee);
if ($check != '1') {
    $admin->show_no_permissions();

} else {
    $table         = 'ppSD_criteria_cache';
    $order         = 'ppSD_criteria_cache.name';
    $dir           = 'ASC';
    $display       = '50';
    $page          = '1';
    $defaults      = array(
        'sort'    => $order,
        'order'   => $dir,
        'page'    => $page,
        'display' => $display,
        'filters' => array(),
    );
    $force_filters = array(
        '1||save||eq||ppSD_criteria_cache',
    );
    $gen_table     = $admin->get_table($table, $_GET, $defaults, $force_filters);





    ?>



    <form action="cp-includes/get_table.php" id="table_filters" method="post" onsubmit="return update_table();">

        <input type="hidden" name="order" value="<?php echo $gen_table['order']; ?>"/>

        <input type="hidden" name="dir" value="<?php echo $gen_table['dir']; ?>"/>

        <input type="hidden" name="menu" value="<?php echo $gen_table['menu']; ?>"/>

        <input type="hidden" name="table" value="<?php echo $table; ?>"/>

        <input type="hidden" name="permission" value="<?php echo $permission; ?>"/>

        <div id="topblue" class="fonts small">
            <div class="holder">

                <div class="floatright" id="tb_right">
                    <?php
                    include dirname(__FILE__) . '/pagination_display.php';
                    ?>
                </div>

                <div class="floatleft" id="tb_left">

                    <span><b>Listing Reports</b></span>

                    <!--
                    <span class="div">|</span>

                    <a href="null.php" onclick="return show_filters();">Filters<img src="imgs/down-arrow.png"
                                                                                    id="filter_arrow" width="10" height="10"
                                                                                    alt="Expand" border="0"
                                                                                    class="icon-right"/></a>
                                                                                    -->

                    <span class="div">|</span>

			<span id="innerLinks">

			</span>

                </div>

                <div class="clear"></div>

            </div>
        </div>


    </form>



    <div id="mainsection">

        <form id="table_checkboxes">

            <table class="tablesorter listings" id="active_table" border="0">

                <?php

                echo $gen_table['th'];

                echo $gen_table['td'];

                ?>

            </table>


            <div id="bottom_delete">
                <div class="pad16"><span class="small gray caps bold"
                                         style="margin-right:24px;">With Selected:</span><input type="button"
                                                                                                value="Delete"
                                                                                                class="del"
                                                                                                onclick="return compile_delete('<?php echo $table; ?>','table_checkboxes');"/>
                </div>
            </div>

        </form>

    </div>



<?php

}
