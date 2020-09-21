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

$permission = 'member';
$check = $admin->check_permissions('member', $employee);
if ($check != '1') {
    $admin->show_no_permissions();

} else {

    if (!empty($_GET['criteria_id'])) {
        $criteria_id = $_GET['criteria_id'];
    } else {
        $criteria_id = '';
    }

    $useDisplay = '';
    $useSort = '';
    $useOrder = '';

    if (! empty($criteria_id)) {
        $crit = new criteria($criteria_id);
        if (! empty($crit->data['sort'])) {
            $useSort = $crit->data['sort'];
            if ($useSort == 'username') {
                $useSort = 'ppSD_members.username';
            } else {
                $useSort = 'ppSD_member_data.' . $useSort;
            }
        }
        if (! empty($crit->data['sort_order'])) {
            $useOrder = $crit->data['sort_order'];
        }
        if (! empty($crit->data['display_per_page'])) {
            $useDisplay = $crit->data['display_per_page'];
        }
    }

    $filter_array_default = array();
    $table                = 'ppSD_members';
    $order                = 'ppSD_members.username';
    $dir                  = 'ASC';
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
        $force_filters[] = $employee['id'] . '||owner||eq||ppSD_members';
    }
    $gen_table = $admin->get_table($table, $_GET, $defaults, $force_filters, $criteria_id);

    ?>

    <form action="cp-includes/get_table.php" id="table_filters" method="post" onsubmit="return update_table();">
    <input type="hidden" name="order" value="<?php echo $gen_table['order']; ?>"/>
    <input type="hidden" name="dir" value="<?php echo $gen_table['dir']; ?>"/>
    <input type="hidden" name="menu" value="<?php echo $gen_table['menu']; ?>"/>
    <input type="hidden" name="table" value="<?php echo $table; ?>"/>
        <input type="hidden" name="criteria_id" value="<?php echo $criteria_id; ?>"/>
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
                <span><b>Members</b></span>
                <span class="div">|</span>
                <a href="null.php" onclick="return show_filters();">Filters<img src="imgs/down-arrow.png"
                                                                                id="filter_arrow" width="10" height="10"
                                                                                alt="Expand" border="0"
                                                                                class="icon-right"/></a>

                <?php
                if (! empty($_GET['criteria_id'])) {
                    ?>
                    <span class="div">|</span>
                    <a href="null.php" onclick="return show_criteria_actions();">Criteria Actions<img src="imgs/down-arrow.png"
                                                                                    id="filter_arrow1" width="10" height="10"
                                                                                    alt="Expand" border="0"
                                                                                    class="icon-right"/></a>
                <?php
                }
                ?>
                <span class="div">|</span>
			<span id="innerLinks">
				<a href="null.php" onclick="return load_page('member','add');">Create Member</a>
				<a href="null.php" onclick="return popup('member_types','');">Member Types</a>
				<a href="index.php?l=sources">Sources</a>
                <span class="div">|</span>
				<a href="null.php" onclick="return prep_export('member','<?php if (! empty($_GET['criteria_id'])) { echo $_GET['criteria_id']; } ?>');">Export</a>
                <?php
                if (! empty($_GET['criteria_id'])) {
                    ?>
                    <span class="div">|</span>
                    <a href="null.php" onclick="return popup('preview_criteria','id=<?php echo $_GET['criteria_id']; ?>&type=criteria','','0');">View Criteria</a>
                    <?php
                }
                ?>
            </span>
            </div>
            <div class="clear"></div>
        </div>
    </div>


    <div id="criteria_actions" class="fonts smaller">
        <table id="criteria_action_table">
            <thead>
            <tr>
                <th width="250">Name</th>
                <th>Description</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $criteriaActions = new criteriaActions('member');
            $actions = $criteriaActions->getList();
            foreach ($actions as $action) {
                echo "<tr>";
                echo "<td><a href=\"null.php\" onclick=\"return popup('criteria_action','scope=member&cid=" . $criteria_id . "&act=" . $action->id . "');\">" . $action->name . "</a></td>";
                echo "<td>" . $action->description . "</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
    </div>


    <div id="filters" class="fonts smaller">
    <div class="pad24">

    <div id="filters_top">
        <div id="filters_right">
            <input type="submit" value="Apply Filters" class="save"/>
        </div>

        <div id="filters_left">
            <span><b>Applying Filters</b></span>
            <!--<span><a href="null.php" onclick="return popup('filters-member');"><img src="imgs/icon-settings.png" width="16" height="16" border="0" alt="Settings" title="Settings" class="icon" />Settings</a></span>-->
	   			<span><?php
                    echo $admin->alpha_list('members', 'username', 'ppSD_members');
                    ?></span>
        </div>
        <div class="clear"></div>
    </div>

    <div class="col50">

        <?php

        $opt_filters = $db->get_option('member_filters');
        $add_filters = array();

        if (!empty($employee['options']['member_filters'])) {
            $add_filters = explode(',', $employee['options']['member_filters']);
        } 

        if (!empty($opt_filters)) {
            $add_filters = array_merge($add_filters, explode(',', $opt_filters));
        }

        $thefilters = array(
            'id:ppSD_members::',
            'username:ppSD_members::',
            'email:ppSD_members::',
            'last_name:::',
            'city:::',
            'state:::',
            'joined:ppSD_members:1:1',
            'last_login:ppSD_members:1:1',
            'last_date_check:ppSD_members:1:1',
            'last_renewal:ppSD_members:1:1',
        );
        $thefilters = array_merge($thefilters, $add_filters);

        foreach ($thefilters as $aFilter) {
            $exp = explode(':', $aFilter);
            if (empty($exp['1'])) {
                $exp['1'] = 'ppSD_member_data';
            }
            if ($exp['0'] == 'last_date_check') {
                $show = 'last_activity';
            } else {
                $show = $exp['0'];
            }

            ?>

            <div class="field">
                <label><?php echo format_db_name($show); ?></label>
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
                           onkeyup="return autocom('owner','id','username','ppSD_staff','username,first_name,last_name','staff');"
                           value="" style="width:110px;" class="filterinputtype"/>

                    <input type="hidden" name="filter[owner]" id="owner_id" value=""/>
                    -->
                    <p class="field_desc_show">Filter by items assigned only to a specific employee.</p>

                </div>

            </div>

        <?php

        }

        ?>

        <div class="field">
            <label>Member Type</label>
            <div class="field_entry">
                <select name="filter[member_type]" style="width:150px;">
                    <?php
                    echo $admin->member_types();
                    ?>
                </select>
            </div>
        </div>

        <div class="field">
            <label class="">Status</label>
            <div class="field_entry">
                <select name="filter[status]" style="width:150px;">
                    <option value=""></option>
                    <option value="A">Active</option>
                    <option value="I">Inactive</option>
                    <!--<option value="O">Overdue</option>-->
                    <option value="C">Suspended</option>
                    <option value="P">Pending E-Mail Confirmation</option>
                    <option value="Y">Pending Activation</option>
                </select>
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
                        <input type="button" value="Delete" class="del" onclick="return compile_delete('<?php echo $table; ?>','table_checkboxes');"/>
                    </div>
                    <span class="small gray caps bold" style="margin-right:24px;">
                        With Selected:
                    </span>
                    <input type="button" value="E-Mail" class="" onclick="return json_add('email-users', 'members', '0', 'table_checkboxes');"/>
                </div>
            </div>

        </form>

    </div>



<?php

}

?>
