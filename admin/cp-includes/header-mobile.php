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
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns="http://www.w3.org/1999/html" lang="en" xml:lang="en">
<head>
    <title><?php echo COMPANY; ?> | <?php echo $employee['username']; ?> | Zenbership Administration</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="author" content="Castlamp (http://www.castlamp.com/)"/>
    <meta name="generator" content="Zenbership Membership Software"/>
    <!--Start:CSS-->
    <link type="text/css" rel="stylesheet" media="all" href="css/reset.css"/>
    <link type="text/css" rel="stylesheet" media="all" href="css/panel.css"/>

    <link type="text/css" rel="stylesheet" media="all" href="css/menu.mobile.css"/>
    <link type="text/css" rel="stylesheet" media="all" href="css/tables.mobile.css"/>
    <link type="text/css" rel="stylesheet" media="all" href="css/search.mobile.css"/>

    <!--<link type="text/css" rel="stylesheet" media="handheld, only screen and (max-device-width: 720px)" href="css/mobile.css" />-->
    <link type="text/css" rel="stylesheet" href="css/mobile.css" />
    <meta name="viewport" content="width=device-width, initial-scale=0.75" />
    <link type="text/css" rel="stylesheet" media="all" href="css/jquery.jscrollpane.css"/>
    <link type="text/css" rel="stylesheet" media="all" href="css/jquery.cleditor.css"/>
    <link type="text/css" rel="stylesheet" media="all" href="css/jquery_ui/jquery.ui.css"/>
    <link type="text/css" rel="stylesheet" media="all" href="css/jquery.fileuploader.css"/>
    <link type="text/css" rel="stylesheet" media="all" href="css/jquery.imgareaselect.css"/>
    <!--End:CSS-->
    <!--Start:Javascript-->
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="../pp-js/jquery.ui.js"></script>
    <script type="text/javascript" src="../pp-js/jquery.timepicker.js"></script>
    <script type="text/javascript" src="js/jquery.mousewheel.js"></script>
    <!--<script type="text/javascript" src="js/jquery.jscrollpane.min.js"></script>-->
    <script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
    <script type="text/javascript" src="js/jquery.autocomplete.js"></script>
    <script type="text/javascript" src="js/shortcuts.js"></script>
    <script type="text/javascript" src="js/jquery.ctrl.js"></script>
    <script type="text/javascript" src="js/forms.js"></script>
    <script type="text/javascript" src="js/jquery.dropdown.mobile.js"></script>
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

        function mobile_menu() {
            $('#nav').toggle();
            return false;
        }

        function mobile_search() {
            $('#search').toggle();
            return false;
        }


    </script>
    <script type="text/javascript" src="js/admin.js"></script>
    <!--End:Javascript-->

</head>
<body>

<a name="pagetop"></a>

<div id="topdark">
    <div id="logo" onclick="window.location='<?php echo PP_ADMIN; ?>';"></div>
    <div id="topright">
        <a href="null.php" onclick="return mobile_menu();"><img src="imgs/icon-menu-mobile.png" /></a>
        <a href="null.php" onclick="return mobile_search();"><img src="imgs/icon-search-mobile.png" /></a>
        <a href="logout.php"><img src="imgs/icon-logout-mobile.png" /></a>
    </div>
    <div class="clear"></div>
</div>

<div id="search">
    <a href="null.php" onclick="return popup('build_criteria_type','type=search');"><img src="imgs/icon-lg-criteria.png"
                                                                                         width="16" height="16"
                                                                                         border="0"
                                                                                         alt="Criteria Search"
                                                                                         title="Criteria Search"
                                                                                         class="icon"/></a><input
        type="text" name="query" class="sleep search_input" value="Search" style="width:200px;"
        onkeyup="return quick_search(this.value)"/>
</div>

<?php
include 'menu.php';
?>
