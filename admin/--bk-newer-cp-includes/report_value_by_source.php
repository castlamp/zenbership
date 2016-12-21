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


$permission = 'cart-value_by_source';
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
    $start = $exp_date['0'] . '-' . $exp_date['1'] . '-01';
}
if (! empty($_GET['end_date'])) {
    $end = $_GET['end_date'];
} else {
    $end = $exp_date['0'] . '-' . $exp_date['1'] . '-31';
}

if ($start > $end) {
    $temp = $start;
    $start = $end;
    $end = $temp;
}

$where_add = " AND (ppSD_cart_sessions.date_completed>='" . $db->mysql_cleans($start) . "' AND ppSD_cart_sessions.date_completed<='" . $db->mysql_cleans($end) . "')";

/**
 * Value by source, for contacts
 */
$q1 = $db->run_query("
    SELECT
        ppSD_contacts.id AS contact_id,
        ppSD_sources.id AS source_id,
        ppSD_sources.source AS source,
        SUM(ppSD_cart_session_totals.total) AS total
    FROM `ppSD_sources`
    JOIN `ppSD_contacts`
    ON ppSD_contacts.source=ppSD_sources.id
    JOIN `ppSD_cart_sessions`
    ON ppSD_cart_sessions.member_id=contact_id
    JOIN `ppSD_cart_session_totals`
    ON ppSD_cart_sessions.id=ppSD_cart_session_totals.id
    WHERE
      ppSD_cart_sessions.status='1' $where_add
  ORDER BY `total` ASC
");

/**
 * Value by source, for members
 */
$q2 = $db->run_query("
    SELECT
        ppSD_members.id AS member_id,
        ppSD_sources.id AS source_id,
        ppSD_sources.source AS source,
        SUM(ppSD_cart_session_totals.total) AS total
    FROM `ppSD_sources`
    JOIN `ppSD_members`
    ON ppSD_members.source=ppSD_sources.id
    JOIN `ppSD_cart_sessions`
    ON ppSD_cart_sessions.member_id=ppSD_members.id
    JOIN `ppSD_cart_session_totals`
    ON ppSD_cart_sessions.id=ppSD_cart_session_totals.id
    WHERE
      ppSD_cart_sessions.status='1' $where_add
  ORDER BY `total` ASC
");

/**
 * Value by account, for members
 */
$q3 = $db->run_query("
    SELECT
        ppSD_members.id AS member_id,
        ppSD_accounts.name AS account_name,
        SUM(ppSD_cart_session_totals.total) AS total
    FROM `ppSD_accounts`
    JOIN `ppSD_members`
    ON ppSD_members.account=ppSD_accounts.id
    JOIN `ppSD_cart_sessions`
    ON ppSD_cart_sessions.member_id=ppSD_members.id
    JOIN `ppSD_cart_session_totals`
    ON ppSD_cart_sessions.id=ppSD_cart_session_totals.id
    WHERE
      ppSD_cart_sessions.status='1' $where_add
  ORDER BY `total` ASC
");

/**
 * Value by account, for contacts
 */
$q4 = $db->run_query("
    SELECT
        ppSD_contacts.id AS contact_id,
        ppSD_accounts.name AS account_name,
        SUM(ppSD_cart_session_totals.total) AS total
    FROM `ppSD_accounts`
    JOIN `ppSD_contacts`
    ON ppSD_contacts.account=ppSD_accounts.id
    JOIN `ppSD_cart_sessions`
    ON ppSD_cart_sessions.member_id=ppSD_members.id
    JOIN `ppSD_cart_session_totals`
    ON ppSD_cart_sessions.id=ppSD_cart_session_totals.id
    WHERE
      ppSD_cart_sessions.status='1' $where_add
  ORDER BY `total` ASC
");

/**
 * Value By Member Type
 */
$q5 = $db->run_query("
    SELECT
        ppSD_members.id AS member_id,
        ppSD_member_types.name AS type_name,
        SUM(ppSD_cart_session_totals.total) AS total
    FROM `ppSD_member_types`
    JOIN `ppSD_members`
    ON ppSD_members.member_type=ppSD_member_types.id
    JOIN `ppSD_cart_sessions`
    ON ppSD_cart_sessions.member_id=ppSD_members.id
    JOIN `ppSD_cart_session_totals`
    ON ppSD_cart_sessions.id=ppSD_cart_session_totals.id
    WHERE
      ppSD_cart_sessions.status='1' $where_add
  ORDER BY `total` ASC
");

?>

<form action="index.php" method="get">
    <input type="hidden" name="l" value="<?php echo $_GET['l']; ?>" />
    <div id="topblue" class="fonts small"><div class="holder">
        <div class="floatright" id="tb_right">
            <?php
            $datepickstart = $admin->datepicker('start_date', $start, '0', '100');
            $datepickend = $admin->datepicker('end_date', $end, '0', '100');
            echo $datepickstart; ?> to <?php echo $datepickend; ?> <input type="submit" value="Go" class="blue " />
        </div>
        <div class="floatleft" id="tb_left">
            <b>Report: Value by Source</b>
        </div>
        <div class="clear"></div>
    </div></div>
</form>

<div id="mainsection">

<div class="nontable_section" style="margin-bottom: -42px;">
    <div class="pad24notop">
        <h1>Value by Source: <?php echo $start; ?> to <?php echo $end; ?></h1>
    </div>
</div>

<div class="col50l">

    <div class="nontable_section">
        <div class="pad24">

            <h1 class="">Members</h1>

            <h2 class="">Value by Source</h2>
            <div class="nontable_section_inner">
                <div class="pad24">

                    <dl>
                        <dt>Source</dt>
                        <dd><b>Value</b></dd>
                        <?php
                        while ($source_row = $q2->fetch()) {
                            echo "<dt>" . $source_row['source'] . "</dt>";
                            echo "<dd>" . place_currency($source_row['total']) . "</dd>";
                        }
                        ?>
                    </dl>
                    <div class="clear"></div>

                </div>
            </div>

            <h2 class="margintopmore">Value by Account</h2>
            <div class="nontable_section_inner">
                <div class="pad24">

                    <dl>
                        <dt>Account</dt>
                        <dd><b>Value</b></dd>
                        <?php
                        while ($account_row = $q3->fetch()) {
                            echo "<dt>" . $source_row['account_name'] . "</dt>";
                            echo "<dd>" . place_currency($source_row['total']) . "</dd>";
                        }
                        ?>
                    </dl>
                    <div class="clear"></div>

                </div>
            </div>

            <h2 class="margintopmore">Value by Member Type</h2>
            <div class="nontable_section_inner">
                <div class="pad24">

                    <dl>
                        <dt>Type</dt>
                        <dd><b>Value</b></dd>
                        <?php
                        while ($source_row = $q5->fetch()) {
                            echo "<dt>" . $source_row['type_name'] . "</dt>";
                            echo "<dd>" . place_currency($source_row['total']) . "</dd>";
                        }
                        ?>
                    </dl>
                    <div class="clear"></div>

                </div>
            </div>


        </div>
    </div>

</div>
<div class="col50r">

    <div class="nontable_section">
        <div class="pad24">

            <h1 class="">Contacts</h1>

            <h2 class="">Value by Source</h2>
            <div class="nontable_section_inner">
                <div class="pad24">

                    <dl>
                        <dt>Source</dt>
                        <dd><b>Value</b></dd>
                        <?php
                        while ($source_row = $q1->fetch()) {
                            echo "<dt>" . $source_row['source'] . "</dt>";
                            echo "<dd>" . place_currency($source_row['total']) . "</dd>";
                        }
                        ?>
                    </dl>
                    <div class="clear"></div>

                </div>
            </div>

            <h2 class="margintopmore">Value by Account</h2>
            <div class="nontable_section_inner">
                <div class="pad24">

                    <dl>
                        <dt>Account</dt>
                        <dd><b>Value</b></dd>
                        <?php
                        while ($account_row = $q4->fetch()) {
                            echo "<dt>" . $source_row['account_name'] . "</dt>";
                            echo "<dd>" . place_currency($source_row['total']) . "</dd>";
                        }
                        ?>
                    </dl>
                    <div class="clear"></div>

                </div>
            </div>

        </div>
    </div>

</div>
<div class="clear"></div>

</div>

<?php
}
?>