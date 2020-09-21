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

$notes = new notes;
$pinned_notes = $notes->get_pinned_notes('', '2');

$home_guide = $db->get_option('welcome_guide');

if (empty($home_guide)) {

    include "welcome_guide.php";

} else {

    ?>

    <div id="topblue" class="fonts small">
        <div class="holder">
            <div class="floatright" id="tb_right">
                <?php
                /*
                $smedia = new socialmedia();
                $twitter = $smedia->confirm_twitter_setup();
                $fb = $smedia->confirm_fb_setup();
                if ($twitter['error'] != '1') {
                    echo "<span><a href=\"index.php?l=social_media_twitter\"><img src='imgs/icon-twitter.png' width=16 height=16 border=0 class='icon' />Twitter Feeds</a></span>";
                }
                if ($fb['error'] != '1') {
                    if ($twitter['error'] != '1') {
                        echo "<span class=\"div\"></span>";
                    }
                    echo "<span><a href=\"index.php?l=social_media_facebook\"><img src='imgs/icon-facebook.png' width=16 height=16 border=0 class='icon' />Facebook Feeds</a></span>";
                }
                */
                ?>
            </div>
            <div class="floatleft" id="tb_left">
                <span><b>Welcome!</b></span>
                <span class="div">|</span>
            <span id="innerLinks">
                <a href="index.php?l=notes">Notes</a>
                <a href="index.php?l=notes&filters[]=4||label||eq||ppSD_notes&filters[]=1||complete||neq||ppSD_notes">To
                    Do List</a>
                <a href="index.php?l=notes&filters[]=1920-01-01 00:01:01||deadline||neq||ppSD_notes&filters[]=1||complete||neq||ppSD_notes&order=deadline&dir=ASC">Deadlines</a>
                <a href="index.php?l=notes&filters[]=25||label||eq||ppSD_notes&filters[]=1||complete||neq||ppSD_notes&order=deadline&dir=ASC">Appointments</a>
                <a href="index.php?l=calendar">Calendar</a>
                <span class="div">|</span>
                <a href="index.php?l=feed">Activity Feed</a>
            </span>
            </div>
            <div class="clear"></div>
        </div>
    </div>


    <div id="mainsection" style="background-color: #f9f9f9;">

        <div class="col40l" style="min-height: 100vh;border-right: 1px solid #e1e1e1;background-color: #fff;">
            <h1 class="homeh1">
                <a href="index.php?l=feed">Activity Feed</a>
            </h1>

                    <?php
                    if ($employee['permissions']['admin'] == '1') {
                    ?>

                        <div style="padding:16px;">
                            <form action="index.php" id="graph_form" method="get">

                    <?php

                    // Monthly Graph
                    $gdata = $admin->get_graph_array($_GET);

                    // Graph 1
                    $graph = array(
                        array(
                            'title' => 'Contacts',
                            'key'   => 'contacts',
                        ),
                        array(
                            'title' => 'Members',
                            'key'   => 'members',
                        ),
                        array(
                            'title' => 'Logins',
                            'key'   => 'logins',
                        ),
                        array(
                            'title' => 'Revenue',
                            'key'   => 'revenue',
                        ),
                    );

                    $options = array(
                        'title'      => 'Overview',
                        'element'    => 'graph',
                        'increments' => $gdata['int'],
                        'type'       => $gdata['unit'],
                        'yaxis'      => array(
                            array(
                                'title'      => '',
                                'line_width' => '3',
                                'type'       => 'line',
                            ),
                            array(
                                'title'      => '',
                                'line_width' => '3',
                                'type'       => 'line',
                            ),
                            array(
                                'title'      => '',
                                'line_width' => '3',
                                'type'       => 'line',
                            ),
                            array(
                                'title'      => '',
                                'line_width' => '3',
                                'type'       => 'line',
                            ),
                        ),
                    );

                    $graph_outA = new graph($graph, $options);

                    echo $graph_outA;

                    echo $admin->graph_form($gdata);

                    ?>
                </form>
            </div>
            <div id="graph" style="border-bottom:1px solid #e1e1e1;height:200px;"></div>

            <?php
            }
            ?>


            <div id="feed">
                <ul class="history_list" style="overflow-y:auto;">
                    <?php
                    $history = new history('', '', '', '', '', '', '');
                    $q12 = $db->run_query("
                        SELECT *
                        FROM `ppSD_history`
                        ORDER BY `date` DESC
                        LIMIT 30
                    ");
                    while ($item = $q12->fetch()) {
                        echo $history->format_condensed($item);
                    }
                    ?>
                </ul>
            </div>
        </div>


        <div class="col20c noBorder" style="border-right:1px solid #e1e1e1 !important;background-color:#fff;">
            <h1 class="homeh1">
                <a href="index.php?l=contacts&filters[]=<?php echo date('Y-m-d', time() - 259200); ?>||created||gt||ppSD_contacts&order=created&dir=asc">Recent Contacts (Action Required)</a>
            </h1>

            <?php
            $contact = new contact;
            $recent = $contact->getRecent();

            if (sizeof($recent) <= 0) {
                echo "<p style='padding:12px;'><i>No recent contacts to display.</i></p>";
            } else {
                $up = 0;
                foreach ($recent as $item) {
                    $up++;
                    $class = ($up % 2 == 0) ? 'odd' : 'even';
                    echo $contact->contactCard($item, $class);
                }
            }
            ?>
        </div>


        <div class="col20c noBorder" style="margin-left:-1px;border-left:1px solid #e1e1e1 !important;background-color:#fff;">
            <h1 class="homeh1">
                <a href="index.php?l=members&filters[]=<?php echo date('Y-m-d', time() - 259200); ?>||joined||gt||ppSD_members&order=joined&dir=asc">Recent Members</a>
            </h1>

            <?php
            $user = new user;
            $recent = $user->getRecent();

            if (sizeof($recent) <= 0) {
                echo "<p style='padding:12px;'><i>No recent members to display.</i></p>";
            } else {
                $up = 0;
                foreach ($recent as $item) {
                    $up++;
                    $class = ($up % 2 == 0) ? 'even' : 'odd';
                    echo $user->memberCard($item, $class);
                }
            }
            ?>
        </div>

        <div class="col20r" style="border-left: 1px solid #e1e1e1;margin-left:-1px;">
            <div class="">

                <?php

                if (! empty($pinned_notes)) {

                    foreach ($pinned_notes as $item) {
                        echo $admin->format_note($item, 0, 0, 1);
                    }

                }

                ?>


                <?php
                echo $admin->employee_notes(1, 10);
                ?>
            </div>
        </div>
        <div class="clear"></div>

    </div>

    <style>
        #quickLinks {
            text-align: center;
            border-bottom: 1px solid #ccc;
            -webkit-box-shadow: inset 0px 2px 4px 0px rgba(255, 255, 255, 0.75);
            box-shadow: inset 0px 2px 4px 0px rgba(255, 255, 255, 0.75);
        }

        #quickLinks span {
            width: 25%;
            display: inline-block;
            border-right: 1px solid #ccc;
            border-left: 1px solid #f9f9f9;
            box-sizing: border-box;
        }

        #quickLinks span:hover {
            background-color: #f9f9f9 !important;
        }

        #quickLinks a {
            display: block;
            width: 100%;
            height: 32px;
        }
    </style>

<?php
}
