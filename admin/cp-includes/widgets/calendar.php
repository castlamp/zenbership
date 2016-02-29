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


// Generate Calendar
$exp = explode(' ', current_date());
$exp_date = explode('-', $exp['0']);
if (!empty($_GET['year'])) {
    $year = $_GET['year'];
} else {
    $year = $exp_date['0'];
}
if (!empty($_GET['month'])) {
    $month = $_GET['month'];
} else {
    $month = $exp_date['1'];
}

$copts = array(
    'display' => $options['display'],
    'options' => $options['options'],
);
$calendar = new calendar($year, $month, $copts);

?>

<link href="css/calendar.css" rel="stylesheet" type="text/css"/>
<div class="nontable_section">
    <div class="pad24">
        <h2><?php echo $data['title']; ?></h2>

        <div class="nontable_section_inner">
            <div class="pad24">
                <?php
                echo $calendar->output;
                ?>
            </div>
        </div>
    </div>
</div>