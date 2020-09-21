<?php

/**
 * Alter field names in the top row: special_heading_rules();
 * Alter cell content: get_cell_content();
 * Alter what columns appear in the top row: get_custom_headings();
 *
 * @author          Castlamp
 * @link            http://www.castlamp.com/
 * @link            http://www.zenbership.com/
 * @copyright   (c) 2013-2016 Castlamp
 * @license         http://www.gnu.org/licenses/gpl-3.0.en.html
 * @project         Zenbership Membership Software
 */

class table extends db
{

    protected $scope, $scope_table, $row_id, $current, $special_fields, $employee, $scope_override,
        $order, $dir, $display, $page, $options, $current_row_data, $data, $add_qs, $mobile;
    public $headings, $heading_row, $cell;
    protected $plugin_table;

    /**
     * @param string $scope Determines what we are rendering. 'events', 'calendars', etc.
     * @param array  $data  Data being placed into the row.
     * @param string $act   'cell', 'headings'
     */
    function __construct($scope, $scope_table = '', $scope_override = array(), $force_headings = array())
    {
        global $employee;
        $this->employee = $employee;

        if (defined('ZEN_CUS_EXTENSION')) {
            $this->scope = ZEN_CUS_EXTENSION;
            $this->plugin_table = true;
        }
        else if (substr($scope_table, 0, 10) == 'zen_plugin') {
            $this->scope = $scope;
            $this->plugin_table = true;
        }
        else {
            $this->scope = $scope;
            $this->plugin_table = false;
        }

        $this->scope_table = $scope_table;
        $this->scope_override = $scope_override;
        $this->current = 'odd';
        if (check_mobile()) {
            $this->mobile = '1';
        }
        else {
            $this->mobile = '0';
        }
        $this->default_options();
        $this->get_headings($force_headings);
    }

    /**
     * Potentially overridden in get_headings

     */
    function default_options()
    {
        $this->options = array(
            'skip_delete' => '0',
            'duplicate'   => '0',
        );

    }

    /**
     * Determine the headings for this table.
     *
     * @param array $custom_headings
     */
    function get_headings($custom_headings = array())
    {
        if ($this->plugin_table) {
            $getPackage = require PP_PATH . '/custom/plugins/' . $this->scope . '/admin/package.php';
            $tableData = $getPackage[$this->scope_table];
            $this->headings = $tableData['force_headings'];
        }
        else if (! empty($custom_headings)) {
            $this->headings = $custom_headings;
        }
        else {

            // Get the standard headings
            $this->get_custom_headings();
            // Now check for custom headings.
            $check1 = $this->scope . '_headings_' . $this->employee['id'];
            $headings = $this->get_option($check1);
            if ( ! empty($headings)) {
                $this->headings = explode(',', $headings);
            }
            else {
                $check2 = $this->scope . '_headings_admin';
                $heads2 = $this->get_option($check2);
                if ($this->employee['permissions']['admin'] == '1' && ! empty($heads2)) {
                    $check2 = $this->scope . '_headings_admin';
                    $this->headings = explode(',', $this->get_option($check2));
                }
                else {
                    if (empty($this->headings)) {
                        $check3 = $this->scope . '_headings';
                        $this->headings = explode(',', $this->get_option($check3));
                    }
                }
            }

            // Mobile?
            if ($this->mobile == '1') {
                $temp = $this->headings;
                $this->headings = array($temp['0']);
            }
        }

        $this->render_headings();
    }

