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

class delete extends admin
{

    protected $id;
    protected $table;
    protected $task;
    protected $special;
    protected $employee;
    protected $function;
    protected $overrideTask = 'delete';
    protected $task_id;
    protected $alter_stats;
    protected $confirm_delete = '1';
    protected $data = array();
    public $reason;
    public $result;

    protected $static_check;
    protected $ownership_check;

    function __construct($id, $table, $special_delete = '0')
    {
        // Variables
        $this->id      = $id;
        $this->table   = $table;
        $this->special = $special_delete;
        // Permissions
        $this->determine_task();

        if (! empty($this->task)) {
            $this->check_permission();
            $this->route_task();
        }
    }

    function determine_task()
    {
        $this->alter_stats = '0';
        switch ($this->table) {
            case 'ppSD_history':
                $this->task            = 'history-delete';
                $this->function        = 'delete_history';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_notes':
                $this->task            = 'note_delete';
                $this->function        = 'delete_note';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_note_labels':
                $this->task            = 'note_label-delete';
                $this->function        = 'delete_note_label';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_packages':
                $this->task            = 'package-delete';
                $this->function        = 'delete_package';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_criteria_cache':
                $this->task            = 'criteria-delete';
                $this->function        = 'delete_criteria';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_custom_actions':
                $this->task            = 'hook-delete';
                $this->function        = 'delete_hook';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_products_linked':
                $this->task            = 'package_item-delete';
                $this->function        = 'delete_package_item';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_form_closed_sessions':
                $this->task            = 'reg_code-delete';
                $this->function        = 'delete_reg_code';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_lead_conversion':
                $this->task            = 'conversion-delete';
                $this->function        = 'delete_conversion';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                $this->alter_stats     = '1';
                break;
            case 'ppSD_contacts':
                $this->task            = 'contact-delete';
                $this->function        = 'delete_contact';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                $this->alter_stats     = '1';
                break;
            case 'ppSD_widgets':
                $this->task            = 'widget-delete';
                $this->function        = 'delete_widget';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_saved_emails':
                $this->task            = 'email-saved-delete';
                $this->function        = 'delete_saved_email';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_uploads':
                $this->task            = 'upload-delete';
                $this->function        = 'delete_upload';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_usage_logs':
                $this->task            = 'usage_delete';
                $this->function        = 'delete_usage';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_member_types':
                $this->task            = 'member_type-delete';
                $this->function        = 'delete_member_type';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_member_types_content':
                $this->task            = 'member_type_content-delete';
                $this->function        = 'delete_member_type_content';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_accounts':
                $this->task            = 'account-delete';
                $this->function        = 'delete_account';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                $this->alter_stats     = '1';
                break;
            case 'ppSD_fields':
                $this->task            = 'field-delete';
                $this->function        = 'delete_field';
                $this->static_check    = '1';
                $this->ownership_check = '0';
                break;
            case 'ppSD_content_access':
                $this->task            = 'content_access-delete';
                $this->function        = 'delete_content_access';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                $this->overrideTask    = 'content_access_lost';
                break;
            case 'ppSD_content':
                $this->task            = 'content-delete';
                $this->function        = 'delete_content';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_event_timeline':
                $this->task            = 'event_timeline-delete';
                $this->function        = 'delete_timeline_entry';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_cart_sessions':
                $this->task            = 'transaction-delete';
                $this->function        = 'delete_transaction';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                $this->alter_stats     = '1';
                break;
            case 'ppSD_cart_billing':
                $this->task            = 'credit_card-delete';
                $this->function        = 'delete_card';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                $this->alter_stats     = '0';
                break;
            case 'ppSD_subscriptions':
                $this->task            = 'subscription-delete';
                $this->function        = 'delete_subscription';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                $this->alter_stats     = '1';
                break;
            case 'ppSD_campaigns':
                $this->task            = 'campaign-delete';
                $this->function        = 'delete_campaign';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_campaign_items':
                $this->task            = 'campaign-item-delete';
                $this->function        = 'delete_campaign_item';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_campaign_unsubscribe':
                $this->task            = 'campaign_unsubscriber-delete';
                $this->function        = 'delete_unsubscription';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                $this->alter_stats     = '1';
                break;
            case 'ppSD_campaign_subscriptions':
                $this->task            = 'campaign_subscriber-delete';
                $this->function        = 'delete_campaign_subscription';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                $this->alter_stats     = '1';
                break;
            case 'ppSD_email_scheduled':
                $this->task            = 'email-queue-delete';
                $this->function        = 'delete_email_queue';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_invoices':
                $this->task            = 'invoice-delete';
                $this->function        = 'delete_invoice';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_invoice_components':
                $this->task            = 'invoice_components-delete';
                $this->function        = 'delete_invoice_component';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_invoice_payments':
                $this->task            = 'invoice_payment-delete';
                $this->function        = 'delete_invoice_payment';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_products':
                $this->task            = 'product_delete';
                $this->function        = 'delete_product';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_login_announcements':
                $this->task            = 'announcement-delete';
                $this->function        = 'delete_announcement';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_logins':
                $this->task            = 'login-delete';
                $this->function        = 'delete_login';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_cart_categories':
                $this->task            = 'category-delete';
                $this->function        = 'delete_categories';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_cart_coupon_codes':
                $this->task            = 'promo_code-delete';
                $this->function        = 'delete_promo_code';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_cart_coupon_codes_used':
                $this->task            = 'promo_code_usage-delete';
                $this->function        = 'delete_promo_codes_usage';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_cart_terms':
                $this->task            = 'shop_terms-delete';
                $this->function        = 'delete_shop_terms';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_tax_classes':
                $this->task            = 'shop_tax-delete';
                $this->function        = 'delete_shop_tax';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_events':
                $this->task            = 'event_delete';
                $this->function        = 'delete_event';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_event_rsvps':
                $this->task            = 'event_rsvp-delete';
                $this->function        = 'delete_rsvp';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_calendars':
                $this->task            = 'calendar-delete';
                $this->function        = 'delete_calendar';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                break;
            case 'ppSD_forms':
                $this->task            = 'form-delete';
                $this->function        = 'delete_form';
                $this->static_check    = '1';
                $this->ownership_check = '1';
                break;
            case 'ppSD_fieldsets':
                $this->task            = 'fieldset-delete';
                $this->function        = 'delete_fieldset';
                $this->static_check    = '1';
                $this->ownership_check = '1';
                break;
            case 'ppSD_event_types':
                $this->task            = 'event_types-delete';
                $this->function        = 'delete_event_types';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_sources':
                $this->task            = 'source-delete';
                $this->function        = 'delete_source';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_source_tracking':
                $this->task            = 'source-tracking-delete';
                $this->function        = 'delete_source_tracking';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_staff':
                $this->task            = 'employee-delete';
                $this->function        = 'delete_employee';
                $this->static_check    = '1';
                $this->ownership_check = '1';
                break;
            case 'ppSD_templates':
                $this->task            = 'template-delete';
                $this->function        = 'delete_template';
                $this->static_check    = '1';
                $this->ownership_check = '1';
                break;
            case 'ppSD_templates_email':
                $this->task            = 'template-email-delete';
                $this->function        = 'delete_email_template';
                $this->static_check    = '1';
                $this->ownership_check = '1';
                break;
            case 'ppSD_shipping_rules':
                $this->task            = 'shop_shipping-delete';
                $this->function        = 'delete_shipping_rule';
                $this->static_check    = '0';
                $this->ownership_check = '0';
                break;
            case 'ppSD_members':
                $this->task            = 'member_delete';
                $this->function        = 'delete_member';
                $this->static_check    = '0';
                $this->ownership_check = '1';
                $this->overrideTask    = 'member_delete';
                break;
            default:
                $this->check_extension();
        }
    }

