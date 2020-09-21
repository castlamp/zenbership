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
// Required
if (empty($_POST['scope'])) {
    echo "No type was submitted.";
    exit;

}
// Load the basics
require "../sd-system/config.php";
$admin = new admin;
$task = 'import-' . $_POST['scope'];
// Check permissions and employee
$employee = $admin->check_employee($task);
$task_id = $db->start_task($task, 'staff', $_POST['id'], $employee['username']);
if (!empty($_POST['real'])) {
    $preview   = '0';
    $file_path = $_POST['file'];

} else {
    $preview   = '1';
    $file_path = PP_PATH . '/custom/uploads/' . $_FILES['file']['name'];
    if (! move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
        echo "Could not write import file to correct location at $file_path.";
        exit;

    }

}
$import = new import($_POST['scope'], $file_path, $_POST['delimiter'], $preview, $_POST['options']);
$task = $db->end_task($task_id, '1');
if ($import->error == '1') {
    echo "<H1>ERROR</H1>";
    echo "<p>" . $import->error_details . "</p>";
    exit;

} else {
    ?>



    <html>

    <head>

        <style type="text/css">

            body {

                margin: 24px 0;

                background-color: #f9f9f9;

            }

            input[type=submit]:hover {

                background-color: #bbffab !important;

            }

            input[type=submit] {

                padding: 12px 24px;

                color: #fff;

                -moz-box-shadow: 0px 0px 3px #000000;

                -webkit-box-shadow: 0px 0px 3px #000000;

                box-shadow: 0px 0px 3px #000000;

                margin: 0 auto;

                border: 1px solid #333;

                -moz-border-radius: 10px;

                -webkit-border-radius: 10px;

                border-radius: 10px;

                cursor: pointer;

                /*IE 7 AND 8 DO NOT SUPPORT BLUR PROPERTY OF SHADOWS*/

                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#bbffab', endColorstr='#77bf7c');

                /*INNER ELEMENTS MUST NOT BREAK THIS ELEMENTS BOUNDARIES*/

                /*Element must have a height (not auto)*/

                /*All filters must be placed together*/

                -ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr = '#bbffab', endColorstr = '#77bf7c')";

                /*Element must have a height (not auto)*/

                /*All filters must be placed together*/

                background-image: -moz-linear-gradient(top, #bbffab, #77bf7c);

                background-image: -ms-linear-gradient(top, #bbffab, #77bf7c);

                background-image: -o-linear-gradient(top, #bbffab, #77bf7c);

                background-image: -webkit-gradient(linear, center top, center bottom, from(#bbffab), to(#77bf7c));

                background-image: -webkit-linear-gradient(top, #bbffab, #77bf7c);

                background-image: linear-gradient(top, #bbffab, #77bf7c);

                /*--IE9 DOES NOT SUPPORT CSS3 GRADIENT BACKGROUNDS--*/

            }

            table#preview_table {

                font-family: arial;

                width: 100%;

                font-size: 1.0em;

                border-top: 1px solid #e1e1e1;

                border-left: 1px solid #e1e1e1;

            }

            table#preview_table th {

                text-align: left;

                padding: 8px;

                font-weight: bold;

                border-bottom: 2px solid #e1e1e1;

                border-right: 1px solid #e1e1e1;

                background-color: #f1f1f1;

            }

            table#preview_table td {

                text-align: left;

                padding: 8px;

                border-bottom: 1px solid #e1e1e1;

                border-right: 1px solid #e1e1e1;

            }

            table#preview_table tr.skipping {

                background-color: #ccc;

                color: #666;

                font-style: italic;

            }

            table#preview_table tr.updating {

                background-color: #FFFBBD;

            }

            .holder {

                font-family: arial;

                font-size: 0.8em;

                margin: 0 auto;

                color: #666;

                background-color: #fff;

                border: 1px solid #333;

                width: 95%;

                -moz-border-radius: 10px;

                -webkit-border-radius: 10px;

                border-radius: 10px;

                /*IE 7 AND 8 DO NOT SUPPORT BORDER RADIUS*/

                -moz-box-shadow: 0px 0px 10px #e1e1e1;

                -webkit-box-shadow: 0px 0px 10px #e1e1e1;

                box-shadow: 0px 0px 10px #e1e1e1;

                /*IE 7 AND 8 DO NOT SUPPORT BLUR PROPERTY OF SHADOWS*/

            }

            .holder_in {

                padding: 20px;

            }

        </style>

    </head>

    <body>

    <div class="holder">

        <div class="holder_in">


            <?php

            if ($preview != '1') {
                if ($_POST['scope'] == 'contact') {
                    $url = PP_URL . '/admin/index.php?l=contacts';

                } else if ($_POST['scope'] == 'member') {
                    $url = PP_URL . '/admin/index.php?l=members';

                } else if ($_POST['scope'] == 'account') {
                    $url = PP_URL . '/admin/index.php?l=accounts';

                } else if ($_POST['scope'] == 'rsvp') {
                    $url = PP_URL . '/admin/index.php?l=events';

                } else if ($_POST['scope'] == 'transaction') {
                    $url = PP_URL . '/admin/index.php?l=transactions';

                } else {
                    $url = PP_URL . '/admin/';

                }
                echo "<a href=\"" . $url . "\">Import Complete: click here to return to the control panel.</a>";

            }

            echo $import->output;

            if ($preview != '1') {
                if (!unlink($file_path)) {
                    echo "<hr><font color=red>WARNING: Unable to delete file. Please delete the file manually from: $file_path</font>";

                }

            }

            ?>


        </div>

    </div>

    </body>

    </html>



    <?php

    exit;

}

