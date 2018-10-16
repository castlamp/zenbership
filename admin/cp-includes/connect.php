<?php


/**
 * Connect options, such as email and SMS.
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

$permission = 'connect';
$check = $admin->check_permissions($permission, $employee);
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

                <b>Connect Options</b>

            </div>

            <div class="clear"></div>

        </div>
    </div>



    <div id="mainsection">


        <div class="nontable_section">
            <div class="pad24">

                <h1>Connect Through E-Mail</h1>

                <div class="nontable_section_inner">

                    <div class="pad24 line_bot">

                        <div class="col33">

                            <h2><img src="imgs/icon-email_campaign.png" width="32" height="32" alt="E-Mail Campaign"
                                     title="E-Mail Campaign" class="iconlg"/><a href="index.php?l=email_campaigns">E-Mail
                                    Campaigns</a></h2>

                            <p>Opt-in or autoresponder e-mail campaigns.</p>

                        </div>

                        <div class="col33">

                            <h2><img src="imgs/icon-email_target.png" width="32" height="32" alt="Targeted E-Mail"
                                     title="Targeted E-Mail" class="iconlg"/><a href="returnnull.php"
                                                                                onclick="return popup('build_criteria_type','','1');">Targeted
                                    E-Mail Blast</a></h2>

                            <p>Send a mass e-mail based on specific criteria.</p>

                        </div>

                        <div class="col33">

                            <h2><img src="imgs/icon-email_outbox.png" width="32" height="32" alt="Dispatch Logs"
                                     title="Dispatch Logs" class="iconlg"/><a href="index.php?l=email_outbox">Outbox</a>
                            </h2>

                            <p>View e-mails saved to the outbox.</p>

                        </div>

                        <div class="clear"></div>

                    </div>

                    <div class="pad24 line_top">

                        <div class="col33">

                            <h2><img src="imgs/icon-tracking.png" width="32" height="32" alt="Stats and Tracking"
                                     title="Stats and Tracking" class="iconlg"/><a href="index.php?l=email_tracking">Tracking
                                    Logs</a></h2>

                            <p>View who has opened outgoing emails.</p>

                        </div>

                        <div class="col33">

                            <h2><img src="imgs/icon-lg-queue.png" width="32" height="32" alt="Outgoing Queue"
                                     title="Outgoing Queue" class="iconlg"/><a href="index.php?l=email_queue">Outgoing
                                    Queue</a></h2>

                            <p>View the outgoing queue.</p>

                        </div>

                        <div class="col33">

                        </div>

                        <div class="clear"></div>

                    </div>

                </div>

            </div>
        </div>



        <div class="nontable_section">
            <div class="pad24">

                <h1>Connect Through SMS</h1>

                <div class="nontable_section_inner">

                    <div class="pad24 line_bot">

                        <!--
                        <div class="col33">

                            <h2><img src="imgs/icon-sms_campaign.png" width="32" height="32" alt="SMS Campaign"
                                     title="SMS Campaign" class="iconlg"/><a href="index.php?l=sms_campaigns">SMS
                                    Campaigns</a></h2>

                            <p>Automated e-mail campaigns.</p>

                        </div>
                        -->

                        <div class="col33">

                            <h2><img src="imgs/icon-sms_target.png" width="32" height="32" alt="Targeted SMS"
                                     title="Targeted SMS" class="iconlg"/><a href="returnnull.php" onclick="return popup('build_criteria_type','type=sms','0');">Targeted
                                    SMS Blast</a></h2>


                            <p>Send a mass e-mail based on specific criteria.</p>

                        </div>

                        <!--
                        <div class="col33">

                            <h2><img src="imgs/icon-sms_outbox.png" width="32" height="32" alt="Dispatch Logs"
                                     title="Dispatch Logs" class="iconlg"/><a href="index.php?l=sms_outbox">SMS
                                    Outbox</a></h2>

                            <p>View SMS messages saved to the outbox.</p>

                        </div>
                        -->

                        <div class="clear"></div>

                    </div>

                </div>

            </div>
        </div>


        <div class="nontable_section">
            <div class="pad24">

                <h1>Connect through Social Media</h1>

                <div class="nontable_section_inner">

                    <div class="pad24 line_bot">

                        <div class="col33">

                            <h2><img src="imgs/icon-lg-facebook.png" width="32" height="32" alt="Facebook"
                                     title="Facebook" class="iconlg"/><a href="returnnull.php"
                                                                         onclick="return popup('facebook','','1');">Facebook</a>
                            </h2>

                            <p>Manage your Facebook account.</p>

                        </div>

                        <div class="col33">

                            <h2><img src="imgs/icon-lg-twitter.png" width="32" height="32" alt="Twitter" title="Twitter"
                                     class="iconlg"/><a href="returnnull.php" onclick="return popup('twitter','','1');">Twitter</a>
                            </h2>

                            <p>Manage your Twitter account.</p>

                        </div>

                        <div class="col33">

                        </div>

                        <div class="clear"></div>

                    </div>

                </div>

            </div>
        </div>


    </div>



<?php

}

?>
