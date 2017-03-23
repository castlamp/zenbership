
<ul id="nav">
<?php
if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['menu-crm'])) {
?>
    <li>
        <a href="index.php?l=accounts">Accounts</a>
        <ul>
            <li><a href="index.php?l=accounts">View All</a></li>
            <li><a href="index.php?l=accounts&filters[]=<?php echo $exp_date['0']; ?>||created||like||ppSD_accounts">Created Today</a>
            <li><a href="index.php?l=accounts&filters[]=<?php echo $day3; ?>||created||gt||ppSD_accounts">Created Within Last 3 Days</a></li>
            <li><a href="index.php?l=accounts&filters[]=<?php echo $day7; ?>||created||gt||ppSD_accounts">Created Within Last 7 Days</a></li>
            <li><a href="index.php?l=accounts&filters[]=<?php echo $day14; ?>||created||gt||ppSD_accounts">Created Within Last 14 Days</a></li>
            <li><a href="index.php?l=accounts&filters[]=<?php echo $day30; ?>||created||gt||ppSD_accounts">Created Within Last 30 Days</a></li>
            <?php
            if ($employee['permissions']['admin'] == '1') {
                ?>
                <li class="div"></li>
                <li><a href="returnnul.php" onclick="return popup('options','type=accounts');">Options</a></li>
            <?php
            }
            ?>
        </ul>
    </li>
</li>
<li>
    <a href="index.php?l=contacts">Contacts</a>
    <ul>
        <li class="title">Types</li>
        <li><a href="index.php?l=contacts&filters[]=Contact||type||eq||ppSD_contacts&filters[]=1||status||eq||ppSD_contacts">Contacts</a></li>
        <li><a href="index.php?l=contacts&filters[]=Lead||type||eq||ppSD_contacts&filters[]=1||status||eq||ppSD_contacts">Leads</a></li>
        <li><a href="index.php?l=contacts&filters[]=Opportunity||type||eq||ppSD_contacts&filters[]=1||status||eq||ppSD_contacts">Opportunities</a></li>
        <li><a href="index.php?l=contacts&filters[]=Customer||type||eq||ppSD_contacts&filters[]=1||status||eq||ppSD_contacts">Customers</a></li>
        <li class="div"></li>
        <li class="title">Sorting Options</li>
        <li>
            <a href="index.php?l=contacts">By Status</a>
            <ul>
                <li><a href="index.php?l=contacts&filters[]=1||status||eq||ppSD_contacts">Active</a></li>
                <li><a href="index.php?l=contacts&filters[]=2||status||eq||ppSD_contacts">Converted</a></li>
                <li><a href="index.php?l=contacts&filters[]=1||status||eq||ppSD_contacts&filters[]=<?php echo current_date(); ?>||next_action||lt||ppSD_contacts">Overdue</a></li>
                <li><a href="index.php?l=contacts&filters[]=3||status||eq||ppSD_contacts">Dead</a></li>
            </ul>
        </li>
        <li><a href="index.php?l=contacts">By Type</a>
            <ul>
                <li><a href="index.php?l=contacts&filters[]=Contact||type||like||ppSD_contacts&filters[]=1||status||eq||ppSD_contacts">Contacts</a></li>
                <li><a href="index.php?l=contacts&filters[]=Lead||type||like||ppSD_contacts&filters[]=1||status||eq||ppSD_contacts">Lead</a></li>
                <li><a href="index.php?l=contacts&filters[]=Opportunity||type||like||ppSD_contacts&filters[]=1||status||eq||ppSD_contacts">Opportunity</a></li>
                <li><a href="index.php?l=contacts&filters[]=Customer||type||like||ppSD_contacts&filters[]=1||status||eq||ppSD_contacts">Customer</a></li>
            </ul>
        </li>
        <li>
            <a href="index.php?l=contacts">By Creation Date</a>
            <ul>
                <li><a href="index.php?l=contacts&filters[]=<?php echo $exp_date['0']; ?>||created||like||ppSD_contacts">Today</a>
                <li><a href="index.php?l=contacts&filters[]=<?php echo $day3; ?>||created||gt||ppSD_contacts">Last 3 Days</a></li>
                <li><a href="index.php?l=contacts&filters[]=<?php echo $day7; ?>||created||gt||ppSD_contacts">Last 7 Days</a></li>
                <li><a href="index.php?l=contacts&filters[]=<?php echo $day14; ?>||created||gt||ppSD_contacts">Last 14 Days</a></li>
                <li><a href="index.php?l=contacts&filters[]=<?php echo $day30; ?>||created||gt||ppSD_contacts">Last 30 Days</a></li>
            </ul>
        </li>
        <li><a href="index.php?l=contacts&filters[]=<?php echo $exp_date['0']; ?>||next_action||like||ppSD_contacts">By Due Date</a>
            <ul>
                <li><a href="index.php?l=contacts&filters[]=<?php echo $exp_date['0']; ?>||next_action||like||ppSD_contacts&filters[]=1||status||eq||ppSD_contacts">DueToday</a></li>
                <li><a href="index.php?l=contacts&filters[]=<?php echo $day3f; ?>||next_action||lt||ppSD_contacts&filters[]=1||status||eq||ppSD_contacts&order=next_action">Next 3 Days</a></li>
                <li><a href="index.php?l=contacts&filters[]=<?php echo $day7f; ?>||next_action||lt||ppSD_contacts&filters[]=1||status||eq||ppSD_contacts&order=next_action">Next 7 Days</a></li>
                <li><a href="index.php?l=contacts&filters[]=<?php echo $day14f; ?>||next_action||lt||ppSD_contacts&filters[]=1||status||eq||ppSD_contacts&order=next_action">Next 14 Days</a></li>
                <li><a href="index.php?l=contacts&filters[]=<?php echo $day30f; ?>||next_action||lt||ppSD_contacts&filters[]=1||status||eq||ppSD_contacts&order=next_action">Next 30 Days</a></li>
            </ul>
        </li>
        <li><a href="null.php" onclick="return popup('build_criteria','type=search&act=contact');">Custom Reports</a>
            <ul>
                <?php
                $list = $admin->saved_criteria_list('contact', '', true);
                $upb = false;
                foreach ($list as $item) {
                    $upb = true;
                    echo '<li><a href="index.php?l=contacts&criteria_id=' . $item['id'] . '">' . $item['name'] . '</a></li>';
                }
                if (! $upb) {
                    echo '<li><i>No custom reports.</i></li>';
                }
                ?>
            </ul>
        </li>
        <?php
        if ($employee['permissions']['admin'] == '1') {
        ?>
            <li><a href="index.php?l=contacts&filters[]=2||owner||eq||ppSD_contacts">Unassigned</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1'|| ! empty($employee['permissions']['scopes']['conversions'])) {
        ?>
        <li><a href="index.php?l=conversions">Conversions</a></li>
        <?php
        }
        ?>
        <li><a href="index.php?l=contacts&filters[]=<?php echo $day3; ?>||created||gt||ppSD_contacts&order=created&dir=asc">Recent</a></li>

        <?php
        if ($employee['permissions']['admin'] == '1' ||
            ! empty($employee['permissions']['scopes']['import']) ||
            ! empty($employee['permissions']['scopes']['sources']) ||
            ! empty($employee['permissions']['scopes']['note_labels'])
        ) {
        ?>

        <li class="div"></li>
        <li class="title">Other</li>
        <?php
        if ($employee['permissions']['admin'] == '1'|| ! empty($employee['permissions']['scopes']['sources'])) {
        ?>
        <li><a href="index.php?l=sources">Sources</a>
            <ul>
                <li><a href="index.php?l=source_tracking">Tracking</a></li>
            </ul>
        </li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1') {
            ?>
            <li><a href="null.php" onclick="return popup('pipeline','');">Pipeline Steps</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1'|| ! empty($employee['permissions']['scopes']['import'])) {
            ?>
            <li><a href="returnnull.php" onclick="return popup('import','scope=contact');">Import</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1'|| ! empty($employee['permissions']['scopes']['note_labels'])) {
        ?>
        <li><a href="null.php" onclick="return popup('note_labels','');">Note Labels</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1') {
            ?>
            <li><a href="returnnul.php" onclick="return popup('options','type=contacts');">Options</a></li>
        <?php
        }
        }
        ?>
    </ul>
</li>
<?php
}
if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['menu-members'])) {
?>
<li>
    <a href="index.php?l=members">Members</a>
    <ul>
        <li class="title">Membership Roster</li>
        <li>
            <a href="index.php?l=members">By Status</a>
            <ul>
                <li><a href="index.php?l=members&filters[]=A||status||eq||ppSD_members">Active</a></li>
                <li><a href="index.php?l=members&filters[]=I||status||eq||ppSD_members">Inactive</a></li>
                <li><a href="index.php?l=members&filters[]=C||status||eq||ppSD_members">Suspended</a></li>
                <li><a href="index.php?l=members&filters[]=R||status||eq||ppSD_members">Rejected</a></li>
                <li><a href="index.php?l=members&filters[]=Y||status||eq||ppSD_members">Pending Approval</a></li>
                <li><a href="index.php?l=members&filters[]=P||status||eq||ppSD_members">Pending E-Mail Confirmation</a>
                </li>
            </ul>
        </li>
        <li>
            <a href="index.php?l=members">By Creation Date</a>
            <ul>
                <li><a href="index.php?l=members&filters[]=<?php echo $exp_date['0']; ?>||joined||like||ppSD_members">Today</a>
                <li><a href="index.php?l=members&filters[]=<?php echo $day3; ?>||joined||gt||ppSD_members">Last 3 Days</a></li>
                <li><a href="index.php?l=members&filters[]=<?php echo $day7; ?>||joined||gt||ppSD_members">Last 7 Days</a></li>
                <li><a href="index.php?l=members&filters[]=<?php echo $day14; ?>||joined||gt||ppSD_members">Last 14 Days</a></li>
                <li><a href="index.php?l=members&filters[]=<?php echo $day30; ?>||joined||gt||ppSD_members">Last 30 Days</a></li>
            </ul>
        </li>
        <li>
            <a href="index.php?l=members">By Last Renewal</a>
            <ul>
                <li><a href="index.php?l=members&filters[]=<?php echo $exp_date['0']; ?>||last_renewal||like||ppSD_members">Today</a>
                <li><a href="index.php?l=members&filters[]=<?php echo $day3; ?>||last_renewal||gt||ppSD_members">Last 3 Days</a></li>
                <li><a href="index.php?l=members&filters[]=<?php echo $day7; ?>||last_renewal||gt||ppSD_members">Last 7 Days</a></li>
                <li><a href="index.php?l=members&filters[]=<?php echo $day14; ?>||last_renewal||gt||ppSD_members">Last 14 Days</a></li>
                <li><a href="index.php?l=members&filters[]=<?php echo $day30; ?>||last_renewal||gt||ppSD_members">Last 30 Days</a></li>
            </ul>
        </li>
        <?php
        if ($employee['permissions']['admin'] == '1'|| ! empty($employee['permissions']['scopes']['member_types'])) {
        ?>
        <li>
            <a href="index.php?l=members">By Member Type</a>
            <ul>
                <?php
                $member_types = $admin->member_types('', 'list');
                echo $member_types;
                ?>
            </ul>
        </li>
        <li><a href="null.php" onclick="return popup('build_criteria','type=search&act=member');">Custom Reports</a>
            <ul>
                <?php
                $list = $admin->saved_criteria_list('member', '', true);
                $upa = false;
                foreach ($list as $item) {
                    $upa = true;
                    echo '<li><a href="index.php?l=members&criteria_id=' . $item['id'] . '">' . $item['name'] . '</a></li>';
                }
                if (! $upa) {
                    echo '<li><i>No custom reports.</i></li>';
                }
                ?>
            </ul>
        </li>
        <li class="div"></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1'|| ! empty($employee['permissions']['scopes']['logins'])) {
        ?>
        <li class="title">Other</li>
        <li>
            <a href="index.php?l=logins">Login Activity</a>
            <ul>
                <li><a href="index.php?l=logins&filters[]=<?php echo $exp_date['0']; ?>||date||like||ppSD_logins">Today</a></li>
                <li><a href="index.php?l=logins&filters[]=<?php echo $day7; ?>||date||gt||ppSD_logins">Last 7 Days</a></li>
                <li><a href="index.php?l=logins&filters[]=<?php echo $day14; ?>||date||gt||ppSD_logins">Last 14 Days</a></li>
                <li><a href="index.php?l=logins&filters[]=<?php echo $day30; ?>||date||gt||ppSD_logins">Last 30 Days</a></li>
            </ul>
        </li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1'|| ! empty($employee['permissions']['scopes']['login_announcements'])) {
        ?>
        <li>
            <a href="index.php?l=announcements">News</a>
            <ul>
                <li><a href="index.php?l=announcements">View All</a></li>
                <?php
                if ($employee['permissions']['admin'] == '1') {
                    ?>
                    <li class="div"></li>
                    <li><a href="returnnul.php" onclick="return popup('options','type=announcements');">Options</a></li>
                <?php
                }
                ?>
            </ul>
        </li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1'|| ! empty($employee['permissions']['scopes']['member_types'])) {
        ?>
        <li><a href="null.php" onclick="return popup('member_types','');">Membership Types</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1') {
            ?>
            <li><a href="returnnul.php" onclick="return popup('options','type=members');">Options</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1'|| ! empty($employee['permissions']['scopes']['import'])) {
        ?>
        <li><a href="returnnull.php" onclick="return popup('import','scope=member');">Import</a></li>
        <?php
        }
        ?>
    </ul>
</li>
<?php
}
if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['menu-shop'])) {
?>
<li>
    <a href="index.php?l=shop">Shop</a>
    <ul class="sub_menu">
        <?php
        if ($employee['permissions']['admin'] == '1' ||  ! empty($employee['permissions']['scopes']['transaction'])) {
        ?>
        <li class="title">Sort by Activity</li>
        <li>
            <a href="index.php?l=transactions">Transactions</a>
            <ul>
                <li><a href="index.php?l=transactions&filters[]=<?php echo $exp_date['0']; ?>||date_completed||like||ppSD_cart_sessions">Today</a></li>
                <li><a href="index.php?l=transactions&filters[]=<?php echo $day3; ?>||date_completed||gt||ppSD_cart_sessions">Last 3 Days</a></li>
                <li><a href="index.php?l=transactions&filters[]=<?php echo $day7; ?>||date_completed||gt||ppSD_cart_sessions">Last 7 Days</a></li>
                <li><a href="index.php?l=transactions&filters[]=<?php echo $day14; ?>||date_completed||gt||ppSD_cart_sessions">Last 14 Days</a></li>
                <li><a href="index.php?l=transactions&filters[]=<?php echo $day30; ?>||date_completed||gt||ppSD_cart_sessions">Last 30 Days</a></li>
                <li class="div"></li>
                <li><a href="index.php?l=transactions&filters[]=1||status||eq||ppSD_cart_sessions">Settled</a></li>
                <li><a href="index.php?l=transactions&filters[]=2||status||eq||ppSD_cart_sessions">Pending Payment</a></li>
                <li><a href="index.php?l=transactions&filters[]=0||status||eq||ppSD_cart_sessions">Unfinished</a></li>
                <li class="div"></li>
                <li><a href="index.php?l=billing_report">Billing Report</a></li>
            </ul>
        </li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' ||  ! empty($employee['permissions']['scopes']['subscription'])) {
        ?>
        <li>
            <a href="index.php?l=subscriptions">Subscriptions</a>
            <ul>
                <li><a href="index.php?l=subscriptions&filters[]=<?php echo $exp_date['0']; ?>||next_renew||like||ppSD_subscriptions&filters[]=1||status||e1||ppSD_subscriptions">Renewing Today</a></li>
                <li><a href="index.php?l=subscriptions&filters[]=<?php echo $day3f; ?>||next_renew||lt||ppSD_subscriptions&filters[]=1||status||e1||ppSD_subscriptions">Renewing within 3 days</a></li>
                <li><a href="index.php?l=subscriptions&filters[]=<?php echo $day7f; ?>||next_renew||lt||ppSD_subscriptions&filters[]=1||status||e1||ppSD_subscriptions">Renewing within 7 days</a></li>
                <li><a href="index.php?l=subscriptions&filters[]=<?php echo $day14f; ?>||next_renew||lt||ppSD_subscriptions&filters[]=1||status||e1||ppSD_subscriptions">Renewing within 14 days</a></li>
                <li><a href="index.php?l=subscriptions&filters[]=<?php echo $day30f; ?>||next_renew||lt||ppSD_subscriptions&filters[]=1||status||e1||ppSD_subscriptions">Renewing within 30 days</a></li>
                <li class="div"></li>
                <li><a href="index.php?l=subscriptions&filters[]=1||status||eq||ppSD_subscriptions">Active</a></li>
                <li><a href="index.php?l=subscriptions&filters[]=2||status||eq||ppSD_subscriptions">Canceled</a></li>
                <li class="div"></li>
                <li><a href="index.php?l=packages">Packages</a></li>
                <li><a href="returnnul.php" onclick="return popup('subscription_penalties','');">Penalties</a></li>
                <?php
                if ($employee['permissions']['admin'] == '1') {
                    ?>
                    <li class="div"></li>
                    <li><a href="returnnul.php" onclick="return popup('options','type=subscriptions');">Options</a></li>
                <?php
                }
                ?>
            </ul>
        </li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' ||  ! empty($employee['permissions']['scopes']['invoice'])) {
        ?>
        <li>
            <a href="index.php?l=invoices">Invoices</a>
            <ul>
                <li><a href="index.php?l=invoices&filters[]=3||status||eq||ppSD_invoices">Overdue</a></li>
                <li><a href="index.php?l=invoices&filters[]=2||status||eq||ppSD_invoices">Partially Paid</a></li>
                <li><a href="index.php?l=invoices&filters[]=1||status||eq||ppSD_invoices">Paid</a></li>
                <li><a href="index.php?l=invoices&filters[]=5||status||eq||ppSD_invoices">Dead</a></li>
                <?php
                if ($employee['permissions']['admin'] == '1') {
                    ?>
                    <li class="div"></li>
                    <li><a href="returnnul.php" onclick="return popup('options','type=invoices');">Options</a></li>
                <?php
                }
                ?>
            </ul>
        </li>
        <li class="div"></li>
        <?php
        }
        ?>
        <li class="title">Shop</li>
        <?php
        if ($employee['permissions']['admin'] == '1' ||  ! empty($employee['permissions']['scopes']['cart-report'])) {
        ?>
        <li><a href="index.php?l=shop_report">Monthly Overview</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' ||  ! empty($employee['permissions']['scopes']['categories'])) {
        ?>
        <li><a href="index.php?l=categories">Shop Categories</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' ||  ! empty($employee['permissions']['scopes']['product'])) {
        ?>
        <li>
            <a href="index.php?l=products">Products</a>
            <ul>
                <li><a href="index.php?l=products&filters[]=1||type||eq||ppSD_products">Single Purchase</a></li>
                <li><a href="index.php?l=products&filters[]=2||type||eq||ppSD_products">Subscription Products</a></li>
                <li><a href="index.php?l=products&filters[]=3||type||eq||ppSD_products">Trial Products</a></li>
                <li class="div"></li>
                <li><a href="index.php?l=products&filters[]=1||physical||eq||ppSD_products">Physical Items</a></li>
                <li><a href="index.php?l=products&filters[]=1||physical||neq||ppSD_products">Non-physical Items</a></li>
            </ul>
        </li>
        <?php
        }
        ?>

        <?php
        if ($employee['permissions']['admin'] == '1' ||
            ! empty($employee['permissions']['scopes']['promo_code']) ||
            ! empty($employee['permissions']['scopes']['promo_code_usage'])
        ) {
        ?>
        <li>
            <a href="index.php?l=promo_codes">Promotional Codes</a>
            <ul>
                <?php
                if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['promo_code'])) {
                ?>
                <li><a href="index.php?l=promo_codes">Manage Codes</a></li>
                <?php
                }
                if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['promo_code_usage'])) {
                ?>
                <li><a href="index.php?l=promo_code_usage">Usage Logs</a></li>
                <?php
                }
                ?>
            </ul>
        </li>
        <?php
        }
        ?>

        <?php
        if ($employee['permissions']['admin'] == '1' ||
            ! empty($employee['permissions']['scopes']['payment_gateway']) ||
            ! empty($employee['permissions']['scopes']['shop_terms']) ||
            ! empty($employee['permissions']['scopes']['shop_tax']) ||
            ! empty($employee['permissions']['scopes']['shop_shipping'])
        ) {
        ?>
        <li class="div"></li>
        <li class="title">Settings</li>
        <li>
            <a href="index.php?l=shop">Setup</a>
            <ul>
                <?php
                if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['payment_gateway'])) {
                ?>
                <li><a href="index.php?l=shop_payment_gateways">Payment Gateways</a></li>
                <?php
                }
                if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['shop_terms'])) {
                ?>
                <li><a href="index.php?l=shop_terms">Terms of Purchase</a></li>
                <?php
                }
                if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['shop_tax'])) {
                ?>
                <li><a href="index.php?l=shop_tax">Tax Classes</a></li>
                <?php
                }
                if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['shop_shipping'])) {
                ?>
                <li><a href="index.php?l=shop_shipping">Shipping Options</a></li>
                <?php
                }
                ?>
            </ul>
        </li>
        <?php
        }
        ?>
        <?php
        if ($employee['permissions']['admin'] == '1') {
            ?>
            <li><a href="returnnul.php" onclick="return popup('options','type=cart');">Options</a></li>
        <?php
        }
        ?>
    </ul>
