<?php

/**
 * Administrative Control Panel Header.
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
?><!DOCTYPE html>
<html>
<head>
    <title><?php echo COMPANY; ?> | <?php echo $employee['username']; ?> | Zenbership Administration</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="author" content="Castlamp (http://www.castlamp.com/)"/>
    <meta name="generator" content="Zenbership Membership Software"/>
    <!--Start:CSS-->
    <link type="text/css" rel="stylesheet" media="all" href="css/reset.css"/>
    <link type="text/css" rel="stylesheet" media="all" href="css/panel.css"/>

    <link type="text/css" rel="stylesheet" media="all" href="css/menu.css"/>
    <link type="text/css" rel="stylesheet" media="all" href="css/tables.css"/>
    <link type="text/css" rel="stylesheet" media="all" href="css/search.css"/>

    <link type="text/css" rel="stylesheet" media="all" href="css/jquery.cleditor.css"/>
    <link type="text/css" rel="stylesheet" media="all" href="css/jquery_ui/jquery.ui.css"/>
    <link type="text/css" rel="stylesheet" media="all" href="css/jquery.fileuploader.css"/>
    <link type="text/css" rel="stylesheet" media="all" href="css/jquery.imgareaselect.css"/>
    <link type="text/css" rel="stylesheet" media="all" href="css/animate.css"/>

    <!--<link href='https://fonts.googleapis.com/css?family=Lato:400,300,100' rel='stylesheet' type='text/css'>-->
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300' rel='stylesheet' type='text/css'>

    <!--End:CSS-->
    <!--Start:Javascript-->
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/highcharts.min.js"></script>
    <script type="text/javascript" src="../pp-js/jquery.ui.js"></script>
    <script type="text/javascript" src="../pp-js/jquery.timepicker.js"></script>
    <script type="text/javascript" src="js/jquery.mousewheel.js"></script>
    <script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
    <script type="text/javascript" src="js/jquery.autocomplete.js"></script>
    <script type="text/javascript" src="js/shortcuts.js"></script>
    <script type="text/javascript" src="js/jquery.ctrl.js"></script>
    <script type="text/javascript" src="js/forms.js"></script>
    <script type="text/javascript" src="js/jquery.dropdown.js"></script>
    <script type="text/javascript">
        var zen_url = '<?php echo PP_URL; ?>';
        var boxes_checked = 0;
        var window_width = 0;
        var window_height = 0;
        var subtract = 143;
        var cropping = '';
        var active_page = '';
        var active_act = '';
        var active_id = '';
        var active_subpage_id = '';
        var active_faded = '';
        var active_faded_main = '';

        function print() {
            var pathname = window.location.pathname;
            var qs = location.search;
            var url = pathname + qs + '&print=1';
            window.location = url;
            return false;
        }
    </script>
    <script type="text/javascript" src="js/admin.js"></script>
    <!--End:Javascript-->
</head>
<body>

<a name="pagetop"></a>

<div id="topbar">
    <div class="holder small">

        <div class="floatleft" id="top_plain">

            <?php
            // Used for main nav menu and contact top.
            $date = current_date();
            $exp_date = explode(' ', $date);
            $date_exp = explode('-', $exp_date['0']);
            $time = strtotime($date);
            $day3 = date('Y-m-d', $time - 259200);
            $day7 = date('Y-m-d', $time - 604800);
            $day14 = date('Y-m-d', $time - 1209600);
            $day30 = date('Y-m-d', $time - 2592000);
            $day3f = date('Y-m-d', $time + 259200);
            $day7f = date('Y-m-d', $time + 604800);
            $day14f = date('Y-m-d', $time + 1209600);
            $day30f = date('Y-m-d', $time + 2592000);
            // Contacts Awaiting
            $today = explode(' ', current_date());
            $upcoming_contacts = $admin->contacts_by_day($employee['id'], $today['0']);
            $overdue_contacts = $admin->overdue_contacts($employee['id']);
            // $new_contacts = $admin->new_contacts($employee['id']);
            $opportunities = $admin->opportunity_contacts($employee['id']);

            $notes = new notes;
            $deadlines = $notes->deadlines_on_day($exp_date['0'], $employee['id']);
            $appointments = $notes->appointments_on_day($exp_date['0'], $employee['id']);
            ?>


        <span class="head">
            Contacts &raquo;
        </span><span>
            <a href="index.php?l=contacts&filters[]=1||status||eq||ppSD_contacts&filters[]=<?php
            $filter = urlencode('' . $today['0'] . '||next_action||like||ppSD_contacts');
            echo $filter;
            ?>"><?php
                if ($upcoming_contacts <= 0) {
                    echo '<img src="imgs/icon-attention-off.png" width="16" height="16" alt="Alerts!" class="iconLess" />None need attention';
                } else {
                    echo '<img src="imgs/icon-attention-on.png" width="16" height="16" alt="Alerts!" class="iconLess" />' . $upcoming_contacts . " need attention";
                }
                ?></a>
        </span><span>
            <a href="index.php?l=contacts&filters[]=1||status||eq||ppSD_contacts&filters[]=<?php
            $filter = urlencode('' . current_date() . '||next_action||lt||ppSD_contacts');
            echo $filter;
            ?>"><?php
                if ($overdue_contacts <= 0) {
                    echo '<img src="imgs/icon-attention-off.png" width="16" height="16" alt="Alerts!" class="iconLess" />None are overdue';
                } else {
                    echo '<img src="imgs/icon-attention-on.png" width="16" height="16" alt="Alerts!" class="iconLess" />' . $overdue_contacts . " overdue";
                }
                ?></a>
        </span><span>
            <a href="index.php?l=contacts&filters[]=Opportunity||type||eq||ppSD_contacts&filters[]=1||status||eq||ppSD_contacts"><?php
                if ($opportunities <= 0) {
                    echo '<img src="imgs/icon-attention-off.png" width="16" height="16" alt="Alerts!" class="iconLess" />No opportunities.';
                } else {
                    echo '<img src="imgs/icon-attention-on.png" width="16" height="16" alt="Alerts!" class="iconLess" />';
                    if ($opportunities > 1) {
                        echo $opportunities . " opportunities";
                    } else {
                        echo $opportunities . " opportunity";
                    }
                }
                ?></a>
        </span><span>
            <a href="index.php?l=notes&filters[]=<?php echo $exp_date['0']; ?>||deadline||like||ppSD_notes&filters[]=1||complete||neq||ppSD_notes&order=deadline&dir=ASC"><?php
                if ($deadlines <= 0) {
                    echo '<img src="imgs/icon-attention-off.png" width="16" height="16" alt="Alerts!" class="iconLess" />No deadlines';
                } else {
                    echo '<img src="imgs/icon-attention-on.png" width="16" height="16" alt="Alerts!" class="iconLess" />';
                    if ($deadlines > 1) {
                        echo $deadlines . " deadlines";
                    } else {
                        echo $deadlines . " deadline";
                    }
                }
                ?></a>
        </span><span>
            <a href="index.php?l=notes&filters[]=<?php echo $exp_date['0']; ?>||deadline||like||ppSD_notes&filers[]=25||label||eq||ppSD_notes&filters[]=1||complete||neq||ppSD_notes&order=deadline&dir=ASC"><?php
                if ($appointments <= 0) {
                    echo '<img src="imgs/icon-attention-off.png" width="16" height="16" alt="Alerts!" class="iconLess" />No appointments';
                } else {
                    echo '<img src="imgs/icon-attention-on.png" width="16" height="16" alt="Alerts!" class="iconLess" />';
                    if ($appointments > 1) {
                        echo $appointments . " appointments";
                    } else {
                        echo $appointments . " appointment";
                    }
                }
                ?></a>
        </span>
        </div>

        <div class="floatright" id="topRightSection">
            <?php
            if ($employee['permissions']['admin'] == '1') {
            ?>
                <span style="padding-left:12px;">
                    <a href="index.php?l=employees"><img src="imgs/icon-employee.png" class="iconLess" />Employees</a>
                </span><?php
            }
            ?><span style="padding-left:8px;">
                <a href="index.php?l=reports"><img src="imgs/icon-reports.png" class="iconLess" />Reports</a>
            </span><span>
                <a href="null.php" onclick="return user_box();">Welcome <?php echo $employee['first_name']; ?><img
                        src="imgs/down-arrow.png" id="user_arrow" width="10" height="10" alt="Expand" border="0"
                        class="icon-right"/></a>
            </span>
        </div>

        <div class="clear"></div>

    </div>
</div>

<div id="topdark">

<div id="logo">
    <a href="<?php echo PP_ADMIN; ?>"><img src="imgs/logo.png" alt="Zenbership Membership Software" /></a>
</div>

<div id="search">

    <form action="cp-functions/search.php" method="get">
    <a href="null.php" onclick="return popup('build_criteria_type','type=search');"><img src="imgs/icon-lg-criteria.png"
                                                                                         width="20" height="20"
                                                                                         border="0"
                                                                                         alt="Criteria Search"
                                                                                         title="Criteria Search"
                                                                                         class="iconMore"/></a><input
        type="text" id="searchbox" name="query" class="sleep search_input" autocomplete="off" value="Search"
        onkeyup="return quick_search(this.value)"/>
    </form>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#searchbox')
                .blur(function() {
                    $("#searchbox").css('width', '28px');
                })
                .click(function() {
                    $("#searchbox").css('width', '200px');
                });
        });
    </script>

</div>

    <?php
    include 'menu.php';
    ?>

</div>