    function check_extension()
    {
        $ae = new admin_extensions($this->table, $this->employee, $this->table);
        if ($ae->methodExists('delete', 'func')) {
            $data_out = array(
                'id' => $this->id,
                'function' => $this->function,
                'task' => $this->task,
                'table' => $this->table,
                'data' => (array)$this->data,
            );
            $this->task_id = $this->start_task($this->overrideTask, 'staff', $this->id, $this->employee['username'], '', $data_out);

            $ae->runTask('delete', 'func', $data_out);

            $this->end_task($this->task_id, '1', '', $this->table . '_delete', '', $data_out);

            $this->result = '1';
        } else {
            $this->result = '0';
            $this->reason = 'Extension does not have a delete method.';
        }
    }

    function check_permission()
    {
        // This will kill the whole process if
        // the user isn't logged in.
        $this->employee = $this->check_employee($this->task, '0', '0');
        if (empty($this->employee)) {
            $this->task_id        = 'prevent';
            $this->confirm_delete = '0';
        } else {
            if (empty($this->overrideTask)) {
                $this->overrideTask = $this->task;
            }
            $data_out = array(
                'id' => $this->id,
                'function' => $this->function,
                'task' => $this->overrideTask,
                'table' => $this->table,
            );
            $this->task_id = $this->start_task($this->overrideTask, 'staff', $this->id, $this->employee['username'], '', $data_out);
        }
    }