    /**
     * This determines what headings appear
     * in the top row of a table.
     */
    function get_custom_headings()
    {
        if ($this->scope_table == 'ppSD_cart_categories') {
            $this->headings = array(
                'name',
                'subcategory',
                'cols',
            );
            $this->options = array(
                'show_type'      => 'popup_small',
                'scope_override' => 'category-edit',
            );
        }
        else if ($this->scope_table == 'ppSD_login_announcements') {
            $this->headings = array(
                'title',
                'starts',
                'ends',
                'active',
                'type',
                'public',
            );
            $this->options = array(
                'show_type'      => 'popup_large',
                'scope_override' => 'announcement-add',
            );
        }
        else if ($this->scope_table == 'ppSD_custom_actions') {
            $this->headings = array(
                'name',
                'trigger',
                'type',
                'when',
                'active',
                'plugin',
            );
            $this->options = array(
                'show_type'      => 'popup_large',
                'scope_override' => 'hook',
            );
        }
        else if ($this->scope_table == 'ppSD_packages') {
            $this->headings = array(
                'name',
            );
            $this->options = array(
                'show_type'      => 'popup_small',
                'scope_override' => 'package',
            );
        }
        else if ($this->scope_table == 'ppSD_calendars') {
            $this->headings = array(
                'name',
                'id',
                'members_only',
                'style',
            );
            $this->options = array(
                'show_type'      => 'popup_small',
                'scope_override' => 'calendar-edit',
            );
        }
        else if ($this->scope_table == 'ppSD_sources') {
            $this->headings = array(
                'name',
                'id',
                'trigger',
                'redirect',
                'redirect_b',
            );
            $this->options = array(
                'show_type'      => 'popup_small',
                'scope_override' => 'source-edit',
            );
        }
        else if ($this->scope_table == 'ppSD_source_tracking') {
            $this->headings = array(
                'date',
                'source_id',
                'referrer',
                'converted_date',
                'user_type',
                'user_id',
                'link_variation',
            );
            $this->options = array(
                'show_type'      => 'none',
                'scope_override' => '',
            );
        }
        else if ($this->scope_table == 'ppSD_lead_conversion') {
            $this->headings = array(
                'date',
                'contact_id',
                'user_id',
                'owner',
                'estimated_value',
                'actual_value',
                'percent_change',
            );
            $this->options = array(
                'show_type'      => 'popup_small',
                'scope_override' => 'conversion',
            );
        }
        else if ($this->scope_table == 'ppSD_content') {
            $this->headings = array(
                'name',
                'type',
                'url',
                'id',
            );
            $this->options = array(
                'show_type'      => 'popup_large',
                'scope_override' => 'content-edit',
            );
        }
        else if ($this->scope_table == 'ppSD_cart_items_complete') {
            $this->headings = array(
                'cart_session',
                'date',
                'total',
                'status',
            );
            $this->options = array(
                'show_type'       => 'slider',
                'scope_override'  => 'transaction',
                'use_id_for_load' => 'cart_session',
            );
        }
        else if ($this->scope_table == 'ppSD_content_access') {
            $this->headings = array(
                'content',
                'expires',
                'added',
            );
            $this->options = array(
                'show_type'      => 'none',
                'scope_override' => '',
            );
        }
        else if ($this->scope_table == 'ppSD_fields') {
            $this->headings = array(
                'display_name',
                'type',
                'special_type',
                'id',
                'maxlength',
            );
            $this->options = array(
                'show_type'      => 'popup_large',
                'scope_override' => 'field-edit',
            );
        }
        else if ($this->scope_table == 'ppSD_campaigns') {
            $this->headings = array(
                'name',
                'id',
                'optin_type',
                'type',
                'user_type',
                'subscribers',
                'status',
            );
            $this->options = array(
                'show_type'      => 'slider',
                'scope_override' => 'campaign',
            );
        }
        else if ($this->scope_table == 'ppSD_saved_emails') {
            $this->headings = array(
                'date',
                'subject',
                'user_id',
                'user_type',
                'fail'
            );
            $this->options = array(
                'show_type'      => 'popup_large',
                'scope_override' => 'email-view',
            );
        }
        else if ($this->scope_table == 'ppSD_email_scheduled') {
            $this->headings = array(
                'email_id',
                'user_id',
                'user_type',
                'added',
                'type'
            );
            $this->options = array(
                'show_type'      => 'popup_large',
                'scope_override' => 'email-view-scheduled',
            );
        }
        else if ($this->scope_table == 'ppSD_bounced_emails') {
            $this->headings = array(
                'email_id',
                'date',
                'user_id',
                'user_type',
            );
            $this->options = array(
                'show_type'       => 'popup_large',
                'scope_override'  => 'email-view',
                'use_id_for_load' => 'email_id',
            );
        }
        else if ($this->scope_table == 'ppSD_email_trackback') {
            $this->headings = array(
                'email_id',
                'date',
                'last_viewed',
                'times_opened',
            );
            $this->options = array(
                'show_type'       => 'popup_large',
                'scope_override'  => 'email-view',
                'use_id_for_load' => 'email_id',
            );
        }
        else if ($this->scope_table == 'ppSD_tracking_activity') {
            $this->headings = array(
                'date',
                'type',
                'value',
            );
            $this->options = array(
                'show_type'      => 'none',
                'scope_override' => '',
            );
        }
        else if ($this->scope_table == 'ppSD_link_tracking') {
            $this->headings = array(
                'email_id',
                'first_clicked',
                'last_clicked',
                'clicked',
                'link',
            );
            $this->options = array(
                'show_type'       => 'popup_large',
                'scope_override'  => 'email-view',
                'use_id_for_load' => 'email_id',
            );
        }
        else if ($this->scope_table == 'ppSD_staff') {
            $this->headings = array(
                'username',
                'last_name',
                'first_name',
                'department',
                'status',
            );
            $this->options = array(
                'show_type'      => 'slider',
                'scope_override' => 'employee',
            );
        }
        else if ($this->scope_table == 'ppSD_usage_logs') {
            $this->headings = array(
                'username',
                'start_date',
                'task',
                'act_id',
            );
            $this->options = array(
                'show_type'       => 'slider',
                'scope_override'  => 'employee',
                'use_id_for_load' => 'username',
            );
        }
        else if ($this->scope_table == 'ppSD_criteria_cache') {
            $this->headings = array(
                'name',
                'type',
            );
            $this->options = array(
                // 'skip_delete'    => '1',
                'show_type'      => 'none',
                'show_type'       => 'popup_small',
                'scope_override' => 'preview_criteria',
            );
            //<a href="null.php" onclick="return popup('preview_criteria','id=174&amp;type=criteria','','0');">
        }
        else if ($this->scope_table == 'ppSD_error_codes') {
            $this->headings = array(
                'code',
                'lang',
                'msg',
            );
            $this->options = array(
                'skip_delete'     => '1',
                'show_type'       => 'popup_small',
                'scope_override'  => 'error_code-edit',
                'use_id_for_load' => 'code',
                'force_tr_id'     => 'code',
            );
        }
        else if ($this->scope_table == 'ppSD_events') {
            $this->headings = array(
                'name',
                'starts',
                'ends',
                'total_rsvps',
                'max_rsvps',
                'location_name',
            );
            $this->options = array(
                'duplicate'      => '1',
                'show_type'      => 'slider',
                'scope_override' => 'event',
            );
        }
        else if ($this->scope_table == 'ppSD_history') {
            $this->headings = array(
                'when',
                'details',
                'date',
                'plugin',
            );
            $this->options = array(
                'skip_delete'    => '1',
                'show_type'      => 'none',
                'scope_override' => '',
            );
        }
        else if ($this->scope_table == 'ppSD_campaign_logs') {
            $this->headings = array(
                'title',
                'date',
                'user_id',
            );
            $this->options = array(
                'scope_override'  => 'email-view',
                'use_id_for_load' => 'email_id',
            );
        }
        else if ($this->scope_table == 'ppSD_uploads') {
            $this->headings = array(
                'filename',
                'date',
                'label',
                'size',
            );
            $this->options = array(
                'scope_override' => '',
                'show_type'      => 'none',
            );
        }
        else if ($this->scope_table == 'ppSD_campaign_subscriptions') {
            $this->headings = array(
                'date',
                'user_id',
                'active',
                'subscribed_by',
            );
            $this->options = array(
                'scope_override' => '',
                'show_type'      => 'none',
                'table'          => 'subslider_table',
            );
        }
        else if ($this->scope_table == 'ppSD_campaign_unsubscribe') {
            $this->headings = array(
                'date',
                'user_id',
                'reason',
                'by',
            );
            $this->options = array(
                'scope_override' => '',
                'show_type'      => 'none',
                'table'          => 'subslider_table',
            );
        }
        else if ($this->scope_table == 'ppSD_campaign_items') {
            $this->headings = array(
                'title',
                'timeframe',
            );
            $this->options = array(
                'scope_override' => 'campaign_message-add',
                'show_type'      => 'popup_large',
            );
            $this->add_qs = '&campaign_id=%campaign_id%';
        }
        else if ($this->scope_table == 'ppSD_fieldsets') {
            $this->headings = array(
                'name',
                'desc',
            );
            $this->options = array(
                'show_type'      => 'popup_large',
                'scope_override' => 'fieldset-add',
            );
        }
        else if ($this->scope_table == 'ppSD_forms') {
            $this->headings = array(
                'id',
                'name',
                'type',
                'code_required',
                'disabled',
            );
            $this->options = array(
                'show_type'      => 'popup_large',
                'scope_override' => 'forms-edit',
            );
        }
        else if ($this->scope_table == 'ppSD_invoices') {
            $this->headings = array(
                'company_name',
                'contact_name',
                'date',
                'date_due',
                'due',
                'paid',
                'status',
            );
            $this->options = array(
                'show_type'      => 'slider',
                'scope_override' => 'invoice',
            );
        }
        else if ($this->scope_table == 'ppSD_logins') {
            $this->headings = array(
                'member_id',
                'date',
                'session_id',
                'ip',
                'browser_short',
                'type',
                'notes',
            );
            $this->options = array(
                'show_type'      => 'none',
                'scope_override' => '',
            );
        }
        else if ($this->scope_table == 'ppSD_notes') {
            $this->headings = array(
                'name',
                'date',
                'label',
                'public',
                'deadline',
                'value',
                'for'
            );
            $this->options = array(
                'complete'       => '1',
                'show_type'      => 'popup_large',
                'scope_override' => 'note-add',
            );
        }
        else if ($this->scope_table == 'ppSD_products') {
            $this->headings = array(
                'id',
                'name',
                'category',
                'type',
                'price',
            );
            $this->options = array(
                'duplicate'      => '1',
                'show_type'      => 'slider',
                'scope_override' => 'product',
            );
        }
        else if ($this->scope_table == 'ppSD_cart_coupon_codes_used') {
            $this->headings = array(
                'code',
                'date',
                'order_id',
                'member_id',
                'savings',
            );
            $this->options = array(
                'show_type'      => 'none',
                'scope_override' => '',
            );
        }
        else if ($this->scope_table == 'ppSD_cart_coupon_codes') {
            $this->headings = array(
                'id',
                'dollars_off',
                'percent_off',
                'date_start',
                'date_end',
            );
            $this->options = array(
                'show_type'      => 'popup_small',
                'scope_override' => 'promo_code-edit',
            );
        }
        else if ($this->scope_table == 'ppSD_payment_gateways') {
            $this->headings = array(
                'name',
                'code',
                'fee_flat',
                'fee_percent',
                'test_mode',
            );
            $this->options = array(
                'skip_delete'    => '1',
                'show_type'      => 'popup_small',
                'scope_override' => 'payment_gateway-edit',
            );
        }
        else if ($this->scope_table == 'ppSD_shipping_rules') {
            $this->headings = array(
                'name',
                'type',
                'details',
                'cost',
            );
            $this->options = array(
                'show_type'      => 'popup_small',
                'scope_override' => 'shop_shipping-edit',
            );
        }
        else if ($this->scope_table == 'ppSD_tax_classes') {
            $this->headings = array(
                'name',
                'state',
                'country',
                'percent_physical',
                'percent_digital'
            );
            $this->options = array(
                'show_type'      => 'popup_small',
                'scope_override' => 'shop_tax-edit',
            );
        }
        else if ($this->scope_table == 'ppSD_cart_terms') {
            $this->headings = array(
                'name',
            );
            $this->options = array(
                'show_type'      => 'popup_large',
                'scope_override' => 'shop_terms-edit',
            );
        }
        else if ($this->scope_table == 'ppSD_subscriptions') {
            $this->headings = array(
                'id',
                'member_id',
                'product',
                'next_renew',
                'price',
                'status',
            );
            $this->options = array(
                'show_type'      => 'slider',
                'scope_override' => 'subscription',
            );
        }
        else if ($this->scope_table == 'ppSD_cart_sessions' || $this->scope_table == 'ppSD_cart_session_totals') {
            if ($this->scope_table == 'ppSD_cart_session_totals') {
                $this->headings = array(
                    'id',
                    'date_completed',
                    //'method',
                    'total',
                    'subtotal',
                    'shipping',
                    'tax',
                    'gateway_fees',
                    'savings',
                    //'state',
                    //'country',
                );
            }
            else {
                $this->headings = array(
                    'id',
                    'date_completed',
                    'payment_gateway',
                    'total',
                    'status',
                );
            }
            $this->options = array(
                'show_type'      => 'slider',
                'scope_override' => 'transaction',
            );
        }
        else if ($this->scope_table == 'ppSD_widgets') {
            $this->headings = array(
                'id',
                'name',
                'type',
                'active',
            );
            $this->options = array(
                'show_type'      => 'popup_large',
                'scope_override' => 'widgets-edit',
            );
        }
        else if ($this->scope_table == 'ppSD_members') {
            $this->headings = array(
                'username',
                'joined',
                'last_name',
                'first_name',
                'email',
                'profile_pic',
            );
            $this->options = array(
                'show_type'      => 'slider',
                'scope_override' => 'member',
            );
        }
        else if ($this->scope_table == 'ppSD_member_types') {
            $this->headings = array(
                'name',
            );
            $this->options = array(
                'show_type'      => 'popup_small',
                'scope_override' => 'member_type-edit',
            );
        }
        else if ($this->scope_table == 'ppSD_event_rsvps') {
            $this->headings = array(
                'last_name',
                'first_name',
                'date',
                'status',
                'type'
            );
            $this->options = array(
                'show_type'      => 'popup_small',
                'scope_override' => 'rsvp_view',
                'table'          => 'subslider_table',
                'checkin' => true,
            );
            $this->add_qs = '&event_id=%event_id%';
        }
        else if ($this->scope_table == 'ppSD_invoice_payments') {
            $this->headings = array(
                'order_id',
                'date',
                'paid',
                'new_balance'
            );
            $this->options = array(
                'show_type'       => 'slider',
                'scope_override'  => 'transaction',
                'use_id_for_load' => 'order_id',
            );
        }
        else if ($this->scope_table == 'ppSD_accounts') {
            $this->headings = array(
                //    'profile_pic',
                'name',
                'city',
                'state',
                'phone',
                'created'
            );
            $this->options = array(
                'show_type'      => 'slider',
                'scope_override' => 'account',
            );
        }
        else if ($this->scope_table == 'ppSD_login_announcement_regions') {
            $this->headings = array(
                'name',
                'tag',
                'display',
                'snippet_length',
                'template_set_prefix',
            );
            $this->options = array(
                'show_type'      => 'popup_small',
                'scope_override' => 'news_regions',
            );
        }
        else if ($this->scope_table == 'ppSD_contacts') {
            $this->headings = array(
                'profile_pic',
                'last_name',
                'first_name',
                'type',
                'next_action',
                'account',
                'expected_value'
            );
            $this->options = array(
                'extend'         => '1',
                'show_type'      => 'slider',
                'scope_override' => 'contact',
            );
        }

        // Overriding the default scope information?
        if ( ! empty($this->scope_override)) {
            $this->options['scope_override'] = $this->scope_override['page'];
            $this->options['show_type'] = $this->scope_override['type'];
        }
    }