</li>
<?php
}
if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['menu-events'])) {
?>
<li>
    <a href="index.php?l=events">Events</a>
    <ul>
        <?php
        if ($employee['permissions']['admin'] == '1' ||  ! empty($employee['permissions']['scopes']['event'])) {
        ?>
        <li class="title">Events</li>
        <li>
            <a href="index.php?l=events">Upcoming Events</a>
            <ul>
                <li><a href="index.php?l=events&filters[]=<?php echo $exp_date['0']; ?>||starts||like||ppSD_events">Today</a></li>
                <li><a href="index.php?l=events&filters[]=<?php echo $day3f; ?>||starts||lt||ppSD_events">Next 3 Days</a></li>
                <li><a href="index.php?l=events&filters[]=<?php echo $day7f; ?>||starts||lt||ppSD_events">Next 7 Days</a></li>
                <li><a href="index.php?l=events&filters[]=<?php echo $date_exp['0'] . '-' . $date_exp['1']; ?>||starts||like||ppSD_events">This Month</a></li>
                <li><a href="index.php?l=events&filters[]=<?php echo $date_exp['0']; ?>||starts||like||ppSD_events">This Year</a></li>
            </ul>
        </li>
        <li><a href="index.php?l=events&filters[]=<?php echo current_date(); ?>||starts||lt||ppSD_events">Past Events</a></li>
        <li class="div"></li>        <?php
        }
        if ($employee['permissions']['admin'] == '1' ||  ! empty($employee['permissions']['scopes']['calendar'])) {
        ?>
        <li class="title">Other</li>
        <li><a href="index.php?l=calendars">Calendars</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' ||  ! empty($employee['permissions']['scopes']['event_types'])) {
        ?>
        <li><a href="prevent_null.php" onclick="return popup('event_types');">Event Types</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1') {
            ?>
            <li class="div"></li>
            <li><a href="returnnul.php" onclick="return popup('options','type=events');">Options</a></li>
        <?php
        }
        ?>
    </ul>
</li>
<?php
}
if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['menu-cms'])) {
?>
<li>
    <a href="index.php?l=content">Content</a>
    <ul>
        <?php
        if ($employee['permissions']['admin'] == '1' ||  ! empty($employee['permissions']['scopes']['content'])) {
        ?>
        <li class="title">Content By Type</li>
        <li><a href="index.php?l=content&filters[]=folder||type||eq||ppSD_content">Secure Folders</a></li>
        <li><a href="index.php?l=content&filters[]=page||type||eq||ppSD_content">Custom Pages</a></li>
        <li><a href="index.php?l=content&filters[]=redirect||type||eq||ppSD_content">Redirections</a></li>
        <li class="div"></li>
        <li class="title">CMS Components</li>
        <li><a href="index.php?l=content&filters[]=section||type||eq||ppSD_content">Sections</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' ||  ! empty($employee['permissions']['scopes']['widgets'])) {
        ?>
        <li>
            <a href="index.php?l=widgets">Widgets</a>
            <ul>
                <li><a href="index.php?l=widgets&filters[]=code||type||eq||ppSD_widgets">PHP Code</a></li>
                <li><a href="index.php?l=widgets&filters[]=menu||type||eq||ppSD_widgets">Menus</a></li>
                <li><a href="index.php?l=widgets&filters[]=html||type||eq||ppSD_widgets">HTML Block</a></li>
                <li><a href="index.php?l=widgets&filters[]=upload_list||type||eq||ppSD_widgets">Upload List</a></li>
                <li class="div"></li>
                <li><a href="null.php" onclick="return command('widget_installer', '', '');">Run Installer</a></li>
            </ul>
        </li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1') {
            ?>
            <li class="div"></li>
            <li><a href="returnnul.php" onclick="return popup('options','type=site');">Options</a></li>
        <?php
        }
        ?>
    </ul>
</li>
<?php
}
if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['menu-connect'])) {
?>
<li>
    <a href="index.php?l=connect">Marketing</a>
    <ul>
        <li class="title">E-Mail</li>
        <?php
        if ($employee['permissions']['admin'] == '1' ||  ! empty($employee['permissions']['scopes']['campaigns'])) {
        ?>
        <li>
            <a href="index.php?l=email_campaigns">Campaigns</a>
            <ul>
                <li><a href="index.php?l=email_campaigns&filters[]=single_optin||optin_type||eq||ppSD_campaigns">Single Opt-In</a></li>
                <li><a href="index.php?l=email_campaigns&filters[]=double_optin||optin_type||eq||ppSD_campaigns">Double Opt-In</a></li>
                <li><a href="index.php?l=email_campaigns&filters[]=criteria||optin_type||eq||ppSD_campaigns">Automated Followup</a></li>
            </ul>
        </li>
        <?php
        }
        ?>
        <li>
            <a href="index.php?l=email_outbox">Engagement</a>
            <ul>
                <?php
                if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['email-outbox'])) {
                ?>
                <li><a href="index.php?l=email_outbox">Outbox</a></li>
                <?php
                }
                if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['email_tracking'])) {
                ?>
                <li><a href="index.php?l=email_tracking">Readership Logs</a></li>
                <?php
                }
                if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['email_milestones'])) {
                ?>
                <li><a href="index.php?l=email_milestones">Milestones</a></li>
                <?php
                }
                if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['email_clicks'])) {
                ?>
                <li><a href="index.php?l=email_clicks">Link Clicks</a></li>
                <?php
                }
                if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['emails-bounced'])) {
                ?>
                <li><a href="index.php?l=email_bounced">Bounced Logs</a></li>
                <?php
                }
                ?>
            </ul>
        </li>
        <?php
        if ($employee['permissions']['admin'] == '1' ||
            ! empty($employee['permissions']['scopes']['targeted'])
        ) {
        ?>
        <li><a href="returnnull.php" onclick="return popup('build_criteria_type','','1');">Targeted Blast</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' ||  ! empty($employee['permissions']['scopes']['email-queue'])) {
        ?>
        <li><a href="index.php?l=email_queue">Pending Queue</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1') {
            ?>
            <li><a href="returnnul.php" onclick="return popup('options','type=email');">Options</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['sms'])) {
        ?>
        <li class="div"></li>
        <li class="title">SMS</li>
        <!--<li><a href="index.php?l=email_campaigns&filters[]=sms||type||eq||ppSD_campaigns">Campaigns</a></li>-->
        <?php
            if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['sms-targeted'])) {
        ?>
        <li><a href="returnnull.php" onclick="return popup('build_criteria_type','type=sms','0');">Targeted Blast</a>
        </li>
        <!--<li><a href="index.php?l=sms_outbox">Outbox</a></li>-->
        <?php
            } // Mass SMS
        }
        if ($employee['permissions']['admin'] == '1') {
        ?>
            <li><a href="returnnul.php" onclick="return popup('options','type=sms');">Options</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' ||
            ! empty($employee['permissions']['scopes']['twitter']) ||
            ! empty($employee['permissions']['scopes']['facebook'])) {
        ?>
        <li class="div"></li>
        <li class="title">Social Media</li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1'|| ! empty($employee['permissions']['scopes']['facebook'])) {
        ?>
        <!--<li><a href="returnnull.php" onclick="return popup('facebook','','0');">Facebook</a></li>-->
        <?php
        }
        if ($employee['permissions']['admin'] == '1'|| ! empty($employee['permissions']['scopes']['twitter'])) {
            ?>
        <li><a href="returnnull.php" onclick="return popup('twitter','','0');">Twitter</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1') {
            ?>
            <li><a href="returnnul.php" onclick="return popup('options','type=social_media');">Options</a></li>
        <?php
        }
        ?>
    </ul>