    function reduce_stats()
    {
        $q1 = $this->delete("
            DELETE FROM `ppSD_stats`
            WHERE `key` LIKE '%-" . $this->id . "-%'
        ");
    }

    function route_task()
    {
        if ($this->task_id != 'prevent') {
            $this->data = new history($this->id, '', '', '', '', '', $this->table);
            // Exists?
            if (empty($this->data->final_content)) {
                if ($this->table == 'ppSD_templates_email') {
                    $this->data = $this->get_array("
                        SELECT *
                        FROM `ppSD_templates_email`
                        WHERE `template`='" . $this->mysql_clean($this->id) . "'
                        LIMIT 1
                    ");
                    if (empty($this->data['template'])) {
                        $this->result         = '0';
                        $this->reason         = 'Item not found.';
                        $this->confirm_delete = '0';
                    }
                } else {
                    $this->result         = '0';
                    $this->reason         = 'Item not found.';
                    $this->confirm_delete = '0';
                }
            } else {
                // Ownership issues
                if ($this->ownership_check == '0' && $this->static_check == '0') {
                    if ($this->employee['permissions']['admin'] != '1') {
                        $this->result         = '0';
                        $this->reason         = 'Administrators only.';
                        $this->confirm_delete = '0';
                    }
                } else {
                    if ($this->ownership_check == '1') {
                        $this->check_ownership();
                    }
                    if ($this->static_check == '1') {
                        if ($this->data->final_content['static'] == '1') {
                            $this->result         = '0';
                            $this->reason         = 'Static Component';
                            $this->confirm_delete = '0';
                        }
                    }
                }
            }
            // Confirmed?
            if ($this->confirm_delete == '1') {
                if ($this->alter_stats == '1') {
                    $this->reduce_stats();
                }
                if (method_exists($this, $this->function)) {
                    $function = $this->function;
                    $this->$function();

                    // Complete the process.
                    $data_out = array(
                        'id' => $this->id,
                        'function' => $this->function,
                        'task' => $this->task,
                        'table' => $this->table,
                        'data' => (array)$this->data,
                    );

                    $this->task_id = $this->end_task($this->task_id, '1', '', $this->overrideTask, '', $data_out);
                } else {
                    $this->result         = '0';
                    $this->reason         = 'Method does not exist: ' . $this->function;
                    $this->confirm_delete = '0';
                }
            }
        } else {
            $this->result         = '0';
            $this->reason         = 'Permissions';
            $this->confirm_delete = '0';
        }
    }

    function check_ownership()
    {
        // Special considerations
        if ($this->table == 'ppSD_content_access') {
            $main_data = new history($this->data->final_content['member_id'], '', '', '', '', '', 'ppSD_members');
            $owner     = $main_data->final_content['owner'];
        } else if ($this->table == 'ppSD_event_timeline' || $this->table == 'ppSD_event_rsvps') {
            $main_data = new history($this->data->final_content['event_id'], '', '', '', '', '', 'ppSD_events');
            $owner     = $main_data->final_content['owner'];
        } else if ($this->table == 'ppSD_card_billing') {
            $member = $this->get_array("
                SELECT COUNT(*) FROM `ppSD_members`
                WHERE `id`='" . $this->mysql_clean($this->data->final_content['member_id']) . "'
            ");
            if ($member['0'] > 0) {
                $table = 'ppSD_members';
            } else {
                $table = 'ppSD_contacts';
            }
            $main_data = new history($this->data->final_content['member_id'], '', '', '', '', '', $table);
            $owner     = $main_data->final_content['owner'];
        } else if ($this->table == 'ppSD_campaign_subscriptions' ||
            $this->table == 'ppSD_campaign_unsubscribe' ||
            $this->table == 'ppSD_campaign_items' ||
            $this->table == 'ppSD_campaign_logs'
        ) {
            $main_data = new history($this->data->final_content['campaign_id'], '', '', '', '', '', 'ppSD_campaigns');
            $owner     = $main_data->final_content['owner'];
        } else if ($this->table == 'ppSD_invoice_components' || $this->table == 'ppSD_invoice_payments') {
            $main_data = new history($this->data->final_content['invoice_id'], '', '', '', '', '', 'ppSD_invoices');
            $owner     = $main_data->final_content['owner'];
        } else {
            if (!empty($this->data->final_content['added_by'])) {
                $owner = $this->data->final_content['added_by'];
            } else {
                if (! empty($this->data->final_content['owner'])) {
                    $owner = $this->data->final_content['owner'];
                } else {
                    $owner = '2';
                }
            }
        }
        // Perform the check.
        if ($owner != $this->employee['id'] && $this->employee['permissions']['admin'] != '1') {
            $this->result         = '0';
            $this->reason         = 'Ownership';
            $this->confirm_delete = '0';
        }

    }


    /**
     * Delete login/sessions.
     */
    function delete_login()
    {
        // Data
        $get = $this->get_array("
            SELECT `session_id`
            FROM `ppSD_logins`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");

        $q1           = $this->delete("
            DELETE FROM `ppSD_logins`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");

        $q2           = $this->delete("
            DELETE FROM `ppSD_sessions`
            WHERE `id`='" . $this->mysql_clean($get['session_id']) . "'
            LIMIT 1
        ");

        $this->result = '1';
    }


    /**
     * Delete user or contact history item.
     */
    function delete_history()
    {
        // Ownership?
        $q1           = $this->delete("DELETE FROM `ppSD_history` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        $this->result = '1';
    }

    /**
     * Delete user or contact history item.
     */
    function delete_email_queue()
    {
        // Ownership?
        $q1           = $this->delete("DELETE FROM `ppSD_email_scheduled` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        $this->result = '1';
    }

    /**
     * Delete a note.
     */
    function delete_note()
    {
        // Ownership?
        // Delete note
        $q1 = $this->delete("DELETE FROM `ppSD_notes` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        // Uploads associated with this note?
        $STH = $this->run_query("SELECT `id` FROM `ppSD_uploads` WHERE `note_id`='" . $this->mysql_clean($this->id) . "'");
        while ($row = $STH->fetch()) {
            $del = $this->delete_upload($row['id']);
        }
        $this->result = '1';
    }

    function delete_note_label()
    {
        // Ownership?
        // Delete note
        $find = $this->get_array("
            SELECT `static_lookup`
            FROM `ppSD_note_labels`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");

        if (! empty($find['static_lookup'])) {
            $this->result = '0';
            $this->reason = 'You cannot delete this label.';
        } else {
            $q1 = $this->delete("DELETE FROM `ppSD_note_labels` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
            // Uploads associated with this note?
            $q1           = $this->delete("UPDATE `ppSD_notes` SET `label`='6' WHERE `label`='" . $this->mysql_clean($this->id) . "'");
            $this->result = '1';
        }
    }

    function delete_criteria()
    {
        $q1           = $this->delete("
            DELETE FROM `ppSD_criteria_cache`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        $this->result = '1';
    }

    function delete_hook()
    {
        $get = $this->get_array("
            SELECT *
            FROM `ppSD_custom_actions`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        $q1           = $this->delete("
            DELETE FROM `ppSD_custom_actions`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        if ($get['type'] == '1') {
            $un = @unlink($get['data']);
        }
        $this->result = '1';
    }

    function delete_announcement()
    {
        $q6           = $this->delete("
            DELETE FROM `ppSD_login_announcements`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        $q6           = $this->delete("
            DELETE FROM `ppSD_login_announcement_logs`
            WHERE `announcement_id`='" . $this->mysql_clean($this->id) . "'
        ");
        $this->result = '1';
    }

    /**
     * Delete a user.
     */
    function delete_member($force_id = '')
    {
        // Stats
        if (empty($force_id)) {
            $force_id = $this->id;
        }

        // Get username
        $user = new user;
        $data = $user->get_user($force_id);
        $this->put_stats('members', '1', 'subtract', $data['data']['joined']);

        // Delete the file
        $q1 = $this->delete("DELETE FROM `ppSD_members` WHERE `id`='" . $this->mysql_clean($force_id) . "' LIMIT 1");
        $q2 = $this->delete("DELETE FROM `ppSD_member_data` WHERE `id`='" . $this->mysql_clean($force_id) . "' LIMIT 1");
        $q3 = $this->delete("DELETE FROM `ppSD_notes` WHERE `user_id`='" . $this->mysql_clean($force_id) . "'");
        $q4 = $this->delete("DELETE FROM `ppSD_content_access` WHERE `member_id`='" . $this->mysql_clean($force_id) . "'");
        $q5 = $this->delete("DELETE FROM `ppSD_data_eav` WHERE `item_id`='" . $this->mysql_clean($force_id) . "'");
        $q6 = $this->delete("DELETE FROM `ppSD_login_announcement_logs` WHERE `member_id`='" . $this->mysql_clean($force_id) . "'");
        $q7 = $this->delete("DELETE FROM `ppSD_sessions` WHERE `member_id`='" . $this->mysql_clean($force_id) . "'");

        // Uploads
        $STH = $this->run_query("SELECT `id` FROM `ppSD_uploads` WHERE `member_id`='" . $this->mysql_clean($force_id) . "'");
        while ($row = $STH->fetch()) {
            $del = $this->delete_upload($row['id']);
        }

        // Invoices
        $STH = $this->run_query("SELECT `id` FROM `ppSD_invoices` WHERE `member_id`='" . $this->mysql_clean($force_id) . "'");
        while ($row = $STH->fetch()) {
            $del = $this->delete_invoice($row['id']);
        }

        // Orders
        $STH = $this->run_query("SELECT `id` FROM `ppSD_subscriptions` WHERE `member_id`='" . $this->mysql_clean($force_id) . "'");
        while ($row = $STH->fetch()) {
            $del = $this->delete_subscription($row['id']);
        }

        // Orders
        $STH = $this->run_query("SELECT `id` FROM `ppSD_cart_sessions` WHERE `member_id`='" . $this->mysql_clean($force_id) . "'");
        while ($row = $STH->fetch()) {
            $del = $this->delete_transaction($row['id']);
        }

        // RSVPs
        $STH = $this->run_query("SELECT `id` FROM `ppSD_event_rsvps` WHERE `user_id`='" . $this->mysql_clean($force_id) . "'");
        while ($row = $STH->fetch()) {
            $del = $this->delete_rsvp($row['id']);
        }

        // Delete cache
        $this->delete_cache($force_id);

        // Reply
        $this->result = '1';
    }

    /**
     * Delete a upload.
     */
    function delete_upload()
    {
        $path   = PP_PATH . "/custom/uploads/" . $this->data->final_content['filename'];
        $unlink = @unlink($path);
        // Delete the DB entry
        $q1 = $this->delete("DELETE FROM `ppSD_uploads` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        // Reply
        $this->result = '1';
    }

    /**
     * Delete a conversion.
     */
    function delete_conversion()
    {
        // Stats
        $contact = new contact;
        $data    = $contact->get_conversion($this->id);
        $cdata   = $contact->get_contact($data['contact_id']);
        $this->put_stats('conversions', '1', 'subtract', $data['date']);
        $this->put_stats('conversions-' . $cdata['data']['owner'], '1', 'subtract', $data['date']);
        $this->put_stats('conversions_value', $data['actual_value'], 'subtract', $data['date']);
        $this->put_stats('conversions_value-' . $cdata['data']['owner'], $data['actual_value'], 'subtract', $data['date']);
        $this->put_stats('conversion_value_source-' . $cdata['data']['source'], $data['actual_value'], 'subtract', $data['date']);
        $this->put_stats('conversion_value_acct-' . $cdata['data']['account'], $data['actual_value'], 'subtract', $data['date']);
        $this->put_stats('conversion_est_value', $data['estimated_value'], 'subtract', $data['date']);
        $this->put_stats('conversion_est_value-' . $cdata['data']['owner'], $data['estimated_value'], 'subtract', $data['date']);
        $this->put_stats('conversion_est_value_source-' . $cdata['data']['source'], $data['estimated_value'], 'subtract', $data['date']);
        $this->put_stats('conversion_est_value_acct-' . $cdata['data']['account'], $data['estimated_value'], 'subtract', $data['date']);
        $this->put_stats('conversion_perchange', $data['percent_change'], 'subtract', $data['date']);
        $this->put_stats('conversion_perchange-' . $cdata['data']['owner'], $data['percent_change'], 'subtract', $data['date']);
        $this->put_stats('conversion_perchange_source-' . $cdata['data']['source'], $data['percent_change'], 'subtract', $data['date']);
        $this->put_stats('conversion_perchange_acct-' . $cdata['data']['account'], $data['percent_change'], 'subtract', $data['date']);
        // Update contact
        $q1 = $this->update("UPDATE `ppSD_contacts` SET `converted`='0',`converted_id`='' WHERE `id`='" . $this->data->final_content['contact_id'] . "' LIMIT 1");
        // Delete
        $q2 = $this->delete("DELETE FROM `ppSD_lead_conversion` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        // Reply
        $this->result = '1';
    }

    /**
     * Delete a contact.
     */
    function delete_contact()
    {
        // Stats
        $contact = new contact;
        $data    = $contact->get_contact($this->id);
        if (! empty($data['data']['created'])) {
            $this->put_stats('contacts', '1', 'subtract', $data['data']['created']);
            $this->put_stats('contacts-' . $data['data']['owner'], '1', 'subtract', $data['data']['created']);
        }
        // Delete primary stuff
        $q1 = $this->delete("DELETE FROM `ppSD_contacts` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        $q2 = $this->delete("DELETE FROM `ppSD_contact_data` WHERE `contact_id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        $q3 = $this->delete("DELETE FROM `ppSD_notes` WHERE `user_id`='" . $this->mysql_clean($this->id) . "'");
        $q4 = $this->delete("DELETE FROM `ppSD_history` WHERE `user_id`='" . $this->mysql_clean($this->id) . "'");
        $q5 = $this->delete("DELETE FROM `ppSD_saved_emails` WHERE `user_id`='" . $this->mysql_clean($this->id) . "'");
        $q6 = $this->delete("DELETE FROM `ppSD_lead_conversion` WHERE `contact_id`='" . $this->mysql_clean($this->id) . "'");
        // Uploads
        $STH = $this->run_query("SELECT `id` FROM `ppSD_uploads` WHERE `item_id`='" . $this->mysql_clean($this->id) . "'");
        while ($row = $STH->fetch()) {
            $del = $this->delete_upload($row['id']);
        }
        // Reply
        $this->result = '1';
    }

    /**
     * Delete a sent email.
     */
    function delete_saved_email()
    {
        // Delete
        $q1 = $this->delete("DELETE FROM `ppSD_saved_emails` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        // Delete
        $q2 = $this->delete("DELETE FROM `ppSD_history` WHERE `act_id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        // Reply
        $this->result = '1';
    }

    /**
     * Delete an account.
     */
    function delete_account()
    {
        // Delete Members
        $STH = $this->run_query("SELECT `id` FROM `ppSD_members` WHERE `account`='" . $this->mysql_clean($this->id) . "'");
        while ($row = $STH->fetch()) {
            $del_mem = $this->delete_member($row['id']);
        }
        // Delete Contacts
        $STH = $this->run_query("SELECT `id` FROM `ppSD_contacts` WHERE `account`='" . $this->mysql_clean($this->id) . "'");
        while ($row = $STH->fetch()) {
            $del_con = $this->delete_contact($row['id']);
        }
        // Delete
        $q1 = $this->delete("DELETE FROM `ppSD_accounts` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        $q2 = $this->delete("DELETE FROM `ppSD_account_data` WHERE `account_id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        // Reply
        $this->result = '1';
    }

    /**
     * Delete a field
     */
    function delete_field()
    {
        // Delete
        $q1 = $this->delete("DELETE FROM `ppSD_fields` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        $q2 = $this->delete("DELETE FROM `ppSD_fieldsets_fields` WHERE `field`='" . $this->mysql_clean($this->id) . "'");
        $q3 = $this->delete("DELETE FROM `ppSD_field_logic` WHERE `field_id`='" . $this->mysql_clean($this->id) . "'");
        // Main DBs
        $q4 = $this->run_query("ALTER TABLE `ppSD_contact_data` DROP COLUMN `" . $this->mysql_clean($this->id) . "`");
        $q5 = $this->run_query("ALTER TABLE `ppSD_member_data` DROP COLUMN `" . $this->mysql_clean($this->id) . "`");
        $q6 = $this->run_query("ALTER TABLE `ppSD_event_rsvp_data` DROP COLUMN `" . $this->mysql_clean($this->id) . "`");
        $q7 = $this->run_query("ALTER TABLE `ppSD_account_data` DROP COLUMN `" . $this->mysql_clean($this->id) . "`");
        // Reply
        $this->result = '1';
    }

    /**
     * Delete a field
     */
    function delete_content_access()
    {
        // Delete
        $q1 = $this->delete("
            DELETE FROM `ppSD_content_access`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        // Reply
        $this->result = '1';
    }

    function delete_content()
    {
        // Type specific
        if ($this->data->final_content['type'] == 'folder') {
            $path = $this->data->final_content['path'] . '/.htaccess';
            if (!unlink($path)) {
                if (file_exists($path)) {
                    $this->result = '0';
                    $this->reason = 'Could not delete the .htaccess file from the directory. Please do so manually before proceeding.';
                }
            }
        } else if ($this->data->final_content['type'] == 'page') {
            // $del_template = $this->delete_template('content-' . $this->data['id']);
        }
        if ($this->result != '1') {
            // Delete
            $q1 = $this->delete("
                DELETE FROM `ppSD_content`
                WHERE `id`='" . $this->mysql_clean($this->id) . "'
                LIMIT 1
            ");
            $q2 = $this->delete("
                DELETE FROM `ppSD_content_access`
                WHERE `content_id`='" . $this->mysql_clean($this->id) . "'
            ");
            $q3 = $this->delete("
                DELETE FROM `ppSD_widgets_menus`
                WHERE `content_id`='" . $this->mysql_clean($this->id) . "'
            ");
        }
        // Reply
        $this->result = '1';
    }

    function delete_widget()
    {
        $q1 = $this->delete("
            DELETE FROM `ppSD_widgets`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        $q2 = $this->delete("
            DELETE FROM `ppSD_widgets_menus`
            WHERE `widget_id`='" . $this->mysql_clean($this->id) . "'
        ");
        $q3 = $this->delete("
            DELETE FROM `ppSD_options`
            WHERE `id` LIKE 'wg_" . $this->mysql_clean($this->id) . "%'
            LIMIT 1
        ");
        $this->result = '1';
    }

    function delete_template()
    {
        // Delete
        $q1 = $this->delete("
            DELETE FROM `ppSD_templates`
            WHERE `id`='" . $this->mysql_clean($this->id) . "' AND `static`!='1'
            LIMIT 1
        ");
        // Reply
        $this->result = '1';
    }

    function delete_email_template()
    {
        // Delete
        $q1 = $this->delete("
            DELETE FROM `ppSD_templates_email`
            WHERE `template`='" . $this->mysql_clean($this->id) . "' AND `static`!='1'
            LIMIT 1
        ");
        // Reply
        $this->result = '1';
    }

    /**
     * Delete a package
     */
    function delete_package()
    {
        $q1           = $this->delete("
            DELETE FROM `ppSD_packages`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        $q2           = $this->delete("
            DELETE FROM `ppSD_products_linked`
            WHERE `package_id`='" . $this->mysql_clean($this->id) . "'
        ");
        $this->result = '1';
    }

    /**
     * Delete a package
     */
    function delete_package_item()
    {
        $q1           = $this->delete("
            DELETE FROM `ppSD_products_linked`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        $this->result = '1';
    }

    /**
     * Delete a credit card
     */
    function delete_card()
    {
        $q1           = $this->delete("
            DELETE FROM `ppSD_cart_billing`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        $q1           = $this->delete("
            UPDATE `ppSD_subscriptions`
            SET `card_id`=''
            WHERE `card_id`='" . $this->mysql_clean($this->id) . "'
        ");
        $this->result = '1';
    }

    /**
     * Delete a transaction
     */
    function delete_transaction($force_id = '')
    {
        if (empty($force_id)) {
            $force_id = $this->id;
        }
        // Stats
        $cart  = new cart;
        $order = $cart->get_order($force_id, '0');
        $this->put_stats('sales', '1', 'subtract', $order['data']['date_completed']);
        $this->put_stats('sales-' . $order['data']['member_id'], '1', 'subtract', $order['data']['date_completed']);
        $this->put_stats('revenue', $order['pricing']['total'], 'subtract', $order['data']['date_completed']);
        $this->put_stats('revenue-' . $order['data']['member_id'], $order['pricing']['total'], 'subtract', $order['data']['date_completed']);
        $this->put_stats('savings', $order['pricing']['savings'], 'subtract', $order['data']['date_completed']);
        $this->put_stats('shipping', $order['pricing']['shipping'], 'subtract', $order['data']['date_completed']);
        $this->put_stats('tax', $order['pricing']['tax'], 'subtract', $order['data']['date_completed']);
        $this->put_stats('fees', $order['pricing']['gateway_fees'], 'subtract', $order['data']['date_completed']);
        // Refunds
        $this->delete_refunds($order);
        // Coupon?
        if (!empty($data['data']['code'])) {
            $this->put_stats('coupon_usage', '1', 'subtract', $data['data']['date']);
            $this->put_stats('coupon_usage-' . $data['data']['code'], '1', 'subtract', $data['data']['date']);
            $this->put_stats('coupon_savings-' . $data['data']['code'], $order['pricing']['savings'], 'subtract', $data['data']['date']);
        }
        // Delete
        $q5 = $this->delete("DELETE FROM `ppSD_cart_coupon_codes_used` WHERE `order_id`='" . $this->mysql_clean($force_id) . "' LIMIT 1");
        $q1 = $this->delete("DELETE FROM `ppSD_cart_sessions` WHERE `id`='" . $this->mysql_clean($force_id) . "' LIMIT 1");
        $q2 = $this->delete("DELETE FROM `ppSD_cart_session_totals` WHERE `id`='" . $this->mysql_clean($force_id) . "' LIMIT 1");
        $q4 = $this->delete("DELETE FROM `ppSD_cart_items_complete` WHERE `cart_session`='" . $this->mysql_clean($force_id) . "'");
        $q5 = $this->delete("DELETE FROM `ppSD_cart_items` WHERE `cart_session`='" . $this->mysql_clean($force_id) . "'");
        // Reply
        $this->result = '1';
    }

    function delete_refunds($data)
    {
        $total             = $this->get_array("
            SELECT SUM(total)
            FROM `ppSD_cart_refunds`
            WHERE `order_id`='" . $this->mysql_clean($this->id) . "' AND `type`='1'
        ");
        $total_chargebacks = $this->get_array("
            SELECT SUM(total)
            FROM `ppSD_cart_refunds`
            WHERE `order_id`='" . $this->mysql_clean($this->id) . "' AND `type`='2'
        ");
        $count             = $this->get_array("
            SELECT COUNT(*)
            FROM `ppSD_cart_refunds`
            WHERE `order_id`='" . $this->mysql_clean($this->id) . "' AND `type`='1'
        ");
        $count_chargebacks = $this->get_array("
            SELECT COUNT(*)
            FROM `ppSD_cart_refunds`
            WHERE `order_id`='" . $this->mysql_clean($this->id) . "' AND `type`='2'
        ");
        $this->put_stats('refund_totals', $total['0'], 'subtract', $data['data']['date']);
        $this->put_stats('refunds', $count['0'], 'subtract', $data['data']['date']);
        $this->put_stats('chargeback_totals', $total_chargebacks['0'], 'subtract', $data['data']['date']);
        $this->put_stats('chargebacks', $count_chargebacks['0'], 'subtract', $data['data']['date']);
        $q3 = $this->delete("DELETE FROM `ppSD_cart_refunds` WHERE `order_id`='" . $this->mysql_clean($this->id) . "'");
    }

    /**
     * Delete a subscription
     */
    function delete_subscription($force_id = '')
    {
        if (empty($force_id)) {
            $force_id = $this->id;
        }
        $renewed = $this->get_array("
            SELECT COUNT(*)
            FROM `ppSD_cart_items_complete`
            JOIN `ppSD_cart_sessions`
            ON ppSD_cart_sessions.id=ppSD_cart_items_complete.cart_session
            WHERE
                ppSD_cart_items_complete.subscription_id='" . $this->mysql_clean($force_id) . "' AND
                ppSD_cart_sessions.status='1'
        ");
        $income  = $this->get_array("
            SELECT SUM(unit_price * qty)
            FROM `ppSD_cart_items_complete`
            JOIN `ppSD_cart_sessions`
            ON ppSD_cart_sessions.id=ppSD_cart_items_complete.cart_session
            WHERE
                ppSD_cart_items_complete.subscription_id='" . $this->mysql_clean($force_id) . "' AND
                ppSD_cart_sessions.status='1'
        ");
        $sub     = new subscription;
        $data    = $sub->get_subscription($force_id);
        $this->put_stats('revenue', $income['0'], 'subtract', $data['data']['date']);
        $this->put_stats('sales', $renewed['0'], 'subtract', $data['data']['date']);
        $this->put_stats('subscriptions', '1', 'subtract', $data['data']['date']);
        $this->put_stats('subscriptions_created-' . $data['data']['product'], '1', 'subtract', $data['data']['date']);
        $this->put_stats('renewals-' . $data['data']['product'], $renewed['0'], 'subtract', $data['data']['date']);
        $this->put_stats('renewals_approved', $renewed['0'], 'subtract', $data['data']['date']);
        $this->put_stats('renewal_income', $income['0'], 'subtract', $data['data']['date']);
        $this->put_stats('renewal_income-' . $data['data']['product'], $income['0'], 'subtract', $data['data']['date']);
        if ($data['data']['status'] == '2') {
            $this->put_stats('subscriptions_canceled', '1', 'subtract', $data['data']['date']);
            $this->put_stats('subscriptions_canceled-' . $data['data']['product'], '1', 'subtract', $data['data']['date']);
        }
        // Delete
        $q1 = $this->delete("DELETE FROM `ppSD_subscriptions` WHERE `id`='" . $this->mysql_clean($force_id) . "' LIMIT 1");
        $q4 = $this->delete("DELETE FROM `ppSD_cart_items_complete` WHERE `subscription_id`='" . $this->mysql_clean($force_id) . "'");
        /*
        $q100 = $this->run_query("
            SELECT `id`
            FROM `ppSD_cart_items_complete`
            WHERE `subscription_id`='" . $this->mysql_clean($this->id) . "'
        ");
        while ($row = $q100->fetch()) {
            $this->delete_cart_item($row['id']);
        }
        */
        // Reply
        $this->result = '1';
    }

    /**
     * Delete a field
     */
    function delete_invoice_component()
    {
        // Delete
        $q1 = $this->delete("
            DELETE FROM `ppSD_invoice_components`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        // Redo invoice
        $invoice = new invoice;
        $invoice->recalculate_totals($this->data->final_content['invoice_id']);
        $invoice->get_invoice($this->data->final_content['invoice_id'], '1'); // Re-cache
        // Reply
        $this->result = '1';
    }

    /**
     * Delete usage logs
     */
    function delete_usage()
    {
        $q1 = $this->delete("
            DELETE FROM `ppSD_usage_logs`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        // Reply
        $this->result = '1';
    }

    /**
     * Delete an invoice
     */
    function delete_invoice()
    {
        // Stats
        $invoice = new invoice;
        $data    = $invoice->get_invoice($this->id);
        $this->put_stats('invoices', '1', 'subtract', $data['data']['date']);
        $this->put_stats('invoices-' . $data['data']['owner'], '1', 'subtract', $data['data']['date']);
        $this->put_stats('invoice_revenue', $data['totals']['paid'], 'subtract', $data['data']['date']);
        $this->put_stats('invoice_revenue-' . $data['data']['owner'], $data['totals']['paid'], 'subtract', $data['data']['date']);
        $this->put_stats('invoices_outstanding', $data['totals']['due'], 'subtract', $data['data']['date']);
        $this->put_stats('invoices_outstanding-' . $data['data']['owner'], $data['totals']['due'], 'subtract', $data['data']['date']);
        if ($data['data']['status'] == '1') {
            $this->put_stats('invoiced_closed', '1', 'subtract', $data['data']['date']);
            $this->put_stats('invoiced_closed-' . $data['data']['owner'], '1', 'subtract', $data['data']['date']);
        }
        // Delete
        $q1 = $this->delete("
            DELETE FROM `ppSD_invoices`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        $q3 = $this->delete("
            DELETE FROM `ppSD_invoice_totals`
            WHERE `invoice_id`='" . $this->mysql_clean($this->id) . "'
        ");
        $q4 = $this->delete("
            DELETE ppSD_cart_sessions, ppSD_cart_session_totals
            FROM ppSD_cart_sessions
            LEFT JOIN ppSD_cart_session_totals
            ON ppSD_cart_sessions.id=ppSD_cart_session_totals.id
            WHERE ppSD_cart_sessions.invoice_id='" . $this->mysql_clean($this->id) . "''
        ");
        // Payments
        $this->delete_invoice_payments($data);
        // Payments
        // $this->delete_invoice_payment($data);
        // Reply
        $this->result = '1';
    }

    function delete_invoice_payment($id)
    {
        $invoice = new invoice();
        $pay     = $invoice->get_payment($id);
        $inv     = $invoice->get_invoice($pay['invoice_id']);
        $q3      = $this->delete("
            DELETE FROM `ppSD_invoice_payments`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
        ");
        $this->put_stats('invoice_payments', '1', 'subtract', $pay['date']);
        $this->put_stats('invoice_payments-' . $inv['data']['owner'], '1', 'subtract', $pay['date']);
        $this->put_stats('invoice_revenue', $pay['paid'], 'subtract', $pay['date']);
        $this->put_stats('invoice_revenue-' . $inv['data']['owner'], $pay['paid'], 'subtract', $pay['date']);
        $invoice->recalculate_totals($pay['invoice_id']);
        $this->result = '1';
    }

    function delete_invoice_payments($data)
    {
        $q1 = $this->get_array("
            SELECT COUNT(*)
            FROM `ppSD_invoice_payments`
            WHERE `invoice_id`='" . $this->mysql_clean($this->id) . "'
        ");
        $this->put_stats('invoice_payments', $q1['0'], 'subtract', $data['data']['date']);
        $this->put_stats('invoice_payments-' . $data['data']['owner'], $q1['0'], 'subtract', $data['data']['date']);
        $q3 = $this->delete("
            DELETE FROM `ppSD_invoice_payments`
            WHERE `invoice_id`='" . $this->mysql_clean($this->id) . "'
        ");
        $this->result = '1';
    }


    function delete_member_type()
    {
        $query = $this->delete("
            DELETE FROM `ppSD_member_types`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        $query = $this->delete("
            DELETE FROM `ppSD_member_types_content`
            WHERE `member_type`='" . $this->mysql_clean($this->id) . "'
        ");
        $query1 = $this->update("
            UPDATE `ppSD_members`
            SET `member_type`=''
            WHERE `member_type`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        $this->result = '1';
    }


    function delete_member_type_content()
    {
        $query = $this->delete("
            DELETE FROM `ppSD_member_types_content`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        $this->result = '1';
    }


    function delete_invoice_components($data)
    {
        $sum = $this->get_array("
            SELECT SUM(minutes)
            FROM `ppSD_invoice_components`
            WHERE `invoice_id`='" . $this->mysql_clean($this->id) . "' AND `type`='time'
        ");
        $this->put_stats('invoices_minutes_billed', $sum['0'], 'subtract', $data['data']['date']);
        $this->put_stats('invoices_minutes_billed-' . $data['data']['owner'], $sum['0'], 'subtract', $data['data']['date']);
        $q2 = $this->delete("
            DELETE FROM `ppSD_invoice_components`
            WHERE `invoice_id`='" . $this->mysql_clean($this->id) . "'
        ");
        $this->result = '1';
    }

    /**
     * Delete a product
     */
    function delete_product()
    {
        // Delete
        $q1 = $this->delete("DELETE FROM `ppSD_products` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        $q2 = $this->delete("DELETE FROM `ppSD_products_linked` WHERE `product_id`='" . $this->mysql_clean($this->id) . "'");
        $q3 = $this->delete("DELETE FROM `ppSD_products_options` WHERE `product_id`='" . $this->mysql_clean($this->id) . "'");
        $q4 = $this->delete("DELETE FROM `ppSD_products_options_qty` WHERE `product_id`='" . $this->mysql_clean($this->id) . "'");
        $q5 = $this->delete("DELETE FROM `ppSD_products_tiers` WHERE `product_id`='" . $this->mysql_clean($this->id) . "'");
        $q6 = $this->delete("DELETE FROM `ppSD_products_tiers` WHERE `product_id`='" . $this->mysql_clean($this->id) . "'");
        $q7 = $this->delete("DELETE FROM `ppSD_product_views` WHERE `product_id`='" . $this->mysql_clean($this->id) . "'");
        // Reply
        $this->result = '1';
    }

    /**
     * Delete a product
     */
    function delete_categories()
    {
        // Delete
        if ($this->id == '1') {
            // Reply
            $this->result = '0';
            $this->reason = 'You cannot delete the home category of your shop.';
        } else {
            $q1 = $this->delete("DELETE FROM `ppSD_cart_categories` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
            if ($this->special == '1') {
                $q1 = $this->delete("DELETE FROM `ppSD_products` WHERE `category`='" . $this->mysql_clean($this->id) . "'");
            } else {
                $q1 = $this->update("UPDATE `ppSD_products` SET `category`='0',`hide`='1' WHERE `category`='" . $this->mysql_clean($this->id) . "'");
            }
            // Reply
            $this->result = '1';
        }
    }

    /**
     * Delete a promo code
     */
    function delete_promo_code()
    {
        $total = $this->run_query("
            SELECT `order_id`,`date`,`savings`
            FROM `ppSD_cart_coupon_codes`
            WHERE `code`='" . $this->mysql_clean($this->id) . "'
        ");
        while ($row = $total->fetch()) {
            /*
            $this->put_stats('coupon_usage','1','subtract',$row['date']);
            $this->put_stats('coupon_usage-' . $row['code'],'1','subtract',$row['date']);
            $this->put_stats('coupon_savings-' . $row['code'],$row['savings'],'subtract',$row['date']);
            $this->put_stats('savings',$row['savings'],'subtract',$row['date']);
            */
            $this->delete_promo_codes_usage($row['order_id']);
        }
        // Delete
        $q1 = $this->delete("DELETE FROM `ppSD_cart_coupon_codes` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        // $q1 = $this->delete("DELETE FROM `ppSD_cart_coupon_codes_used` WHERE `id`='" . $this->mysql_clean($this->id) . "'");
        // Reply
        $this->result = '1';
    }

    /**
     * Delete a promo code
     */
    function delete_promo_codes_usage($id = '')
    {
        if (empty($id)) {
            $id = $this->id;
        }
        $total = $this->run_query("
            SELECT `date`,`savings`
            FROM `ppSD_cart_coupon_codes`
            WHERE `order_id`='" . $this->mysql_clean($id) . "'
        ");
        while ($row = $total->fetch()) {
            $this->put_stats('coupon_usage', '1', 'subtract', $row['date']);
            $this->put_stats('coupon_usage-' . $row['code'], '1', 'subtract', $row['date']);
            $this->put_stats('coupon_savings-' . $row['code'], $row['savings'], 'subtract', $row['date']);
            $this->put_stats('savings', $row['savings'], 'subtract', $row['date']);
        }
        // Delete
        $q1 = $this->delete("DELETE FROM `ppSD_cart_coupon_codes_used` WHERE `id`='" . $this->mysql_clean($id) . "' LIMIT 1");
        // Reply
        $this->result = '1';
    }

    /**
     * Delete terms
     */
    function delete_shop_terms()
    {
        // Delete
        $q1 = $this->delete("DELETE FROM `ppSD_cart_terms` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        $q1 = $this->delete("UPDATE `ppSD_products` SET `terms`='' WHERE `terms`='" . $this->mysql_clean($this->id) . "'");
        // Reply
        $this->result = '1';
    }

    /**
     * Delete tax
     */
    function delete_shop_tax()
    {
        // Delete
        $q1 = $this->delete("DELETE FROM `ppSD_tax_classes` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        // Reply
        $this->result = '1';
    }

    /**
     * Delete events
     */
    function delete_event()
    {
        // Delete
        $q1 = $this->delete("DELETE FROM `ppSD_events` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        $q2 = $this->delete("DELETE FROM `ppSD_event_products` WHERE `event_id`='" . $this->mysql_clean($this->id) . "'");
        $q3 = $this->delete("DELETE FROM `ppSD_event_reminders` WHERE `event_id`='" . $this->mysql_clean($this->id) . "'");
        $q4 = $this->delete("DELETE FROM `ppSD_event_tags` WHERE `event_id`='" . $this->mysql_clean($this->id) . "'");
        $q5 = $this->delete("DELETE FROM `ppSD_event_timeline` WHERE `event_id`='" . $this->mysql_clean($this->id) . "'");
        // Delete RSVP
        $STH = $this->run_query("SELECT `id` FROM `ppSD_event_rsvps` WHERE `event_id`='" . $this->mysql_clean($this->id) . "'");
        while ($row = $STH->fetch()) {
            $del_rsvp = $this->delete_rsvp($row['id']);
        }
        // Reply
        $this->result = '1';
    }

    /**
     * Delete event timeline entry.
     */
    function delete_timeline_entry()
    {
        // Delete
        $q1 = $this->delete("DELETE FROM `ppSD_event_timeline` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        // Reply
        $deleted_msg = "Deleted event timeline entry $this->id";
        // Re-cache
        $event = new event;
        $event->get_event($this->data->final_content['event_id'], '1');
        // End task
        $this->result = '1';
    }

    /**
     * Delete events RSVP
     */
    function delete_rsvp()
    {
        // Stats
        $event = new event;
        $data  = $event->get_rsvp($this->id);
        $this->put_stats('rsvps', '1', 'subtract', $data['date']);
        $this->put_stats('rsvps_' . $data['event_id'], '1', 'subtract', $data['date']);
        // Delete
        $q1 = $this->delete("DELETE FROM `ppSD_event_rsvps` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        $q2 = $this->delete("DELETE FROM `ppSD_event_rsvp_data` WHERE `rsvp_id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        // Invoices?
        $find = $this->get_array("
            SELECT `id`
            FROM `ppSD_invoices`
            WHERE `rsvp_id`='" . $this->mysql_clean($this->id) . "'
        ");
        if (!empty($find['id'])) {
            $this->delete_invoice($find['id']);
        }
        // Reply
        $this->result = '1';
    }

    /**
     * Delete events RSVP
     */
    function delete_calendar()
    {
        // Delete
        $q1 = $this->delete("DELETE FROM `ppSD_calendars` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        if ($this->special == '1') {
            $q2 = $this->delete("DELETE FROM `ppSD_events` WHERE `calendar_id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        }
        // Reply
        $this->result = '1';
    }

    /**
     * Delete a form
     * and fieldsets on that form.
     */
    function delete_form($force_id = '')
    {
        if (!empty($force_id)) {
            $use_id    = $force_id;
            $simple_id = $force_id;
        } else {
            $use_id    = $this->id;
            $simple_id = str_replace('register-', '', $this->id);
        }
        // Delete
        $q1 = $this->delete("
            DELETE FROM `ppSD_forms`
            WHERE `id`='" . $this->mysql_clean($use_id) . "'
            LIMIT 1
        ");
        // Delete fieldsets
        $q2 = $this->delete("
            DELETE FROM `ppSD_fieldsets_locations`
            WHERE `act_id`='" . $this->mysql_clean($simple_id) . "'
        ");
        // Delete products
        $q3 = $this->delete("
            DELETE FROM `ppSD_form_products`
            WHERE `form_id`='" . $this->mysql_clean($simple_id) . "'
        ");
        // Delete products
        $q4 = $this->delete("
            DELETE FROM `ppSD_access_granters`
            WHERE `item_id`='" . $this->mysql_clean($simple_id) . "'
        ");
        // Remove conditions
        $cond = new conditions;
        $cond->delete_act_conditions($simple_id);
        // Reply
        $this->result = '1';
    }

    /**
     * Delete a fieldset
     */
    function delete_fieldset()
    {
        // Delete
        $q1 = $this->delete("DELETE FROM `ppSD_fieldsets_locations` WHERE `fieldset_id`='" . $this->mysql_clean($this->id) . "'");
        $q2 = $this->delete("DELETE FROM `ppSD_fieldsets_fields` WHERE `fieldset`='" . $this->mysql_clean($this->id) . "'");
        $q3 = $this->delete("DELETE FROM `ppSD_fieldsets` WHERE `id`='" . $this->mysql_clean($this->id) . "'");
        // Reply
        $this->result = '1';
    }

    /**
     * Delete an event type
     */
    function delete_event_types()
    {
        // Delete
        $q1 = $this->delete("
			DELETE FROM `ppSD_event_types`
			WHERE `id`='" . $this->mysql_clean($this->id) . "'
			LIMIT 1
		");
        $q2 = $this->delete("
			DELETE FROM `ppSD_event_tags`
			WHERE `tag`='" . $this->mysql_clean($this->id) . "'
		");
        // Reply
        $this->result = '1';
    }


    function delete_source_tracking()
    {
        $q1 = $this->delete("
			DELETE FROM `ppSD_source_tracking`
			WHERE `id`='" . $this->mysql_clean($this->id) . "'
			LIMIT 1
		");
        $this->result = '1';
    }

    /**
     * Delete source
     */
    function delete_source()
    {
        // Delete
        $q1 = $this->delete("
			DELETE FROM `ppSD_sources`
			WHERE `id`='" . $this->mysql_clean($this->id) . "'
			LIMIT 1
		");
        $q1 = $this->delete("
			DELETE FROM `ppSD_source_tracking`
			WHERE `source_id`='" . $this->mysql_clean($this->id) . "'
			LIMIT 1
		");
        $q2 = $this->update("
			UPDATE `ppSD_members`
			SET `source`=''
			WHERE `source`='" . $this->mysql_clean($this->id) . "'
		");
        $q3 = $this->update("
			UPDATE `ppSD_contacts`
			SET `source`=''
			WHERE `source`='" . $this->mysql_clean($this->id) . "'
		");
        $q4 = $this->update("
			UPDATE `ppSD_accounts`
			SET `source`=''
			WHERE `source`='" . $this->mysql_clean($this->id) . "'
		");
        // Reply
        $this->result = '1';
    }

    /**
     * Delete an employee
     */
    function delete_employee()
    {
        // Delete
        $q1 = $this->delete("
			DELETE FROM `ppSD_staff`
			WHERE `id`='" . $this->mysql_clean($this->id) . "'
			LIMIT 1
		");
        $q1 = $this->update("
			UPDATE `ppSD_members` SET `owner`='2'
			WHERE `owner`='" . $this->mysql_clean($this->id) . "'
		");
        $q1 = $this->update("
			UPDATE `ppSD_contacts` SET `owner`='2'
			WHERE `owner`='" . $this->mysql_clean($this->id) . "'
		");
        $q1 = $this->update("
			UPDATE `ppSD_accounts` SET `owner`='2'
			WHERE `owner`='" . $this->mysql_clean($this->id) . "'
		");
        $q1 = $this->update("
			UPDATE `ppSD_events` SET `owner`='2'
			WHERE `owner`='" . $this->mysql_clean($this->id) . "'
		");
        // Reply
        $this->result = '1';
    }

    /**
     * Delete an employee
     */
    function delete_shipping_rule()
    {
        // Delete
        $q1 = $this->delete("DELETE FROM `ppSD_shipping_rules` WHERE `id`='" . $this->mysql_clean($this->id) . "' LIMIT 1");
        // Reply
        $this->result = '1';
    }

    function delete_campaign_item()
    {
        $q2 = $this->delete("
            DELETE FROM `ppSD_campaign_items`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        // Reply
        $this->result = '1';
    }

    function delete_campaign()
    {
        // Delete
        $q1 = $this->delete("
            DELETE FROM `ppSD_campaigns`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        $q2 = $this->delete("
            DELETE FROM `ppSD_campaign_items`
            WHERE `campaign_id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        $q3 = $this->delete("
            DELETE FROM `ppSD_campaign_logs`
            WHERE `campaign_id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        $q4 = $this->delete("
            DELETE FROM `ppSD_campaign_unsubscribe`
            WHERE `campaign_id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        // Form
        $this->delete_form('campaign-' . $this->id);
        // Reply
        $this->result = '1';
    }

    function delete_unsubscription()
    {
        // Delete
        $q1 = $this->delete("
            DELETE FROM `ppSD_campaign_unsubscribe`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        // Reply
        $this->result = '1';
    }

    function delete_campaign_subscription()
    {
        global $employee;
        // Unsubscribe
        $data     = $this->get_array("
            SELECT *
            FROM `ppSD_campaign_subscriptions`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        $campaign = new campaign($data['campaign_id']);
        $campaign->unsubscribe($data['user_id'], $data['user_type'], 'staff', $employee['id']);
        // Delete
        $q1 = $this->delete("
            DELETE FROM `ppSD_campaign_subscriptions`
            WHERE `id`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        // Reply
        $this->result = '1';
    }

    function delete_reg_code()
    {
        // Delete Code
        $data = $this->get_array("
            DELETE FROM `ppSD_form_closed_sessions`
            WHERE `code`='" . $this->mysql_clean($this->id) . "'
            LIMIT 1
        ");
        // Reply
        $this->result = '1';
    }

}