    /**
     * Render the table's headings.
     */
    function render_headings()
    {
        // Headings
        $this->heading_row = '<thead>';
        $this->heading_row .= '<tr>';
        if (empty($this->options['skip_delete'])) {
            if (empty($this->options['table'])) {
                $table = '';
            }
            else {
                $table = $this->options['table'];
            }
            $this->heading_row .= '<th width="24" class="center"><center><a href="null.php" onclick="return check_all(\'' . $table . '\');"><img src="imgs/tick-black.png" width="16" height="16" border="0" alt="Check All" title="Check All" style="vertical-align:middle;" /></a></center></th>';
        }

        foreach ($this->headings as $heading_name) {
            if ($heading_name == 'profile_pic') {
                $show = '';
                $width = ' width="24"';
            }
            else {
                $show = $this->special_heading_rules($heading_name); // $heading_name
                $width = '';
            }
            $this->heading_row .= '<th' . $width . '>' . $show . '</th>';
        }
        if (empty($this->options['skip_delete'])) {
            if ( ! empty($this->options['duplicate']) || ! empty($this->options['complete'])) {
                $width = '36';
            }
            else {
                $width = '16';
            }
            $this->heading_row .= '<th width="' . $width . '">';
            if ($this->mobile != '1' && ! $this->plugin_table) {
                $this->heading_row .= '<center><a href="null.php" onclick="return popup(\'tableheadings\',\'type=' . $this->scope . '\');"><img src="imgs/icon-settings.png" width="16" height="16" border="0" alt="Settings" title="Settings" style="vertical-align:middle;" /></a></center>';
            }
            $this->heading_row .= '</th>';
        }
        $this->heading_row .= '</tr>';
        $this->heading_row .= '</thead>';

    }

    /**
     * Sets the heading name to something other
     * than the default, if applicable.
     *
     * @param $heading_name
     *
     * @return string
     */
    function special_heading_rules($heading_name)
    {
        if ($heading_name == 'disabled' || $heading_name == 'fail') {
            return 'Status';
        }
        else if ($heading_name == 'browser_short') {
            return 'Browser';
        }
        else if ($this->scope_table == 'ppSD_fields' && $heading_name == 'id') {
            return 'Caller Tag';
        }
        else {
            return $this->get_field_name($heading_name);
        }

    }

