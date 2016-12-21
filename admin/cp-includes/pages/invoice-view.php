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
// Check permissions, ownership,
// and if it exists.
$permission = 'invoice-view';
$check = $admin->check_permissions($permission, $employee);
if ($check != '1') {
    $admin->show_no_permissions($error, '', '1');
} else {
    // Ownership
    $invoice = new invoice;
    $data    = $invoice->get_invoice($_POST['id']);


    $notes = new notes;
    $pinned_notes = $notes->get_pinned_notes($_POST['id']);
    ?>

    <div class="col50l">
        <div class="pad24_fs_l">

            <fieldset>
                <legend>Invoice Overview</legend>
                <div class="pad24">

                    <dl>
                        <dt>Status</dt>
                        <dd><?php echo $data['data']['format_status']; ?></dd>
                        <dt>Created</dt>
                        <dd><?php echo $data['data']['format_date']; ?></dd>
                        <dt>Due</dt>
                        <dd><?php echo $data['data']['format_due_date'] . ' (' . $data['data']['time_to_due_date'] . ')'; ?></dd>
                        <dt>Last Reminder</dt>
                        <dd><?php echo $data['data']['format_last_reminder']; ?></dd>
                        <dt>Owner</dt>
                        <dd><?php
                            if ($data['data']['member_type'] == 'member') {
                                echo "<a href=\"return_null.php\" onclick=\"return load_page('member','view','" . $data['data']['member_id'] . "');\">Member ID " . $data['data']['member_id'] . "</a>";
                            } else {
                                echo "<a href=\"return_null.php\" onclick=\"return load_page('contact','view','" . $data['data']['member_id'] . "');\">Contact ID " . $data['data']['member_id'] . "</a>";
                            }
                            ?></dd>
                    </dl>
                    <div class="clear"></div>

                </div>
            </fieldset>

            <fieldset>
                <legend>Invoiced Party</legend>
                <div class="pad24">

                    <dl>
                        <dt>Contact Name</dt>
                        <dd><?php echo $data['billing']['contact_name']; ?></dd>
                        <dt>Company Name</dt>
                        <dd><?php echo $data['billing']['company_name']; ?></dd>
                        <dt>Address</dt>
                        <dd><?php echo $data['billing']['formatted']; ?></dd>
                        <dt>Website</dt>
                        <dd><?php echo $data['billing']['website']; ?></dd>
                        <dt>E-Mail</dt>
                        <dd><?php echo $data['billing']['email']; ?></dd>
                    </dl>
                    <div class="clear"></div>

                </div>
            </fieldset>

            <fieldset>
                <legend>Memo</legend>
                <div class="pad24">
                    <p><?php echo $data['billing']['memo']; ?></p>

                    <div class="clear"></div>
                </div>
            </fieldset>

        </div>
    </div>
    <div class="col50r">
        <div class="pad24_fs_r">

            <?php

            if (!empty($pinned_notes)) {

                echo '<div style="margin-bottom:24px;">';

                foreach ($pinned_notes as $item) {
                    echo $admin->format_note($item);
                }

                echo '</div>';

            }

            ?>

            <fieldset>
                <legend>Totals</legend>
                <div class="pad24">

                    <dl>
                        <dt>Subtotal</dt>
                        <dd><?php echo $data['format_totals']['format_subtotal']; ?></dd>
                        <dt>Shipping</dt>
                        <dd>+ <?php echo $data['format_totals']['format_shipping']; ?></dd>
                        <dt>Tax</dt>
                        <dd>
                            + <?php echo $data['format_totals']['format_tax'] . ' (' . $data['format_totals']['format_tax_rate'] . ')'; ?></dd>
                        <dt>Credits</dt>
                        <dd>- <?php echo $data['format_totals']['format_credits']; ?></dd>
                        <dt>Total Paid</dt>
                        <dd>- <?php echo $data['format_totals']['format_paid']; ?></dd>
                        <dt>Balance Due</dt>
                        <dd><b><?php echo $data['format_totals']['format_due']; ?></b></dd>
                    </dl>
                    <div class="clear"></div>

                </div>
            </fieldset>

            <?php
            if (!empty($data['data']['shipping_rule'])) {
                ?>

                <fieldset>
                    <legend>Shipping Information</legend>
                    <div class="pad24">

                        <dl>
                            <dt>Method</dt>
                            <dd><?php echo $data['data']['shipping_name']; ?></dd>
                            <dt>Address</dt>
                            <dd><?php echo $data['shipping']['formatted']; ?></dd>
                        </dl>

                        <div class="clear"></div>

                    </div>
                </fieldset>

                <fieldset>
                    <legend>Shipping Map</legend>
                    <div class="pad24">

                        <?php
                        echo generate_map($data['shipping'], '100%', '275');
                        ?>

                    </div>
                </fieldset>

                <?php

            }
            ?>

        </div>
    </div>
    <div class="clear"></div>

    <?php
    }