</li>
<?php
}
if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['menu-integration'])) {
?>
<li>
    <a href="index.php?l=integration">Integrate</a>
    <ul>
        <li class="title">Extensions</li>
        <li><a href="http://www.zenbership.com/Extensions/" target="_blank">Extension Store</a></li>
        <!--<li><a href="null.php" onclick="return popup('extension_store','','1');">Extension Store</a></li>
        <li><a href="index.php?l=modules">Modules</a></li>-->
        <li>
            <a href="">Plugins</a>
            <ul>
                <li><a href="null.php" onclick="return command('plugin_installer', '', '');">Run Installer</a></li>
            </ul>
        </li>
        <li>
            <a href="index.php?l=custom_actions">Hooks</a>
            <ul>
                <li><a href="index.php?l=custom_actions&filters[]=1||type||eq||ppSD_custom_actions">PHP Code Execution</a></li>
                <li><a href="index.php?l=custom_actions&filters[]=2||type||eq||ppSD_custom_actions">E-Mail Dispatcher</a></li>
                <li><a href="index.php?l=custom_actions&filters[]=3||type||eq||ppSD_custom_actions">MySQL Command Execution</a></li>
                <li><a href="index.php?l=custom_actions&filters[]=5||type||eq||ppSD_custom_actions">Outside Connection</a></li>
            </ul>
        </li>
        <li class="div"></li>
        <li class="title">Templates</li>
        <li>
            <a href="index.php?l=templates_html">HTML Templates</a>
            <ul>
                <li><a href="null.php" onclick="return popup('theme','type=html','1');">Theme</a></li>
                <li><a href="index.php?l=templates_html">Template Editor</a></li>
            </ul>
        </li>
        <li>
            <a href="index.php?l=templates_email">E-Mail Templates</a>
            <ul>
                <li><a href="null.php" onclick="return popup('theme','type=email','1');">Theme</a></li>
                <li><a href="index.php?l=templates_email">Template Editor</a></li>
            </ul>
        </li>
        <!--
        <li>
            <a href="index.php?l=templates_mobile">Mobile Templates</a>
            <ul>
                <li><a href="null.php" onclick="return popup('theme','type=mobile','1');">Theme</a></li>
                <li><a href="index.php?l=templates_mobile">Template Editor</a></li>
            </ul>
        </li>
        -->
        <li class="div"></li>
        <li class="title">Forms</li>
        <li>
            <a href="index.php?l=forms">Forms</a>
            <ul>
                <li><a href="index.php?l=forms&filters[]=contact||type||eq||ppSD_forms">Contact Forms</a></li>
                <li><a href="index.php?l=forms&filters[]=register-free||type||eq||ppSD_forms">Free Registration Forms</a></li>
                <li><a href="index.php?l=forms&filters[]=register-paid||type||eq||ppSD_forms">Paid Registration Forms</a></li>
                <li><a href="index.php?l=forms&filters[]=dependency||type||eq||ppSD_forms">Dependency Forms</a></li>
                <li><a href="index.php?l=forms&filters[]=update_account||type||eq||ppSD_forms">Update Forms</a></li>
            </ul>
        </li>
        <li><a href="index.php?l=database_fields">Fields</a></li>
        <li><a href="index.php?l=fieldsets">Fieldsets</a></li>
        <?php
        if ($employee['permissions']['admin'] == '1') {
        ?>
            <li class="div"></li>
            <li class="title">Administrative Tasks</li>
            <li><a href="returnnul.php" onclick="return popup('options','type=general');">Options</a></li>
            <li><a href="index.php?l=error_codes">Language &amp; Errors</a></li>
            <li>
                <a href="null.php" onclick="return false;">Database</a>
                <ul>
                    <!--
                    <li><a href="null.php" onclick="return confirm_act('update','','');">Update Software</a>
                    <li class="div"></li>
                    -->
                    <li><a href="null.php" onclick="return confirm_act('clean_db','','type=temp');">Clear Temp Data</a></li>
                    <?php
                    if ($db->get_option('use_cache') == '1') {
                    ?>
                        <li><a href="null.php" onclick="return confirm_act('clean_db','','type=cache');">Clear Cache</a></li>
                    <?php
                    }
                    ?>
                    <li><a href="null.php" onclick="return confirm_act('clean_db','','type=stats');">Clear Statistics</a></li>
                </ul>
            </li>
        <?php
        }
        ?>
    </ul>
</li>
<?php
}
$extensionLinks = array();
$path = PP_PATH . '/custom/plugins';
$dh = opendir($path);
while (false !== ($filename = readdir($dh))) {
    $fullpath = $path . '/' . $filename;

    if ($filename == '.' || $filename == '..' || ! is_dir($fullpath)) continue;

    if (is_dir($path . '/' . $filename . '/admin')) {
        $package = require $path . '/' . $filename . '/admin/package.php';
        $extensionLinks[$package['menu']] = 'l=home&plugin=' . $filename;
    }
}
if (! empty($extensionLinks)) {
    echo "<li>Extensions<ul>";
    foreach ($extensionLinks as $item => $link) {
        echo "<li><a href=\"?$link\">$item</a></li>";
    }
    echo "</ul></li>";
}
?>
<li>
    <img src="imgs/icon-quickadd.png" id="quickadd" width="16" height="16" alt="Quick Add" title="Quick Add" class="icon_flat"/>
    <ul>
        <li class="title">Quick Add</li>
        <?php
        if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['account'])) {
        ?>
        <li><a href="returnnull.php" onclick="return load_page('account','add');">Account</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['contact'])) {
        ?>
        <li><a href="returnnull.php" onclick="return load_page('contact','add');">Contact</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['content'])) {
        ?>
        <li><a href="returnnull.php" onclick="return popup('content_type','quickadd=1','1');">Content</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['event'])) {
        ?>
        <li><a href="returnnull.php" onclick="return popup('event-add','quickadd=1','1');">Event</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['form'])) {
        ?>
        <li><a href="returnnull.php" onclick="return popup('forms-add-select','quickadd=1','1');">Form</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['invoice'])) {
        ?>
        <li><a href="returnnull.php" onclick="return popup('invoice-add','quickadd=1','1');">Invoice</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['member'])) {
        ?>
        <li><a href="returnnull.php" onclick="return load_page('member','add');">Member</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['product'])) {
        ?>
        <li><a href="returnnull.php" onclick="return popup('product-add','quickadd=1','1');">Product</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['subscription'])) {
        ?>
        <li><a href="returnnull.php" onclick="return popup('subscription-add','quickadd=1');">Subscription</a></li>
        <?php
        }
        if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['transaction'])) {
        ?>
        <li><a href="returnnull.php" onclick="return popup('transaction-add','add','1');">Transaction</a></li>
        <?php
        }
        ?>
</li>
</ul>
</li>
<li>
    <img src="imgs/icon-fav-on.png" id="favorites" width="16" height="16" alt="Favorites" title="Favorites" class="icon_flat"/>
    <ul id="favorite_list">
        <?php echo $admin->get_favorites($employee['id']); ?>
    </ul>
</li>
<?php
// icon-lg-plugins.png
if ($employee['permissions']['admin'] == '1' || ! empty($employee['permissions']['scopes']['plugins'])) {
?>
<li>
<img src="imgs/icon-lg-modules.png" id="plugins" width="16" height="16" alt="Plugins" title="Plugins" class="icon_flat"/>
    <ul id="favorite_list">
        <?php
        echo $admin->get_plugins();
        ?>
    </ul>
</li>
<?php
}
?>
</ul>