    /**
     * Determines the correct "Display Name" for a
     * field based on the field's ID.
     *
     * @param $heading_name
     *
     * @return string
     */
    function get_field_name($heading_name)
    {
        $find = $this->get_array("
            SELECT `display_name`
            FROM `ppSD_fields`
            WHERE `id`='" . $this->mysql_clean($heading_name) . "'
            LIMIT 1
        ");
        if ( ! empty($find['display_name'])) {
            return $find['display_name'];
        }
        else {
            return format_db_name($heading_name);
        }

    }

    /**
     * Render a row in the table.
     *
     * @param array $data    Current row's data.
     * @param bool  $skip_tr Used after edits to update the row's TR content without adding another <tr>
     *
     * @return string Table cell.
     */
    function render_cell($data, $skip_tr = '0')
    {
        // Set current information
        $this->current_row_data = $data;
        if ( ! empty($this->options['use_id_for_load'])) {
            $row_id = $this->current_row_data[$this->options['use_id_for_load']];
        }
        else {
            $row_id = $this->current_row_data['id'];
        }
        // Special Additions
        if ( ! empty($this->add_qs)) {
            foreach ($this->current_row_data as $name => $value) {
                $this->add_qs = str_replace('%' . $name . '%', $value, $this->add_qs);
            }
        }
        // Prepare field processing
        $this->special_fields = new special_fields($this->scope);
        $this->special_fields->update_row($data);
        // Continue to render row.
        if ($this->current == 'even') {
            $this->current = 'odd';
        }
        else {
            $this->current = 'even';
        }
        $this->current .= $this->check_custom_class();
        // Render row
        $cell = '';
        if ($skip_tr != '1') {
            if ( ! empty($this->options['force_tr_id'])) {
                $trid = $data[$this->options['force_tr_id']];
            }
            else {
                $trid = $data['id'];
            }
            $cell .= "\n" . '<tr id="td-cell-' . $trid . '" class="' . $this->current . '">' . "\n";
        }
        if (empty($this->options['skip_delete'])) {
            $cell .= '<td class="center"><input type="checkbox" name="' . $data['id'] . '" value="1" /></td>' . "\n";
        }
        $cell_no = 0;

        if (! empty($this->current_row_data['id'])) {
            $this->save_row_id = $this->current_row_data['id'];
        } else {
            $this->save_row_id = '';
        }

        foreach ($this->headings as $name) {
            $cell_no++;
            // First row gets a link.
            if (empty($this->options['show_type'])) {
                $this->options['show_type'] = 'none';
            }

            if ($cell_no == 1 && $this->options['show_type'] != 'none') {
                // show_type / scope_override
                if ($this->options['show_type'] == 'slider') {
                    $put = '<a href="null.php" onclick="return load_page(\'' . $this->options['scope_override'] . '\',\'view\',\'' . $row_id . '\');">' . $this->get_cell_content($name) . '</a>';
                }
                else if ($this->options['show_type'] == 'popup_small') {
                    $put = '<a href="null.php" onclick="return popup(\'' . $this->options['scope_override'] . '\',\'id=' . $row_id . $this->add_qs . '\');">' . $this->get_cell_content($name) . '</a>';
                }
                else {
                    if (empty($this->options['scope_override'])) {
                        if ($this->plugin_table) {
                            $check = PP_PATH . '/custom/plugins/' . $this->scope . '/admin/views/popup/edit.php';
                            if (file_exists($check)) {
                                $scope = $this->scope . '-edit';
                                $link = true;
                            }
                            else {
                                $scope = '';
                                $link = false;
                            }
                        }
                        else {
                            $scope = $this->scope;
                            $link = true;
                        }
                    }
                    else {
                        $scope = $this->options['scope_override'];
                        $link = true;
                    }
                    if ($link) {
                        $put = '<a href="null.php" onclick="return popup(\'' . $scope . '\',\'id=' . $row_id . $this->add_qs . '\',\'1\');">' . $this->get_cell_content($name) . '</a>';
                    }
                    else {
                        $put = $this->get_cell_content($name, $cell_no);
                    }
                }
            }
            else {
                $put = $this->get_cell_content($name, $cell_no);
            }
            // Add cell
            $cell .= '<td id="' . $data['id'] . '-' . $name . '">' . $put . '</td>' . "\n";
        }
        if (empty($this->options['skip_delete'])) {

            $width = '24px';
            if (! empty($this->options['checkin']) ||  ! empty($this->options['duplicate']) ||  ! empty($this->options['complete'])) {
                $width = "52px";
            }
            $cell .= "<td class=\"options\" style=\"width:$width;\">";
            if ( ! empty($this->options['duplicate'])) {
                $cell .= "<a href=\"return_null.php\" onclick=\"return command('duplicate-" . $this->scope . "','" . $data['id'] . "');\" style=\"margin-right:2px;\"><img src=\"imgs/icon-duplicate.png\" width=\"16\" height=\"16\" border=\"0\" class=\"icon\" alt=\"Duplicate\" title=\"Duplicate\" /></a>";
            }
            if ( ! empty($this->options['complete'])) {
                if ($data['deadline'] != '1920-01-01 00:01:01') {
                    $cell .= "<a href=\"return_null.php\" onclick=\"return json_add('note_complete','" . $data['id'] . "','1','skip','complete=1');\" style=\"margin-right:2px;\"><img src=\"imgs/icon-complete.png\" width=\"16\" height=\"16\" border=\"0\" class=\"icon\" alt=\"Mark Complete\" title=\"Mark Complete\" /></a>";
                }
            }
            if ( ! empty($this->options['checkin'])) {
                if ($data['arrived'] != '1') {
                    $cell .= "<a href=\"return_null.php\" onclick=\"return json_add('checkin','" . $data['id'] . "','1','skip', '');\" style=\"margin-right:2px;\"><img src=\"imgs/icon-complete.png\" width=\"16\" height=\"16\" border=\"0\" class=\"icon\" alt=\"Check In\" title=\"Check In\" /></a>";
                }
            }

            if ($this->plugin_table) {
                $putT = $this->scope;
            }
            else {
                $putT = $this->scope_table;
            }

            $cell .= "<a href=\"return_null.php\" onclick=\"return delete_item('" . $putT . "','" . $data['id'] . "');\"><img src=\"imgs/icon-delete.png\" width=\"16\" height=\"16\" border=\"0\" class=\"icon\" alt=\"Delete\" title=\"Delete\" /></a>";
            $cell .= "</td>" . "\n";
        }
        if ($skip_tr != '1') {
            $cell .= '</tr>' . "\n";
        }

        return $cell;

    }

    /**
     * Special considerations for cell content.
     *
     * @param $name
     *
     * @return bool|string
     */
    function get_cell_content($name, $cell_number = 0)
    {
        $skip_date_check = false;

        // ----------------------------
        //   Prepare some special
        //   considerations for
        //   certain table cells.
        if ($this->scope_table == 'ppSD_cart_sessions') {
            if ($name == 'status') {
                if ($this->current_row_data[$name] == '1') {
                    $this->current_row_data[$name] = 'Paid';
                }
                else if ($this->current_row_data[$name] == '2') {
                    $this->current_row_data[$name] = 'Pending Payment';
                }
                else if ($this->current_row_data[$name] == '0') {
                    $this->current_row_data[$name] = 'Unfinished';
                }
                else if ($this->current_row_data[$name] == '3') {
                    $this->current_row_data[$name] = 'Partially Refunded';
                }
                else if ($this->current_row_data[$name] == '4') {
                    $this->current_row_data[$name] = 'Fully Refunded';
                }
                else if ($this->current_row_data[$name] == '9') {
                    $this->current_row_data[$name] = 'Rejected';
                }
            }
            else if ($name == 'payment_gateway') {
                $this->current_row_data[$name] = str_replace('gw_', '', $this->current_row_data[$name]);
                $this->current_row_data[$name] = ucwords(str_replace('_', ' ', $this->current_row_data[$name]));
            }
            else if ($name == 'shipped') {
                if ($this->current_row_data[$name] == '1') {
                    if ($this->current_row_data['trackable'] == '1') {
                        $cart = new cart();
                        $tracking_link = $cart->tracking_link($this->current_row_data['shipping_number'],
                            $this->current_row_data['shipping_provider']);
                        $this->current_row_data[$name] = $tracking_link;
                    }
                    else {
                        $this->current_row_data[$name] = 'Shipped';
                    }
                }
                else if ($this->current_row_data['shipping_rule'] != '0') {
                    $this->current_row_data[$name] = 'Has Not Shipped';
                }
                else {
                    $this->current_row_data[$name] = '';
                }
            }
        }
        else if ($this->scope_table == 'ppSD_contacts') {
            if (in_array('profile_pic', $this->headings) && $cell_number == 2) {
                $this->current_row_data[$name] = '<a href="null.php" onclick="return load_page(\'contact\',\'view\',\'' . $this->current_row_data['id'] . '\');">' . $this->current_row_data[$name] . '</a>';
            }

            if ($name == 'next_action') {
                $skip_date_check = true;
                $this->current_row_data[$name] = format_date($this->current_row_data['next_action']) . " <a href=\"return_null.php\" onclick=\"return json_add('extend_next_action','" . $this->current_row_data['id'] . "','1','skip','type=contact');\" style=\"margin-right:2px;\"><img src=\"imgs/icon-delay-solid-on.png\" width=\"16\" height=\"16\" border=\"0\" class=\"iconR\" alt=\"Extend Next Action\" title=\"Extend Next Action\" /></a>";
            }
        }
        else if ($this->scope_table == 'ppSD_logins') {
            if ($name == 'member_id') {
                $user = new user;
                $username = $user->get_username($this->current_row_data[$name]);
                $this->current_row_data[$name] = "<a href=\"null.php\" onclick=\"return load_page('member','view','" . $this->current_row_data[$name] . "');\">" . $username . "</a>";
            }
            else if ($name == 'type') {
                if ($this->current_row_data[$name] == '2') {
                    $this->current_row_data[$name] = 'In Person';
                } else {
                    $this->current_row_data[$name] = 'Online';
                }
            }
            else if ($this->current_row_data['status'] != '1') {
                return 'Rejected';
            }
        }

        else if ($this->scope_table == 'ppSD_events') {
            if ($name == 'total_rsvps') {
                $event = new event;
                $this->current_row_data[$name] = $event->get_total_rsvps($this->current_row_data['id']);
            }
            else if ($name == 'name') {
                if ($this->current_row_data['status'] == '2') {
                    $this->current_row_data[$name] .= '<span class="inline_weak">Canceled</span>';
                }
            }
        }
        else if ($this->scope_table == 'ppSD_campaign_unsubscribe' || $this->scope_table == 'ppSD_campaign_subscriptions') {
            if ($name == 'user_id') {
                if ($this->current_row_data['user_type'] == 'contact') {
                    $user = new contact;
                    $username = $user->get_name($this->current_row_data[$name]);
                    $this->current_row_data[$name] = "<a href=\"null.php\" onclick=\"return popup('contact_view','id=" . $this->current_row_data[$name] . "');\">" . $username . "</a>";
                }
                else {
                    $user = new user;
                    $username = $user->get_username($this->current_row_data[$name]);
                    $this->current_row_data[$name] = "<a href=\"null.php\" onclick=\"return popup('member_view','id=" . $this->current_row_data[$name] . "');\">" . $username . "</a>";
                }
            }
            else if ($name == 'active') {
                if ($this->current_row_data['active'] == '1') {
                    $this->current_row_data[$name] = "Active";
                }
                else {
                    $this->current_row_data[$name] = "Pending";
                }
            }
            else if ($name == 'active') {
                if ($this->current_row_data[$name] == '1') {
                    $this->current_row_data[$name] = 'Active';
                }
                else {
                    $this->current_row_data[$name] = 'Pending Opt-in';
                }
            }
            else if ($name == 'subscribed_by') {
                if ($this->current_row_data[$name] == 'user') {
                    $this->current_row_data[$name] = 'By End-User';
                }
                else if ($this->current_row_data[$name] == 'employee') {
                    $this->current_row_data[$name] = 'By Staff Member';
                }
                else if ($this->current_row_data[$name] == 'criteria') {
                    $this->current_row_data[$name] = 'Criteria Subscription';
                }
                else if ($this->current_row_data[$name] == 'condition') {
                    $this->current_row_data[$name] = 'Form Condition Met';
                }
                else {
                    $this->current_row_data[$name] = '';
                }
            }

        }


        else if ($this->scope_table == 'ppSD_staff') {
            if ($name == 'status') {
                if ($this->current_row_data[$name] == '1') {
                    $this->current_row_data[$name] = 'Active';
                }
                else {
                    $this->current_row_data[$name] = 'Inactive';
                }
            }
        }

        else if ($this->scope_table == 'ppSD_tracking_activity') {
            if ($name == 'type') {
                if ($this->current_row_data[$name] == 'rsvp') {
                    $this->current_row_data[$name] = '<a href="null.php" onclick="return popup(\'rsvp_view\',\'id=' . $this->current_row_data['act_id'] . '\');">Event Registration</a>';
                }
                else if ($this->current_row_data[$name] == 'member') {
                    $this->current_row_data[$name] = '<a href="null.php" onclick="return load_page(\'member\',\'view\',\'id=' . $this->current_row_data['act_id'] . '\');">Member Registered</a>';
                }
                else if ($this->current_row_data[$name] == 'contact') {
                    $this->current_row_data[$name] = '<a href="null.php" onclick="return load_page(\'contact\',\'view\',\'id=' . $this->current_row_data['act_id'] . '\');">Contact Created</a>';
                }
                else if ($this->current_row_data[$name] == 'invoice') {
                    $this->current_row_data[$name] = '<a href="null.php" onclick="return load_page(\'invoice\',\'view\',\'id=' . $this->current_row_data['act_id'] . '\');">Invoice Created</a>';
                }
                else if ($this->current_row_data[$name] == 'order') {
                    $this->current_row_data[$name] = '<a href="null.php" onclick="return load_page(\'transaction\',\'view\',\'id=' . $this->current_row_data['act_id'] . '\');">Order Placed</a>';
                }
                else {
                    $this->current_row_data[$name] = ucwords($this->current_row_data['type']);
                }
            }
        }

        else if ($this->scope_table == 'ppSD_campaign_items') {
            if ($name == 'timeframe') {
                if ($this->current_row_data['when_date'] != '1920-01-01 00:01:01') {
                    $this->current_row_data[$name] = $this->format_date($this->current_row_data['when_date']);
                }
                else {
                    $this->current_row_data[$name] = $this->format_timeframe($this->current_row_data['when_timeframe']);
                }
            }
        }

        else if ($this->scope_table == 'ppSD_fields') {
            if ($name == 'display_name') {
                if ($this->current_row_data['encrypted'] == '1') {
                    $icon = '<img src="imgs/icon-encoded.png" width="16" height="16" border="0" alt="Sensitive Information" title="Sensitive Information" class="icon" />';
                }
                else {
                    $icon = '';
                }
                $this->current_row_data[$name] = $icon . $this->current_row_data[$name];
            }
        }
        /*
        else if ($this->scope_table == 'ppSD_campaign_unsubscribe') {
            if ($name == 'by') {
                if ($this->current_row_data[$name] == 'user') {
                    $this->current_row_data[$name] = 'By End-User';
                } else if ($this->current_row_data[$name] == 'staff') {
                    $this->current_row_data[$name] = 'By Staff Member';
                } else {
                    $this->current_row_data[$name] = 'Kill Condition Met';
                }
            }
        }
        */
        else if ($this->scope_table == 'ppSD_subscriptions') {
            if ($name == 'status') {
                if ($this->current_row_data[$name] == '1') {
                    $this->current_row_data[$name] = 'Active';
                }
                else if ($this->current_row_data[$name] == '2') {
                    $this->current_row_data[$name] = 'Canceled';
                }

                if (empty($this->current_row_data['card_id'])) {
                    $this->current_row_data[$name] .= ' <img src="imgs/icon-credit-card-broken.png" width="16" height="16" border="0" class="iconR" title="No credit card on file." alt="No credit card on file" />';
                }

                if (! empty($this->current_row_data['spawned_invoice'])) {
                    $this->current_row_data[$name] .= ' <a href="null.php" onclick="return load_page(\'invoice\',\'view\',\'' . $this->current_row_data['spawned_invoice'] . '\');"><img src="imgs/icon-attention.png" width="16" height="16" border="0" class="iconR" title="Subscription failed and is waiting payment on an invoice." alt="Subscription failed and is waiting payment on an invoice." /></a>';
                }
            }
            else if ($name == 'product') {
                $id = $this->current_row_data[$name];
                $cart = new cart;
                $name = $cart->get_product_name($this->current_row_data[$name]);
                $this->current_row_data[$name] = '<a href="null.php" onclick="return load_page(\'product\',\'view\',\'' . $id . '\');">' . $name . '</a>';
            }
            /*
            else if ($name == 'price') {
                $this->current_row_data[$name] = place_currency($this->current_row_data[$name]);
            }
            */
        }
        else if ($this->scope_table == 'ppSD_widgets') {
            if ($name == 'active') {
                if ($this->current_row_data[$name] == '1') {
                    $this->current_row_data[$name] = 'Active';
                }
                else if ($this->current_row_data[$name] == '0') {
                    $this->current_row_data[$name] = 'Inactive';
                }
            }
            else if ($name == 'type') {
                if ($this->current_row_data[$name] == 'menu') {
                    $this->current_row_data[$name] = 'Menu';
                }
                else if ($this->current_row_data[$name] == 'code') {
                    $this->current_row_data[$name] = 'PHP Code';
                }
                else if ($this->current_row_data[$name] == 'html') {
                    $this->current_row_data[$name] = 'HTML Block';
                }
                else if ($this->current_row_data[$name] == 'plugin') {
                    $this->current_row_data[$name] = 'Plugin';
                }
            }
        }
        else if ($this->scope_table == 'ppSD_invoices') {
            if ($name == 'status') {
                if ($this->current_row_data[$name] == '0') {
                    $this->current_row_data[$name] = 'Unpaid';
                }
                else if ($this->current_row_data[$name] == '1') {
                    $this->current_row_data[$name] = 'Paid';
                }
                else if ($this->current_row_data[$name] == '2') {
                    $this->current_row_data[$name] = 'Partially Paid';
                }
                else if ($this->current_row_data[$name] == '3') {
                    $this->current_row_data[$name] = 'Overdue';
                }
                else if ($this->current_row_data[$name] == '4') {
                    $this->current_row_data[$name] = 'Unresponsive Client';
                }
                else if ($this->current_row_data[$name] == '5') {
                    $this->current_row_data[$name] = 'Dead';
                }
                else if ($this->current_row_data[$name] == '9') {
                    $this->current_row_data[$name] = 'Empty';
                }
            }
            /*
            else if ($name == 'subtotal') {
                $this->current_row_data[$name] = place_currency($this->current_row_data[$name]);
            }
            else if ($name == 'due') {
                $this->current_row_data[$name] = place_currency($this->current_row_data[$name]);
            }
            else if ($name == 'paid') {
                $this->current_row_data[$name] = place_currency($this->current_row_data[$name]);
            }
            */
        }
        else if ($this->scope_table == 'ppSD_history') {
            $history = new history('', '', '', '', '', '', '');
            if ($name == 'when') {
                // method actid date
                $method = $history->get_method_data($this->current_row_data['method'],
                $this->current_row_data['act_id'], $this->current_row_data['date'],
                $this->current_row_data['owner'], $this->current_row_data['type'],
                $this->current_row_data['user_id'], $this->current_row_data);
                $this->current_row_data['when'] = $method['difference'];
                $this->current_row_data['details'] = $method['icon'] . $method['title'];
            }
        }
        else if ($this->scope_table == 'ppSD_notes') {
            if ($name == 'label') {
                $admin = new admin;
                $label = $admin->get_note_label($this->current_row_data[$name]);
                $this->current_row_data[$name] = $label['formatted'];
            }
            /*
            else if ($name == 'name') {
                //$this->current_row_data[$name] .=
            }
            */
            else if ($name == 'public') {
                if ($this->current_row_data[$name] == '1') {
                    $this->current_row_data[$name] = 'Public';
                }
                else if ($this->current_row_data[$name] == '2') {
                    $this->current_row_data[$name] = 'Broadcast';
                }
                else if ($this->current_row_data[$name] == '3') {
                    $this->current_row_data[$name] = 'For Employee';
                }
                else {
                    $this->current_row_data[$name] = 'Private';
                }
            }
            else if ($name == 'for' || $name == 'added_by') {
                if ( ! empty($this->current_row_data[$name])) {
                    $admin = new admin;
                    $emp = $admin->get_employee('', $this->current_row_data[$name]);
                    $this->current_row_data[$name] = $emp['username'];
                }
            }
        }
        else if ($this->scope_table == 'ppSD_staff') {
            if ($name == 'permission_group') {
                $admin = new admin;
                $perm_group = $admin->permission_group($this->current_row_data[$name]);
                $this->current_row_data[$name] = $perm_group['name'];
            }
        }
        else if ($this->scope_table == 'ppSD_staff') {
            if ($name == 'status') {
                if ($this->current_row_data['status'] != '1') {
                    $this->current_row_data['status'] = 'Inactive';
                }
                else {
                    $this->current_row_data['status'] = 'Active';
                }
            }
        }
        else if ($this->scope_table == 'ppSD_campaigns') {
            if ($name == 'optin_type') {
                if ($this->current_row_data[$name] == 'criteria') {
                    $this->current_row_data[$name] = 'Automated';
                }
                else if ($this->current_row_data[$name] == 'single_optin') {
                    $this->current_row_data[$name] = 'Single Opt-In';
                }
                else {
                    $this->current_row_data[$name] = 'Double Opt-In';
                }
            }
            else if ($name == 'status') {
                if ($this->current_row_data['status'] != '1') {
                    $this->current_row_data['status'] = 'Inactive';
                }
                else {
                    $this->current_row_data['status'] = 'Active';
                }
            }
            else if ($name == 'subscribers') {
                $campaign = new campaign($this->current_row_data['id']);
                $subs = $campaign->total_active_subscribers();
                $this->current_row_data[$name] = $subs;
            }
        }
        else if ($this->scope_table == 'ppSD_content_access') {
            if ($name == 'expires') {
                $format_expires = format_date($this->current_row_data['expires']);
                $difference = date_difference($this->current_row_data['expires']);
                $this->current_row_data[$name] = $format_expires . ' (' . $difference . ')';
            }
            else if ($name == 'content') {
                $content = new content;
                $data = $content->get_content($this->current_row_data['content_id'], '1');
                $this->current_row_data[$name] = $data['name'];
            }
        }
        else if ($this->scope_table == 'ppSD_content') {
            if ($name == 'url') {

                // Secure?
                // ! empty($cdata['template']) &&
                if ($this->current_row_data['secure'] == '1' || $this->current_row_data['type'] == 'folder') {
                    $icon = '<img src="imgs/icon-private.png" width="16" height="16" class="icon" border="0" alt="Members Only" title="Members Only" />';
                }
                else {
                    $icon = '<img src="imgs/icon-public.png" width="16" height="16" class="icon" border="0" alt="Public Access" title="Public Access" />';
                }

                // $this->current_row_data[$name] = str_replace('%pp%',PP_URL,$this->current_row_data[$name]);
                if ($this->current_row_data['type'] == 'page') {

                    // Get data and the link
                    $content = new content;
                    $cdata = $content->get_content($this->current_row_data['id'], '1');
                    $fulllink = $content->build_permalink($cdata['permalink'], $cdata['section']);
                    $this->current_row_data['url'] = '<a href="' . $fulllink . '" target="_blank">' . $icon . $fulllink . '</a>';

                }

                else if ($this->current_row_data['type'] == 'folder' || $this->current_row_data['type'] == 'redirect') {
                    $this->current_row_data['url'] = '<a href="' . $this->current_row_data['url'] . '" target="_blank">' . $icon . $this->current_row_data['url'] . '</a>';
                }

                else {
                    $this->current_row_data[$name] = $icon . str_replace('%pp%', PP_URL, $this->current_row_data[$name]);
                }

            }
            else if ($name == 'name') {
                if ($this->current_row_data['type'] == 'page') {
                    $this->current_row_data['name'] = '<a href="returnnull.php" onclick="return popup(\'content-add-page\',\'id=' . $this->current_row_data['id'] . '\',\'1\');">' . $this->current_row_data['name'] . '</a>';
                    $this->current_row_data['show_type'] = 'CMS Page';
                }
                else if ($this->current_row_data['type'] == 'section') {
                    $this->current_row_data['name'] = '<a href="returnnull.php" onclick="return popup(\'content-add-section\',\'id=' . $this->current_row_data['id'] . '\',\'1\');">' . $this->current_row_data['name'] . '</a>';
                    $this->current_row_data['show_type'] = 'CMS Section';
                }
                else if ($this->current_row_data['type'] == 'folder') {
                    $this->current_row_data['name'] = '<a href="returnnull.php" onclick="return popup(\'content-add-folder\',\'id=' . $this->current_row_data['id'] . '\',\'1\');">' . $this->current_row_data['name'] . '</a>';
                    $this->current_row_data['show_type'] = 'Secure Folder';
                }
                else if ($this->current_row_data['type'] == 'redirect') {
                    $this->current_row_data['name'] = '<a href="returnnull.php" onclick="return popup(\'content-add-redirect\',\'id=' . $this->current_row_data['id'] . '\',\'1\');">' . $this->current_row_data['name'] . '</a>';
                    $this->current_row_data['show_type'] = 'URL Redirect';
                }
            }
        }
        else if ($this->scope_table == 'ppSD_products') {
            $cart = new cart;
            if ($name == 'type') {
                if ($this->current_row_data[$name] == '1') {
                    $this->current_row_data[$name] = 'Single Purchase';
                }
                else if ($this->current_row_data[$name] == '2') {
                    $this->current_row_data[$name] = 'Subscription';
                }
                else if ($this->current_row_data[$name] == '3') {
                    $this->current_row_data[$name] = 'Trial';
                }
            }
            else if ($name == 'name') {
                $icons = '';
                if ($this->current_row_data['members_only'] == '1') {
                    $icons .= '<img src="imgs/icon-login.png" width="16" height="16" class="icon-right" alt="Members Only" title="Members Only" />';
                }
                if ($this->current_row_data['featured'] == '1') {
                    $icons .= '<img src="imgs/icon-fav-on.png" width="16" height="16" class="icon-right" alt="Featured Product" title="Featured Product" />';
                }
                if ($this->current_row_data['hide'] == '2') {
                    $icons .= '<img src="imgs/icon-hidden.png" width="16" height="16" class="icon-right" alt="Hidden in Shop" title="Hidden in Shop" />';
                }
                if ($this->current_row_data['physical'] == '1') {
                    $icons .= '<img src="imgs/icon-package.png" width="16" height="16" class="icon-right" alt="Physical Product" title="Physical Product" />';
                }
                $this->current_row_data['name'] .= $icons .
                    '<a href="' . PP_URL . '/catalog.php?id=' . $this->current_row_data['id'] . '" target="_blank"><img src="imgs/icon-view.png" class="icon-right" alt="See in catalog" border="0" /></a>' .
                    '<a href="prevent_null.php" onclick="return popup(\'product-edit\',\'id=' . $this->current_row_data['id'] . '\',\'1\');"><img src="imgs/icon-edit-on.png" class="icon-right" alt="Quick Edit" border="0" /></a>';
            }
            else if ($name == 'category') {
                if ( ! empty($this->current_row_data[$name])) {
                    $cate = $cart->get_category($this->current_row_data[$name]);
                    $this->current_row_data[$name] = $cate['name'];
                }
            }
            else if ($name == 'physical') {
                if ($this->current_row_data[$name] == '1') {
                    $this->current_row_data[$name] = 'Physical w/o Shipping';
                }
                else if ($this->current_row_data[$name] == '2') {
                    $this->current_row_data[$name] = 'Physical w/ Shipping';
                }
                else {
                    $this->current_row_data[$name] = 'Digital';
                }
            }
            else if ($name == 'price') {
                $product = $cart->get_product($this->current_row_data['id']);
                $this->current_row_data[$name] = $cart->format_product_price($product);
            }
        }
        else if ($this->scope_table == 'ppSD_cart_categories') {
            $cart = new cart;
            if ($name == 'subcategory') {
                if ( ! empty($this->current_row_data[$name])) {
                    $cate = $cart->get_category($this->current_row_data[$name]);
                    $this->current_row_data[$name] = $cate['name'];
                }
            }
            else if ($name == 'name') {
                $icons = '';
                if ($this->current_row_data['members_only'] == '1') {
                    $icons .= '<img src="imgs/icon-private.png" width="16" height="16" class="icon-right" alt="Members Only" title="Members Only" />';
                }
                $this->current_row_data['name'] .= $icons;
            }
        }
        else if ($this->scope_table == 'ppSD_payment_gateways') {
            if ($name == 'test_mode') {
                if ($this->current_row_data[$name] == '1') {
                    $this->current_row_data[$name] = 'Test Mode';
                }
                else {
                    $this->current_row_data[$name] = 'Live';
                }
            }
            else if ($name == 'fee_percent') {
                $this->current_row_data['fee_percent'] .= '%';
            }
        }
        else if ($this->scope_table == 'ppSD_uploads') {
            if ($name == 'size') {
                if ( ! empty($this->current_row_data['email_id'])) {
                    $path = PP_PATH . "/admin/sd-system/attachments/" . $this->current_row_data['holdname'];
                }
                else {
                    $path = PP_PATH . "/custom/uploads/" . $this->current_row_data['holdname'];
                }
                if (file_exists($path)) {
                    $fsize = format_bytes(filesize($path));
                }
                else {
                    $fsize = 'N/A';
                }
                $this->current_row_data[$name] = $fsize;
            }
            else if ($name == 'label') {
                $this->current_row_data[$name] = '<a href="null.php" onclick="return popup(\'change_label\',\'id=' . $this->current_row_data['id'] . '\');">' . $this->current_row_data[$name] . '</a>';
            }
            else if ($name == 'filename') {
                $this->current_row_data['holdname'] = $this->current_row_data['filename'];
                if ( ! empty($this->current_row_data['name'])) {
                    $show_name = $this->current_row_data['name'];
                }
                else {
                    $show_name = $this->current_row_data['filename'];
                }
                if ( ! empty($this->current_row_data['email_id'])) {
                    $path = PP_PATH . "/admin/sd-system/attachments/" . $this->current_row_data['filename'];
                    $url = PP_URL . "/admin/sd-system/attachments/" . $this->current_row_data['filename'];
                    $type = 'attachment';
                }
                else {
                    $path = PP_PATH . "/custom/uploads/" . $this->current_row_data['filename'];
                    $url = PP_URL . "/custom/uploads/" . $this->current_row_data['filename'];
                    if ($this->current_row_data['label'] == 'profile-picture') {
                        $type = 'profile-picture';
                    }
                    else {
                        $type = '';
                    }
                }
                if (file_exists($path)) {
                    if ($this->current_row_data['cp_only'] == '1') {
                        $icon = '<img src="imgs/icon-private.png" width="16" height="16" border="0" alt="Private: Control Panel Only" title="Private: Control Panel Only" class="icon" />';
                    }
                    else {
                        $icon = '<img src="imgs/icon-public.png" width="16" height="16" border="0" alt="Public" title="Public" class="icon" />';
                    }
                }
                else {
                    $icon = '<img src="imgs/icon-broken-file.png" width="16" height="16" border="0" alt="File Not Found" title="File Not Found" class="icon" />';
                }
                $ext = $this->get_ext($this->current_row_data['filename']);
                if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
                    $this->current_row_data[$name] = '<a href="returnnull.php" onclick="return popup(\'crop_image\',\'id=' . $this->current_row_data['id'] . '&type=' . $type . '&filename=' . $this->current_row_data['filename'] . '\',\'1\');">' . $icon . $show_name . '</a>';
                }
                else {
                    $this->current_row_data[$name] = "<a href=\"$url\" target=\"_blank\">" . $icon . $show_name . '</a>';
                }
            }
        }
        else if ($this->scope_table == 'ppSD_login_announcements') {
            if ($name == 'active') {
                if ($this->current_row_data[$name] == '1') {
                    $timeit = strtotime(current_date());
                    if ($timeit >= strtotime($this->current_row_data['starts'])) {
                        if ($this->current_row_data['ends'] == '1920-01-01 00:01:01') {
                            $this->current_row_data[$name] = 'Active';
                        }
                        else if ($this->current_row_data['ends'] != '1920-01-01 00:01:01' && $timeit <= strtotime($this->current_row_data['ends'])) {
                            $this->current_row_data[$name] = 'Active';
                        }
                        else {
                            $this->current_row_data[$name] = 'Inactive (date range)';
                        }
                    }
                    else {
                        $this->current_row_data[$name] = 'Inactive (date range)';
                    }
                }
                else {
                    $this->current_row_data[$name] = 'Inactive';
                }
            }
        }
        else if ($this->scope_table == 'ppSD_tax_classes') {
            if ($name == 'percent_physical') {
                $this->current_row_data['percent_physical'] .= '%';
            }
            else if ($name == 'percent_digital') {
                $this->current_row_data['percent_digital'] .= '%';
            }
        }
        else if ($this->scope_table == 'ppSD_custom_actions') {


            if ($name == 'when') {
                if ($this->current_row_data[$name] == '1') {
                    $this->current_row_data[$name] = 'Before Task';
                }
                else {
                    $this->current_row_data[$name] = 'After Task';
                }
            }
            else if ($name == 'type') {
                if ($this->current_row_data[$name] == '1') {
                    $this->current_row_data[$name] = 'PHP Code Execution';
                }
                else if ($this->current_row_data[$name] == '2') {
                    $this->current_row_data[$name] = 'E-Mail Dispatcher';
                }
                else if ($this->current_row_data[$name] == '3') {
                    $this->current_row_data[$name] = 'MySQL Command Execution';
                }
                else if ($this->current_row_data[$name] == '5') {
                    $this->current_row_data[$name] = 'Outside Connection';
                }
                else if ($this->current_row_data[$name] == '6') {
                    $this->current_row_data[$name] = 'SMS Dispatcher';
                }
            }
            else if ($name == 'active') {
                if ($this->current_row_data[$name] == '1') {
                    $this->current_row_data[$name] = 'Active';
                }
                else {
                    $this->current_row_data[$name] = 'Inactive';
                }
            }
        }
        else if ($this->scope_table == 'ppSD_calendars') {
            if ($name == 'style') {
                if ($this->current_row_data[$name] == '2') {
                    $this->current_row_data[$name] = 'Long-List View';
                }
                else if ($this->current_row_data[$name] == '3') {
                    $this->current_row_data[$name] = 'Cloud View';
                }
                else {
                    $this->current_row_data[$name] = 'Standard Calendar View';
                }
            }
            else if ($name == 'members_only') {
                if ($this->current_row_data[$name] != '1') {
                    $this->current_row_data[$name] = 'No (Public Calendar)';
                }
                else {
                    $this->current_row_data[$name] = 'Yes';
                }
            }
            else if ($name == 'id') {
                $this->current_row_data[$name] = $this->current_row_data[$name] . '<a href="' . PP_URL . '/calendar.php?id=' . $this->current_row_data['id'] . '" target="_blank"><img src="imgs/icon-calendar.png" class="icon-right" /></a>';
            }
        }

        else if ($this->scope_table == 'ppSD_link_tracking') {
            if ($name == 'link') {
                $len = strlen($this->current_row_data[$name]);
                if ($len < 80) {
                    $show = $this->current_row_data[$name];
                }
                else {
                    $show = substr($this->current_row_data[$name], 0, 50) . '...';
                }
                $this->current_row_data[$name] = "<a href=\"" . $this->current_row_data[$name] . "\" target=\"_blank\">" . $show . "</a>";
            }
        }

        else if ($this->scope_table == 'ppSD_shipping_rules') {
            if ($name == 'type') {
                if ($this->current_row_data[$name] == 'weight') {
                    $this->current_row_data[$name] = 'Weight-based';
                }
                else if ($this->current_row_data[$name] == 'region') {
                    $this->current_row_data[$name] = 'Region-based';
                }
                else if ($this->current_row_data[$name] == 'qty') {
                    $this->current_row_data[$name] = 'Quantity-based';
                }
                else if ($this->current_row_data[$name] == 'total') {
                    $this->current_row_data[$name] = 'Total-based';
                }
                else if ($this->current_row_data[$name] == 'product') {
                    $this->current_row_data[$name] = 'Product-based';
                }
                else if ($this->current_row_data[$name] == 'flat') {
                    $this->current_row_data[$name] = 'Flat Rate Option';
                }
            }
        }
        else if ($this->scope_table == 'ppSD_forms') {
            if ($name == 'reg_status') {
                if ($this->current_row_data['reg_status'] == 'P') {
                    $this->current_row_data['reg_status'] = 'E-Mail Confirmation';
                }
                else if ($this->current_row_data['reg_status'] == 'Y') {
                    $this->current_row_data['reg_status'] = 'Approval Required';
                }
                else {
                    $this->current_row_data['reg_status'] = 'Instant Activation';
                }
            }
            else if ($name == 'type') {
                if ($this->current_row_data['type'] == 'admin_cp') {
                    $this->current_row_data['type'] = 'Admin Control Panel';
                }
                else if ($this->current_row_data['type'] == 'payment_form') {
                    $this->current_row_data['type'] = 'Payment Form';
                }
                else if ($this->current_row_data['type'] == 'register-free') {
                    $this->current_row_data['type'] = 'Free Registration';
                }
                else if ($this->current_row_data['type'] == 'register-paid') {
                    $this->current_row_data['type'] = 'Paid Registration';
                }
                else if ($this->current_row_data['type'] == 'dependency') {
                    $this->current_row_data['type'] = 'Dependency Form';
                }
                else if ($this->current_row_data['type'] == 'contact') {
                    $this->current_row_data['type'] = 'Contact Form';
                }
                else if ($this->current_row_data['type'] == 'event') {
                    $this->current_row_data['type'] = 'Event Registration';
                }
                else if ($this->current_row_data['type'] == 'update') {
                    $this->current_row_data['type'] = 'Member Update';
                }
            }
            else if ($name == 'disabled') {
                if ($this->current_row_data['disabled'] == '1') {
                    $this->current_row_data['disabled'] = 'Disabled';
                }
                else {
                    $this->current_row_data['disabled'] = '<a href="' . PP_URL . '/register.php?action=reset&id=' . $this->current_row_data['id'] . '" target="_blank">Live</a>';
                }
            }
            else if ($name == 'code_required') {
                if ($this->current_row_data['code_required'] == '1') {
                    $this->current_row_data['code_required'] = '<a href="returnnull.php" onclick="return popup(\'form_codes\',\'form_id=' . $this->current_row_data['id'] . '\');">Manage Codes</a>';
                }
                else {
                    $this->current_row_data['code_required'] = '';
                }
            }
        }
        else if ($this->scope_table == 'ppSD_email_scheduled') {
            if ($name == 'user_id') {
                if ($this->current_row_data['user_type'] == 'member') {
                    $this->current_row_data['user_id'] = '<a href="returnnull.php" onclick="return load_page(\'member\',\'view\',\'' . $this->current_row_data['user_id'] . '\');">' . $this->current_row_data['user_id'] . '</a>';
                }
                else if ($this->current_row_data['user_type'] == 'contact') {
                    $this->current_row_data['user_id'] = '<a href="returnnull.php" onclick="return load_page(\'contact\',\'view\',\'' . $this->current_row_data['user_id'] . '\');">' . $this->current_row_data['user_id'] . '</a>';
                }
            }
        }
        else if ($this->scope_table == 'ppSD_lead_conversion') {
            if ($name == 'percent_change') {
                $this->current_row_data['percent_change'] .= '%';
            }
            else if ($name == 'user_id') {
                $user = new user;
                $username = $user->get_username($this->current_row_data[$name]);
                $this->current_row_data['user_id'] = '<a href="returnnull.php" onclick="return load_page(\'member\',\'view\',\'' . $this->current_row_data['user_id'] . '\');">' . $username . '</a>';
            }
            else if ($name == 'contact_id') {
                $user = new contact;
                $username = $user->get_name($this->current_row_data[$name]);
                $this->current_row_data['contact_id'] = '<a href="returnnull.php" onclick="return load_page(\'contact\',\'view\',\'' . $this->current_row_data['contact_id'] . '\');">' . $username . '</a>';
            }
        }
        else if ($this->scope_table == 'ppSD_cart_coupon_codes') {
            if ($name == 'percent_off') {
                $this->current_row_data['percent_off'] .= '%';
            }
        }
        else if ($this->scope_table == 'ppSD_saved_emails') {
            if ($name == 'user_id') {
                if ($this->current_row_data['user_type'] == 'member') {
                    $user = new user;
                    $username = $user->get_username($this->current_row_data[$name]);
                    $this->current_row_data['user_id'] = '<a href="returnnull.php" onclick="return load_page(\'member\',\'view\',\'' . $this->current_row_data['user_id'] . '\');">' . $username . '</a>';
                }
                else if ($this->current_row_data['user_type'] == 'contact') {
                    $user = new contact;
                    $username = $user->get_name($this->current_row_data[$name]);
                    $this->current_row_data['user_id'] = '<a href="returnnull.php" onclick="return load_page(\'contact\',\'view\',\'' . $this->current_row_data['user_id'] . '\');">' . $username . '</a>';
                }
            }
            else if ($name == 'fail') {
                if ($this->current_row_data['fail'] == '1') {
                    $this->current_row_data['fail'] = 'Failed';
                }
                else if ($this->current_row_data['fail'] == '2') {
                    $this->current_row_data['fail'] = 'Bounced';
                }
                else {
                    $this->current_row_data['fail'] = 'Success';
                }
            }
        } /*
        else if ($this->scope_table == 'ppSD_cart_sessions') {
            if ($name == 'total') {
                $this->current_row_data[$name] = place_currency($this->current_row_data[$name]);
            }
        }
        */
        else if ($this->scope_table == 'ppSD_sources') {
            if ($name == 'redirect') {
                $this->current_row_data['redirect'] = '<a target="_blank" href="' . $this->current_row_data['redirect'] . '">' . $this->current_row_data['redirect'] . '</a>';
            }
            else if ($name == 'redirect_b') {
                $this->current_row_data['redirect_b'] = '<a target="_blank" href="' . $this->current_row_data['redirect_b'] . '">' . $this->current_row_data['redirect_b'] . '</a>';
            }

            // Need to do this because special fields has special considerations
            // for source column in member/contact tables
            $this->current_row_data['name'] = $this->current_row_data['source'];
            $this->current_row_data['name'] .= " <a href=\"index.php?l=source_tracking&filters[]=" . $this->current_row_data['id'] . "||source_id||eq||\"><img src=\"imgs/icon-tracking.png\" width=\"16\" height=\"16\" border=\"0\" class=\"icon\" alt=\"Tracking\" title=\"Tracking\" /></a>";
            $this->current_row_data['name'] .= " <a href=\"index.php?l=source_report&id=" . $this->current_row_data['id'] . "\"><img src=\"imgs/icon-stats-on.png\" width=\"16\" height=\"16\" border=\"0\" class=\"icon\" alt=\"Report\" title=\"Report\" /></a>";
        }
        else if ($this->scope_table == 'ppSD_source_tracking') {
            if ($name == 'source_id') {
                $source = new source;
                $this->current_row_data['source_id'] = $source->get_source_name($this->current_row_data['source_id']) . ' (' . $this->current_row_data['source_id'] . ')';
            }
            else if ($name == 'referrer') {
                if (! empty($this->current_row_data['referrer'])) {
                    $this->current_row_data['referrer'] = '<a href="' . $this->current_row_data['referrer'] . '" target="_blank">' . $this->current_row_data['referrer'] . '</a>';
                }
            }
            else if ($name == 'user_id') {
                if (! empty($this->current_row_data['user_id'])) {
                    if ($this->current_row_data['user_type'] == 'member') {
                        $user = new user;
                        $this->current_row_data['user_id'] = '<a href="returnnull.php" onclick="return load_page(\'member\',\'view\',\'' . $this->current_row_data['user_id'] . '\');">' . $user->get_username($this->current_row_data['user_id']) . '</a>';
                    }
                    else if ($this->current_row_data['user_type'] == 'contact') {
                        $contact = new contact;
                        $this->current_row_data['user_id'] = '<a href="returnnull.php" onclick="return load_page(\'contact\',\'view\',\'' . $this->current_row_data['user_id'] . '\');">' . $contact->get_name($this->current_row_data['user_id']) . '</a>';
                    }
                    else {

                    }
                }
            }
        }
        else if ($this->scope_table == 'ppSD_error_codes') {
            $this->current_row_data['id'] = $this->current_row_data['code'];
        }
        // ----------------------------
        //   Fill out the row.
        if (empty($this->current_row_data[$name]) && $name != 'profile_pic') {
            return '<span class="weak">N/A</span>';
        }
        else {
            if ( ! empty($this->current_row_data[$name])) {
                $date = check_date($this->current_row_data[$name]);
                $cell_content = $this->current_row_data[$name];
            }
            else {
                $date = '0';
                $cell_content = '';
            }
            if ($skip_date_check) {
                return $cell_content;
            }
            if ($date == '1') {
                if ($cell_content == '1920-01-01 00:01:01' || $cell_content == '1920-01-01') {
                    return '<span class="weak">N/A</span>';
                }
                else {
                    return format_date($cell_content);
                }
            }
            else if (in_array($name, $this->price_fields()) && $this->scope_table != 'ppSD_products') {
                return place_currency($cell_content);
            } /*
            else if ($name == 'user_id') {
                if ($this->current_row_data['user_type'] == 'member') {
                    return '<a href="returnnull.php" onclick="return popup(\'member_view\',\'id=' . $this->current_row_data[$name] . '\');">' . $this->current_row_data[$name] . '</a>';
                }
                else if ($this->current_row_data['user_type'] == 'contact') {
                    return '<a href="returnnull.php" onclick="return popup(\'contact_view\',\'id=' . $this->current_row_data[$name] . '\');">' . $this->current_row_data[$name] . '</a>';
                }
                else {
                    return '<a href="returnnull.php" onclick="return popup(\'rsvp_view\',\'id=' . $this->current_row_data[$name] . '\');">' . $this->current_row_data[$name] . '</a>';
                }
            }*/

            else {
                return $this->special_fields->process($name, $cell_content);
            }
        }

    }

