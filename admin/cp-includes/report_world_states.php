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
 * @license     GNU General Public License v3.0
 * @link        http://www.gnu.org/licenses/gpl.html
 * @date        7/24/13 12:47 AM
 * @version     v1.0
 * @project     
 */

$region = 'States';

$permission = 'cart-state_distribution';
$check = $admin->check_permissions($permission,$employee);
if ($check != '1') {
    $admin->show_no_permissions();
} else {

// Date range
$date = current_date();
$exp = explode(' ', $date);
$exp_date = explode('-', $exp['0']);
if (! empty($_GET['start_date'])) {
    $start = $_GET['start_date'];
} else {
    $start = '1998-01-01';
}
if (! empty($_GET['end_date'])) {
    $end = $_GET['end_date'];
} else {
    $end = '2040-01-01';
}

if ($start > $end) {
    $temp = $start;
    $start = $end;
    $end = $temp;
}

?>

<link rel="stylesheet" media="all" href="css/jquery-jvectormap.css"/>

<!--<script src="assets/jquery-1.8.2.js"></script>-->

<script src="js/jvectormap/jquery-jvectormap.js"></script>
<script src="js/jvectormap/jquery-mousewheel.js"></script>

<script src="js/jvectormap/jvectormap.js"></script>

<script src="js/jvectormap/abstract-element.js"></script>
<script src="js/jvectormap/abstract-canvas-element.js"></script>
<script src="js/jvectormap/abstract-shape-element.js"></script>

<script src="js/jvectormap/svg-element.js"></script>
<script src="js/jvectormap/svg-group-element.js"></script>
<script src="js/jvectormap/svg-canvas-element.js"></script>
<script src="js/jvectormap/svg-shape-element.js"></script>
<script src="js/jvectormap/svg-path-element.js"></script>
<script src="js/jvectormap/svg-circle-element.js"></script>

<script src="js/jvectormap/vml-element.js"></script>
<script src="js/jvectormap/vml-group-element.js"></script>
<script src="js/jvectormap/vml-canvas-element.js"></script>
<script src="js/jvectormap/vml-shape-element.js"></script>
<script src="js/jvectormap/vml-path-element.js"></script>
<script src="js/jvectormap/vml-circle-element.js"></script>

<script src="js/jvectormap/vector-canvas.js"></script>
<script src="js/jvectormap/simple-scale.js"></script>
<script src="js/jvectormap/numeric-scale.js"></script>
<script src="js/jvectormap/ordinal-scale.js"></script>
<script src="js/jvectormap/color-scale.js"></script>
<script src="js/jvectormap/data-series.js"></script>
<script src="js/jvectormap/proj.js"></script>
<script src="js/jvectormap/world-map.js"></script>

<script src="js/jvectormap/jquery-jvectormap-us-aea-en.js"></script>
<script src="js/jvectormap/jquery-jvectormap-ca-lcc-en.js"></script>
<script src="js/jvectormap/jquery-jvectormap-au-mill-en.js"></script>

<?php
    $values = '';
    $values_contacts = '';
    $state_list = '';
    $state_list_contacts = '';
    $where_add = '';
    $where_add1 = '';

    if ($employee['permissions']['admin'] != '1') {
        $where_add = " AND (ppSD_members.owner='" . $employee['id'] . "' OR ppSD_members.public='1')";
        $where_add1 = " AND (ppSD_contacts.owner='" . $employee['id'] . "' OR ppSD_contacts.public='1')";
    }

    $ca_provinces = provinces_canada();
    $aus_states = states_australia();

    $total = $db->get_array("
        SELECT
          COUNT(*)
        FROM
          `ppSD_member_data`
        JOIN
            ppSD_members
        ON
            ppSD_members.id=ppSD_member_data.member_id
        WHERE
            `state`!='' AND
            ( ppSD_members.joined>='" . $db->mysql_cleans($start) . "' AND ppSD_members.joined<='" . $db->mysql_cleans($end) . "' )
            $where_add
   ");
    $q1 = $db->run_query("
        SELECT
            UPPER(state) AS state,
            count(state) AS state_totals
        FROM `ppSD_member_data`
        JOIN
            ppSD_members
        ON
            ppSD_members.id=ppSD_member_data.member_id
        WHERE
            `country`!='' AND
            ( ppSD_members.joined>='" . $db->mysql_cleans($start) . "' AND ppSD_members.joined<='" . $db->mysql_cleans($end) . "' )
            $where_add
        GROUP BY `state`
        ORDER BY state_totals DESC
    ");
    while ($row = $q1->fetch()) {

        if (in_array($row['state'], $ca_provinces)) { $prefix = 'CA-'; }
        else if (in_array($row['state'], $aus_states)) { $prefix = 'AU-'; }
        else {  $prefix = 'US-'; }

        $values .= '"' . $prefix . $row['state'] . '": ' . $row['state_totals'] . ",\n";
        $math = ($row['state_totals'] / $total['0']) * 100;
        $percent = number_format($math, 2);
        $state_list .= "<dt><a href=\"index.php?l=members&filters[]=" . $row['state'] . "||state||eq||ppSD_member_data\">" . $row['state'] . "</a></dt>";
        $state_list .= "<dd>
            <span class=\"tcol0\">" . $row['state_totals'] . "</span>
            <span class=\"tcol1\">" . $percent . "%</span>
        </dd>";
    }


    $total_contacts = $db->get_array("
            SELECT
              COUNT(*)
            FROM
              `ppSD_contact_data`
            JOIN
                `ppSD_contacts`
            ON
                ppSD_contacts.id=ppSD_contact_data.contact_id
            WHERE
                `state`!='' AND
              ( ppSD_contacts.created>='" . $db->mysql_cleans($start) . "' AND ppSD_contacts.created<='" . $db->mysql_cleans($end) . "' )
              $where_add1
        ");
    $q2 = $db->run_query("
            SELECT
                UPPER(state) AS state,
                count(state) AS state_totals
            FROM `ppSD_contact_data`
            JOIN
                `ppSD_contacts`
            ON
                ppSD_contacts.id=ppSD_contact_data.contact_id
            WHERE
              `state`!='' AND
              ( ppSD_contacts.created>='" . $db->mysql_cleans($start) . "' AND ppSD_contacts.created<='" . $db->mysql_cleans($end) . "' )
              $where_add1
            GROUP BY `state`
            ORDER BY state_totals DESC
        ");
    while ($row = $q2->fetch()) {

        if (in_array($row['state'], $ca_provinces)) { $prefix = 'CA-'; }
        else if (in_array($row['state'], $aus_states)) { $prefix = 'AU-'; }
        else {  $prefix = 'US-'; }

        $values_contacts .= '"' . $prefix . $row['state'] . '": ' . $row['state_totals'] . ",\n";

        $math = ($row['state_totals'] / $total_contacts['0']) * 100;
        $percent = number_format($math, 2);
        $state_list_contacts .= "<dt><a href=\"index.php?l=contacts&filters[]=" . $row['state'] . "||state||eq||ppSD_contact_data\">" . $row['state'] . "</a></dt>";
        $state_list_contacts .= "<dd>
                <span class=\"tcol0\">" . $row['state_totals'] . "</span>
                <span class=\"tcol1\">" . $percent . "%</span>
            </dd>";
    }
?>

<!--
<script>
jQuery.noConflict();
var countryData = {
    <?php
    echo $values;
    ?>
};
var countryDataContacts = {
    <?php
    echo $values_contacts;
    ?>
};
jQuery(function($) {
    var $ = jQuery;
    jQuery('#map1').vectorMap({
        map: 'us_aea_en',
        series: {
            regions: [{
                scale: ['#ACDC77', '#57A500'],
                normalizeFunction: 'polynomial',
                values: countryData
            }]
        },
        onRegionLabelShow: function(e, el, code){
            el.html(el.html()+' (Members: ' + countryData[code] + ')');
        }
    });
    jQuery('#map2').vectorMap({
        map: 'ca_lcc_en',
        series: {
            regions: [{
                scale: ['#6CACED', '#0052A6'],
                normalizeFunction: 'polynomial',
                values: countryData
            }]
        },
        onRegionLabelShow: function(e, el, code){
            el.html(el.html()+' (Members: ' + countryData[code] + ')');
        }
    });
    jQuery('#map3').vectorMap({
        map: 'au_mill_en',
        series: {
            regions: [{
                scale: ['#EEA5AA', '#CF2E38'],
                normalizeFunction: 'polynomial',
                values: countryData
            }]
        },
        onRegionLabelShow: function(e, el, code){
            el.html(el.html()+' (Members: ' + countryData[code] + ')');
        }
    });
})
jQuery(function($) {
    var $ = jQuery;
    jQuery('#map1c').vectorMap({
        map: 'us_aea_en',
        series: {
            regions: [{
                scale: ['#ACDC77', '#57A500'],
                normalizeFunction: 'polynomial',
                values: countryDataContacts
            }]
        },
        onRegionLabelShow: function(e, el, code){
            el.html(el.html()+' (Contacts: ' + countryDataContacts[code] + ')');
        }
    });
    jQuery('#map2c').vectorMap({
        map: 'ca_lcc_en',
        series: {
            regions: [{
                scale: ['#6CACED', '#0052A6'],
                normalizeFunction: 'polynomial',
                values: countryDataContacts
            }]
        },
        onRegionLabelShow: function(e, el, code){
            el.html(el.html()+' (Contacts: ' + countryDataContacts[code] + ')');
        }
    });
    jQuery('#map3c').vectorMap({
        map: 'au_mill_en',
        series: {
            regions: [{
                scale: ['#EEA5AA', '#CF2E38'],
                normalizeFunction: 'polynomial',
                values: countryDataContacts
            }]
        },
        onRegionLabelShow: function(e, el, code){
            el.html(el.html()+' (Contacts: ' + countryDataContacts[code] + ')');
        }
    });
})
</script>
-->


<form action="index.php" method="get">
<input type="hidden" name="l" value="<?php echo $_GET['l']; ?>" />
<div id="topblue" class="fonts small"><div class="holder">
    <div class="floatright" id="tb_right">
        Region: <?php echo $region; ?> | Members: <?php echo $total['0']; ?> | Contacts: <?php echo $total_contacts['0']; ?> | Range: <?php
        $datepickstart = $admin->datepicker('start_date', $start, '0', '100');
        $datepickend = $admin->datepicker('end_date', $end, '0', '100');
        echo $datepickstart; ?> to <?php echo $datepickend; ?> <input type="submit" value="Go" class="blue " />
    </div>
    <div class="floatleft" id="tb_left">
        <b>Report: Geographical Distribution</b>
    </div>
    <div class="clear"></div>
</div></div>
</form>

<div id="mainsection">

<div class="nontable_section" style="margin-bottom: -42px;">
    <div class="pad24notop">
        <h1>Geographical Distribution (Region: <?php echo $region; ?> | Range: <?php echo $start; ?> to <?php echo $end; ?>)</h1>
    </div>
</div>

    <div class="nontable_section">
        <div class="pad24">

            <h2 class="">Members</h2>
            <div class="nontable_section_inner">
                <div class="pad24">

                    <!--<div class="col66l">
                        <div id="map1" style="width:100%;height:500px;"></div>
                        <div id="map2" style="width:100%;height:500px;"></div>
                        <div id="map3" style="width:100%;height:500px;"></div>
                    </div>
                    <div class="col33">-->
                        <dl class="">
                            <dt>State</dt>
                            <dd>
                                <span class="tcol0"><b>Total</b></span>
                                <span class="tcol1"><b>Percent</b></span>
                            </dd>
                            <?php
                            echo $state_list;
                            ?>
                        </dl>
                        <div class="clear"></div>
                    <!--</div>
                    <div class="clear"></div>-->

                </div>
            </div>


            <h2 class="margintopmore">Contacts</h2>
            <div class="nontable_section_inner">
                <div class="pad24">

                    <!--<div class="col66l">
                        <div id="map1c" style="width:100%;height:500px;"></div>
                        <div id="map2c" style="width:100%;height:500px;"></div>
                        <div id="map3c" style="width:100%;height:500px;"></div>
                    </div>
                    <div class="col33">
                        <dl class="">-->
                            <dt>State</dt>
                            <dd>
                                <span class="tcol0"><b>Total</b></span>
                                <span class="tcol1"><b>Percent</b></span>
                            </dd>
                            <?php
                            echo $state_list_contacts;
                            ?>
                        </dl>
                        <div class="clear"></div>
                    <!--</div>
                    <div class="clear"></div>-->

                </div>
            </div>


        </div>
    </div>

</div>

<?php
}
?>