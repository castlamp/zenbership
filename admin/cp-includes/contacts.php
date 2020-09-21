<?php


/**
 * List of contacts.
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

$permission = 'contact';
$check = $admin->check_permissions($permission, $employee);
if ($check != '1') {
    $admin->show_no_permissions();
} else {
    $filter_array_default = array(
        '1||status||eq||ppSD_contacts'
    );
    $table                = 'ppSD_contacts';

    if (!empty($_GET['criteria_id'])) {
        $criteria_id = $_GET['criteria_id'];
    } else {
        $criteria_id = '';
    }

    if (! empty($criteria_id)) {
        $crit = new criteria($criteria_id);
        if (! empty($crit->data['sort'])) {
            $useSort = 'ppSD_contact_data.' .  $crit->data['sort'];
        }
        if (! empty($crit->data['sort_order'])) {
            $useOrder = $crit->data['sort_order'];
        }
        if (! empty($crit->data['display_per_page'])) {
            $useDisplay = $crit->data['display_per_page'];
        }
    }

    if (! empty($_GET['order'])) {
        if ($_GET['order'] == 'created') {
            $order                = 'ppSD_contacts';
        } else {
            $order                = 'ppSD_contact_data';
        }
        $order                = '.' . htmlentities($_GET['order']);
    } else {
        $order                = 'ppSD_contact_data.last_name';
    }
    $dir                  = (! empty($_GET['dir'])) ? $_GET['dir'] : 'ASC';
    $display              = '50';
    $page                 = '1';
    $defaults             = array(
        'sort'    => (! empty($useSort)) ? $useSort : $order,
        'order'   => (! empty($useOrder)) ? $useOrder : $dir,
        'page'    => $page,
        'display' => (! empty($useDisplay)) ? $useDisplay : $display,
        'filters' => $filter_array_default,
    );
    $force_filters        = array();
    if ($employee['permissions']['admin'] != '1') {
        $force_filters[] = $employee['id'] . '||owner||eq||ppSD_contacts';
    }

    $gen_table = $admin->get_table($table, $_GET, $defaults, $force_filters, $criteria_id);



    ?>

    <form action="cp-includes/get_table.php" id="table_filters" method="post" onsubmit="return update_table();">
    <input type="hidden" name="order" value="<?php echo $gen_table['order']; ?>"/>
    <input type="hidden" name="dir" value="<?php echo $gen_table['dir']; ?>"/>
    <input type="hidden" name="menu" value="<?php echo $gen_table['menu']; ?>"/>
        <input type="hidden" name="criteria_id" value="<?php echo $criteria_id; ?>"/>
    <input type="hidden" name="table" value="<?php echo $table; ?>"/>
    <input type="hidden" name="permission" value="<?php echo $permission; ?>"/>
    <input type="hidden" name="filters" id="filter_field" value='<?php if (!empty($_GET['filters'])) {
        echo serialize($_GET['filters']);
    } else {
        $combine = array_merge($filter_array_default, $force_filters);
        if (!empty($combine)) {
            echo serialize($combine);
        }
    } ?>'/>


    <div id="topblue" class="fonts small">
        <div class="holder">
            <div class="floatright" id="tb_right">

                <?php
                include dirname(__FILE__) . '/pagination_display.php';
                ?>

            </div>

            <div class="floatleft" id="tb_left">
                <span><b>Listing Contacts</b></span>
                <span class="div">|</span>
                <a href="null.php" onclick="return show_filters();">Filters<img src="imgs/down-arrow.png"
                                                                                id="filter_arrow" width="10" height="10"
                                                                                alt="Expand" border="0"
                                                                                class="icon-right"/></a>
                <span class="div">|</span>
                <span id="innerLinks">
                    <a href="null.php" onclick="return load_page('contact','add');">Create Contact</a>
                    <a href="null.php" onclick="return popup('sources','');">Sources</a>
                    <span class="div">|</span>
                    <a href="null.php" onclick="return prep_export('contact');">Export</a>
                </span>
                <?php
                if (! empty($_GET['criteria_id'])) {
                    ?>
                    <span class="div">|</span>
                    <a href="null.php" onclick="return popup('preview_criteria','id=<?php echo $_GET['criteria_id']; ?>&type=criteria','','0');">View Criteria</a>
                <?php
                }
                ?>

            </div>
            <div class="clear"></div>
        </div>
    </div>


    <div id="filters" class="fonts smaller">
    <div class="pad24">

    <div id="filters_top">

        <div id="filters_right">

            <input type="submit" value="Apply Filters" class="save"/>

        </div>

        <div id="filters_left">

            <span><b>Applying Filters</b></span>

            <!--<span><a href="null.php" onclick="return popup('filters-<?php echo $permission; ?>');"><img src="imgs/icon-settings.png" width="16" height="16" border="0" alt="Settings" title="Settings" class="icon" />Settings</a></span>-->

	   			<span><?php

                    echo $admin->alpha_list('contacts', 'last_name', 'ppSD_contact_data');

                    ?></span>

        </div>

        <div class="clear"></div>

    </div>

    <div class="col50">

        <?php

        $opnm = $permission . '_filters';

        $opt_filters = $db->get_option($opnm);
        $add_filters = array();

        if (!empty($employee['options'][$opnm])) {
            $add_filters = explode(',', $employee['options'][$opnm]);

        }

        if (!empty($opt_filters)) {
            $add_filters = array_merge($add_filters, explode(',', $opt_filters));
        }

        // name:table:date:date_range
        $thefilters = array(
            'email:ppSD_contacts::',
            'last_name:::',
            'company_name:::',
            'city:::',
            'state:::',
            'created:ppSD_contacts:1:1',
            'expected_value:ppSD_contacts::'
        );
        $thefilters = array_merge($thefilters, $add_filters);

        foreach ($thefilters as $aFilter) {
            $exp = explode(':', $aFilter);
            if (empty($exp['1'])) {
                $exp['1'] = 'ppSD_contact_data';
            }

            ?>

            <div class="field">

                <label><?php echo format_db_name($exp['0']); ?></label>

                <div class="field_entry">

                    <?php

                    if ($exp['2'] == '1') {
                        $date = '1';
                    } else {
                        $date = '0';
                    }

                    if ($exp['3'] == '1') {
                        $dater = '1';
                    } else {
                        $dater = '0';
                    }

                    echo $admin->filter_field($exp['0'], '', $exp['1'], '1', $date, $dater);

                    if ($dater == '1') {
                        ?>

                        <p class="field_desc_show">Create a date range by inputting two dates, or select a specific date
                            by only inputting the first field. All dates need to be in the "YYYY-MM-DD" format.</p>

                    <?php

                    }

                    ?>

                </div>

            </div>

        <?php

        }

        ?>

    </div>

    <div class="col50">

        <div class="field">

            <label>Type</label>

            <div class="field_entry">

                <select name="filter[type]" style="width:200px;">

                    <option></option>

                    <option>Contact</option>

                    <option>Lead</option>

                    <option>Opportunity</option>

                    <option>Customer</option>

                </select>

            </div>

        </div>

        <div class="field">

            <label>Source</label>

            <div class="field_entry">

                <input type="text" id="source" value="" name="source_name"
                       onkeyup="return autocom('source','id','source','ppSD_sources','source','');" style="width:110px;"
                       class="filterinputtype"/>

                <input type="hidden" name="filter[source]" id="source_id" value=""/>

                <p class="field_desc_show">Begin typing a source to filter by items originiating from that location.</p>

            </div>

        </div>

        <div class="field">

            <label>Account</label>

            <div class="field_entry">

                <input type="text" value="" name="account_name" id="account"
                       onkeyup="return autocom('account','id','name','ppSD_accounts','name','accounts');"
                       style="width:110px;" class="filterinputtype"/>

                <input type="hidden" name="filter[account]" id="account_id" value=""/>

                <p class="field_desc_show">Begin typing an account to filter by items assigned only to specific
                    account.</p>

            </div>

        </div>

        <?php

        if ($employee['permissions']['admin'] == '1') {
            ?>

            <div class="field">
                <label>Assigned To</label>
                <div class="field_entry">
                    <select name="filter[owner]" id="owner" style="width:200px;">
                    <option value=""></option>
                    <?php
                    $list = $admin->get_employees('select');
                    echo $list;
                    ?>
                    </select>
                    <!--
                    <input type="text" id="owner"
                           onkeyup="return autocom('owner','id','username','ppSD_staff','username,firstname,lastname','staff');"
                           value="" style="width:110px;" class="filterinputtype"/>
                    <input type="hidden" name="filter[owner]" id="owner_id" value=""/>
                    -->
                    <p class="field_desc_show">Filter by items assigned only to a specific employee.</p>
                </div>
            </div>

            <div class="field">
                <label>Unassigned</label>
                <div class="field_entry">
                    <input type="checkbox" name="filter[owner]" value="2"/> Unassigned<br/>
                    <input type="hidden" name="filter_type[owner]" value="eq"/>
                </div>
            </div>

        <?php

        }

        ?>

        <div class="field">

            <label>Status</label>

            <div class="field_entry">

                <input type="radio" name="filter[status]" value=""/> --<br/>

                <input type="radio" name="filter[status]" value="1"/> Active<br/>

                <input type="radio" name="filter[status]" value="2"/> Converted<br/>

                <input type="radio" name="filter[status]" value="3"/> Dead

            </div>

        </div>

        <div class="field">

            <label>Bounced E-Mail</label>

            <div class="field_entry">

                <input type="checkbox" name="filter[bounce_notice]" value="1920-01-01 00:01:01"/> E-Mail Has Bounced

                <input type="hidden" name="filter_type[bounce_notice]" value="neq"/>

                <input type="hidden" name="filter_tables[bounce_notice]" value="ppSD_contacts"/>

            </div>

        </div>

        <div class="field">

            <label>Overdue</label>

            <div class="field_entry">

                <input type="checkbox" name="filter[next_action]" value="<?php echo current_date(); ?>"/> Overdue<br/>

                <input type="hidden" name="filter_type[next_action]" value="lt"/>

            </div>

        </div>

    </div>

    <div class="clear"></div>

    </div>
    </div>

    </form>



    <div id="mainsection">

        <form id="table_checkboxes">

            <table class="tablesorter listings" id="active_table" border="0">

                <?php

                echo $gen_table['th'];

                echo $gen_table['td'];

                ?>

            </table>


            <div id="bottom_delete">
                <div class="pad16">

                    <div style="float:right;">
                        <input type="button"
                               value="Delete"
                               class="del"
                               onclick="return compile_delete('<?php echo $table; ?>','table_checkboxes');"/>
                    </div>

                    <div id="sum_list">
                        <span class="sl_key">Expected Value</span>
                        <span class="sl_value" id="math1"><?php echo $gen_table['math']; ?></span>
                    </div>

                    <span class="small gray caps bold"
                                         style="margin-right:24px;">With Selected:</span><input type="button"
                                                                                                value="E-Mail"
                                                                                                class=""
                                                                                                onclick="return json_add('email-users', 'contacts', '0', 'table_checkboxes');"/> <input type="button"
                                                                                                                                                                                        value="Extend Next Date"
                                                                                                                                                                                        class=""
                                                                                                                                                                                        onclick="return json_add('extend-date', 'contacts', '0', 'table_checkboxes');"/> <input type="button"
                                                                                                                                                                                                                                                                                value="Mark Dead"
                                                                                                                                                                                                                                                                                class=""
                                                                                                                                                                                                                                                                                onclick="return json_add('contact_status', '', '0', 'table_checkboxes', 'status=3');" />
                </div>
            </div>

        </form>

    </div>



<?php

}

?>