    function price_fields()
    {
        return array(
            'price',
            'cost',
            'total',
            'gateway_fees',
            'fee_flat',
            'subtotal',
            'shipping',
            'tax',
            'tax_rate',
            'savings',
            'refunds',
            'invoice_due',
            'value',
            'invoice_paid',
            'credits',
            'paid',
            'due',
            'new_balance',
            'dollars_off',
            'actual_value',
            'estimated_value',
        );

    }

    function check_custom_class()
    {
        if ($this->scope_table == 'ppSD_contacts') {
            if ($this->current_row_data['status'] == '2') {
                return ' converted';
            }
            else if ($this->current_row_data['status'] == '3') {
                return ' dead';
            }
            else {
                if (current_date() >= $this->current_row_data['next_action']) {
                    return ' overdue';
                }
            }
        }
        else if ($this->scope_table == 'ppSD_event_rsvps') {
            if ($this->current_row_data['arrived'] == '1') {
                return ' converted';
            }
        }
        else if ($this->scope_table == 'ppSD_source_tracking') {
            if ($this->current_row_data['converted'] == '1') {
                return ' converted';
            }
        }
        else if ($this->scope_table == 'ppSD_products') {
            if ($this->current_row_data['hide'] == '1') {
                return ' dead';
            }
        }
        else if ($this->scope_table == 'ppSD_custom_actions') {
            if ($this->current_row_data['active'] != '1') {
                return ' dead';
            }
        }
        else if ($this->scope_table == 'ppSD_cart_categories') {
            if ($this->current_row_data['hide'] == '1') {
                return ' dead';
            }
        }
        else if ($this->scope_table == 'ppSD_campaigns') {
            if ($this->current_row_data['status'] != '1') {
                return ' canceled';
            }
        }
        else if ($this->scope_table == 'ppSD_email_trackback') {
            if ($this->current_row_data['status'] == '1') {
                return ' converted';
            }
        }
        else if ($this->scope_table == 'ppSD_invoices') {
            if ($this->current_row_data['status'] == '3' || $this->current_row_data['status'] == '4') {
                return ' overdue';
            }
            else if ($this->current_row_data['status'] == '5') {
                return ' dead';
            }
            else if ($this->current_row_data['status'] == '1') {
                return ' converted';
            }
        }
        else if ($this->scope_table == 'ppSD_forms') {
            $this->current_row_data['id'] = str_replace('register-', '', $this->current_row_data['id']);
        }
        else if ($this->scope_table == 'ppSD_notes') {
            if ($this->current_row_data['complete'] == '1') {
                return ' completed';
            }
            else if ($this->current_row_data['deadline'] != '1920-01-01 00:01:01' && $this->current_row_data['deadline'] <= current_date()) {
                return ' overdue';
            }
        }
        else if ($this->scope_table == 'ppSD_cart_sessions') {
            if ($this->current_row_data['status'] == '9') {
                return ' dead';
            }
            else if ($this->current_row_data['status'] == '3' || $this->current_row_data['status'] == '4') {
                return ' converted';
            }
        }
        else if ($this->scope_table == 'ppSD_events') {
            if ($this->current_row_data['status'] == '0') {
                return ' dead';
            }
            if ($this->current_row_data['status'] == '2') {
                return ' canceled';
            }
        }
        else if ($this->scope_table == 'ppSD_saved_emails') {
            if ($this->current_row_data['fail'] == '1') {
                return ' dead';
            }
            else if ($this->current_row_data['fail'] == '2') {
                return ' overdue';
            }
        }
        else if ($this->scope_table == 'ppSD_subscriptions') {
            if ($this->current_row_data['status'] == '2') {
                return ' dead';
            }
        }
        else if ($this->scope_table == 'ppSD_widgets') {
            if ($this->current_row_data['active'] != '1') {
                return ' dead';
            }
        }
        else if ($this->scope_table == 'ppSD_payment_gateways') {
            if ($this->current_row_data['active'] == '1') {
                return ' converted';
            }
        }
        else if ($this->scope_table == 'ppSD_fields') {
            if ($this->current_row_data['static'] != '1') {
                return " custom";
            }
        }
        else {
            return '';
        }

    }

}

