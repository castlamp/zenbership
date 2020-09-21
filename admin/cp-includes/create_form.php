<?php


/**
 * Form creation.
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
?>



    <link type="text/css" rel="stylesheet" media="all" href="css/fields_sortable.css"/>



<?php



// 1 = standard
// 2 = events
if (empty($form_type)) {
    $form_type = '2';

}
if ($multi_col == '1') {
    ?>





    <div class="col50" id="col1" style="position:relative;">

        <div class="pad24t">


            <h1><?php if (!empty($col1_name)) {
                    echo $col1_name;
                } else {
                    echo 'Column 2';
                } ?></h1>


            <ul id="col1_fields" class="colfields">

                <?php

                if (!empty($col1_load)) {
                    echo "<script type=\"text/javascript\">";
                    echo "$(document).ready(function() {";
                    echo "  build_form('" . $col1_load . "','1');";
                    echo "});";
                    echo "</script>";

                } else {
                    echo "<li class=\"removefield\">Click \"Add a Field\" below to begin building the form.</li>";

                }

                ?>

            </ul>


            <center><a href="prevent_null.php" onclick="return prep_add('1','<?php echo $form_type; ?>');">[+] Add a
                    Field</a></center>


        </div>

    </div>



    <div class="col50" id="col2" style="position:relative;">

        <div class="pad24t">


            <h1><?php if (!empty($col2_name)) {
                    echo $col2_name;
                } else {
                    echo 'Column 2';
                } ?></h1>


            <ul id="col2_fields" class="colfields">

                <?php

                if (!empty($col2_load)) {
                    echo "<script type=\"text/javascript\">";
                    echo "$(document).ready(function() {";
                    echo "  build_form('" . $col2_load . "','2');";
                    echo "});";
                    echo "</script>";

                } else {
                    echo "<li class=\"removefield\">Click \"Add a Field\" below to begin building the form.</li>";

                }

                ?>

            </ul>


            <center><a href="prevent_null.php" onclick="return prep_add('2','<?php echo $form_type; ?>');">[+] Add a
                    Field</a></center>


        </div>

    </div>

    <div class="clear"></div>



<?php

} else {
    ?>



    <ul id="col1_fields" class="colfields">

        <?php

        if (!empty($col1_load)) {
            echo "<script type=\"text/javascript\">";
            echo "$(document).ready(function() {";
            echo "  build_form('" . $col1_load . "','1');";
            echo "});";
            echo "</script>";

        } else {
            echo "<li class=\"removefield\">Click \"Add a Field\" below to begin building the form.</li>";

        }

        ?>

    </ul>



    <center><a href="null.php" onclick="return prep_add('1','<?php echo $form_type; ?>');">[+] Add a Field</a></center>





<?php

}

?>
