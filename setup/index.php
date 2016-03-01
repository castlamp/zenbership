<?php

/**
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

$error = '0';
$path = str_replace('/setup', '', dirname(__FILE__));
require "assets/header.php";

?>

    <div class="col50l">

        <fieldset class="blue">
            <legend>Write Permissions</legend>

            <p class="desc">All of the following folders need to be "writable" during the setup process. If they appear in <span class="bad">red</span> below, please set
                permissions on each to "777", reload the page, and try again.</p>

            <ul class="form">
                <li>
                    <?php
                    echo $path;
                    ?>
                </li>
                <li class="indent">
                    <?php
                    if (!is_writable($path . '/admin/sd-system')) {
                        $class = 'bad';
                        $error = '1';
                    } else {
                        $class = 'good';
                    }
                    echo "<span class=$class>admin/sd-system</span>";
                    ?>
                </li>
                <li class="indent">
                    <?php
                    if (!is_writable($path . '/admin/sd-system/attachments')) {
                        $class = 'bad';
                        $error = '1';
                    } else {
                        $class = 'good';
                    }
                    echo "<span class=$class>admin/sd-system/attachments</span>";
                    ?>
                </li>
                <li class="indent">
                    <?php
                    if (!is_writable($path . '/admin/sd-system/exports')) {
                        $class = 'bad';
                        $error = '1';
                    } else {
                        $class = 'good';
                    }
                    echo "<span class=$class>admin/sd-system/exports</span>";
                    ?>
                </li>
                <li class="indent">
                    <?php
                    if (!is_writable($path . '/custom/sessions')) {
                        $class = 'bad';
                        $error = '1';
                    } else {
                        $class = 'good';
                    }
                    echo "<span class=$class>custom/sessions</span>";
                    ?>
                </li>
                <li class="indent">
                    <?php
                    if (!is_writable($path . '/custom/qrcodes')) {
                        $class = 'bad';
                        $error = '1';
                    } else {
                        $class = 'good';
                    }
                    echo "<span class=$class>custom/qrcodes</span>";
                    ?>
                </li>
                <li class="indent">
                    <?php
                    if (!is_writable($path . '/custom/uploads')) {
                        $class = 'bad';
                        $error = '1';
                    } else {
                        $class = 'good';
                    }
                    echo "<span class=$class>custom/uploads</span>";
                    ?>
                </li>
            </ul>

        </fieldset>


        <p class="help">Need help installing the program?
        <br /><br /><a href="http://documentation.zenbership.com/Basics/Installation-and-Setup" target="_blank">Click here</a> for documentation
        <br /><a href="http://www.zenbership.com/Services/Installations-And-Implementations" target="_blank">Click here</a> for information on having our staff install or implement it for you.</p>


    </div>
    <div class="col50r">

        <?php
        if ($error == '1') {
            ?>

            <fieldset class="red">
                <legend>Overview</legend>

                <p class="bad">Please fix the errors before continuing...</p>

            </fieldset>

        <?php
        } else {
            ?>

            <fieldset class="green">
                <legend>MySQL Database</legend>

                <p class="desc">A MySQL database is required to run the program. This database must be <b>created prior
                        to running the setup</b>. You can create a database from your website's control panel, or by
                    contacting your web hosting provider.</p>

                <ul class="form">
                    <li>
                        <label>Server Host</label>
                        <input type="text" name="mysql[host]" value="localhost" autocomplete="off" style="width:200px;"/>
                    </li>
                    <li>
                        <label>Database Name</label>
                        <input type="text" name="mysql[db]" value="" autocomplete="off" style="width:200px;"/>
                    </li>
                    <li>
                        <label>Username</label>
                        <input type="text" name="mysql[user]" value="" autocomplete="off" style="width:200px;"/>
                    </li>
                    <li>
                        <label>Password</label>
                        <input type="text" name="mysql[pass]" value="" autocomplete="off" style="width:200px;"/>
                    </li>
                </ul>

            </fieldset>

            <fieldset class="red">
                <legend>Company and Program Basics</legend>

                <p class="desc">Please provide some basic information about your company and membership site.</p>

                <ul class="form">
                    <li>
                        <label>Company Name</label>
                        <input type="text" name="company_name" placeholder="Acme Inc." autocomplete="off" value="" style="width:200px;"/>
                    </li>
                    <li>
                        <label>Company URL</label>
                        <input type="text" name="company_url" placeholder="http://www.yoursite.com/" autocomplete="off" value="" style="width:200px;"/>
                    </li>
                    <li>
                        <label>Company E-Mail</label>
                        <input type="text" name="company_email" placeholder="info@yoursite.com" autocomplete="off" value="" style="width:200px;"/>

                        <p class="field_desc">Generic contact e-mail for your company.</p>
                    </li>
                    <li>
                        <label>Company Logo</label>
                        <input type="text" name="company_logo" autocomplete="off" placeholder="http://www.yoursite.com/imgs/logo.png" value="" style="width:200px;"/>

                        <p class="field_desc">Input as a full URL. Example: http://www.mysite.com/imgs/logo.png</p>
                    </li>
                    <li>
                        <label>Membership Site Name</label>
                        <input type="text" name="site_name" autocomplete="off" placeholder="My Membership Site" value="" style="width:200px;"/>

                        <p class="field_desc">This is what your members will see in the header of the program's frontend when they access your membership website.</p>
                    </li>
                    <li>
                        <label>Time Adjustment</label>
                        <select name="hour_change">
                            <option>-11</option>
                            <option>-10</option>
                            <option>-9</option>
                            <option>-8</option>
                            <option>-7</option>
                            <option>-6</option>
                            <option>-5</option>
                            <option>-4</option>
                            <option>-3</option>
                            <option>-2</option>
                            <option>-1</option>
                            <option selected="selected">0</option>
                            <option>+1</option>
                            <option>+2</option>
                            <option>+3</option>
                            <option>+4</option>
                            <option>+5</option>
                            <option>+6</option>
                            <option>+7</option>
                            <option>+8</option>
                            <option>+9</option>
                            <option>+10</option>
                            <option>+11</option>
                        </select>

                        <p class="field_desc">Your server is reporting the current time as <?php echo date('g:ia'); ?>.
                            You can change this above to reflect your local time.</p>
                    </li>
                </ul>

            </fieldset>


            <fieldset class="orange">
                <legend>Master Administrator</legend>

                <p class="desc">The master administrator is the primary administrator for the admininstrative dashboard. There
                    are no limitations on the master administrator's privileges. Input your basic information as well as
                    your desired username and password for this user.</p>

                <ul class="form">
                    <li>
                        <label>First Name</label>
                        <input type="text" name="admin[first_name]" autocomplete="off" value="" style="width:200px;"/>
                    </li>
                    <li>
                        <label>Last Name</label>
                        <input type="text" name="admin[last_name]" autocomplete="off" value="" style="width:200px;"/>
                    </li>
                    <li>
                        <label>E-Mail</label>
                        <input type="text" name="admin[email]" autocomplete="off" value="" style="width:200px;"/>
                    </li>
                    <li>
                        <label>Username</label>
                        <input type="text" name="admin[user]" autocomplete="off" value="" style="width:200px;"/>
                    </li>
                    <li>
                        <label>Password</label>
                        <input type="text" name="admin[pass]" autocomplete="off" value="" style="width:200px;"/>
                        <p class="field_desc"><b>Important: your password will be visible as you type!</b></p>
                    </li>
                </ul>

            </fieldset>

            <fieldset class="">
            <legend>Anonymous Statistics?</legend>

                <p class="desc">In order to better understand how our software is used, we provide our users with the option to "opt in" to our
                    statistical collection program. This program is <b>completely anonymous</b> and only provides us with information
                    like total transactions, sales, events, members, and contacts managed.</p>

                <ul class="form">
                    <li>
                <input type="checkbox" name="enroll_stats" value="1" /> Opt-in to the anonymous stat collection program.
                    </li>
                    </ul>
            </fieldset>

        <?php
        }
        ?>

    </div>
    <div class="clear"></div>

    </div>

<?php
if ($error != '1') {
    ?>
    <div class="submit">
        <input type="submit" value="Process Setup"/>
    </div>
<?php
}
?>
    </form>

<?php
require "assets/footer.php";
?>