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

class hooks
{

    public function getList($selected = '', $html = false)
    {
        $array = array(
            'Account' => array(
                'account_create' => 'Created',
                'account_delete' => 'Deleted',
                'account_edit' => 'Edited',
            ),
            'Cart' => array(
                'cart_add' => 'Add Product to Cart',
                'cart_empty' => 'Empty Cart',
                'cart_remove' => 'Remove Product from Cart',
                'cart_update' => 'Update Product Quantity',
                'cc_add' => 'Credit Card Added.',
                'cc_delete' => 'Credit Card Deleted.',
                'product_add' => 'Product Created',
                'product_edit' => 'Product Edited',
                'product_delete' => 'Product Deleted',
            ),
            'Contact' => array(
                'contact_assigned' => 'Assigned to Employee',
                'contact_converted' => 'Converted',
                'contact_create' => 'Created',
                'contact_delete' => 'Deleted',
                'contact_edit' => 'Edited',
                'contact_change_type' => 'Type Changed (Pipeline)',
            ),
            'Event Registration' => array(
                'event_add_registrant' => 'Created',
                'event_checkin' => 'Checked In',
                'event_rsvp_delete' => 'Deleted',
                'event_add' => 'Event Created',
                'event_edit' => 'Event Edited',
                'event_delete' => 'Event Deleted',
            ),
            'Invoice' => array(
                'invoice_closed' => 'Closed',
                'invoice_create' => 'Created',
                'invoice_dead' => 'Marked Dead',
                'invoice_delete' => 'Deleted',
                'invoice_payment' => 'Payment',
            ),
            'Member' => array(
                'content_access_add' => 'Content Access Added',
                'content_access_lost' => 'Content Access Lost',
                'member_create' => 'Created',
                'member_delete' => 'Deleted',
                'member_edit' => 'Edited',
                'activate' => 'E-Mail Activation Complete',
                'login' => 'Logged In',
                'logout' => 'Logged Out',
                'member_status_change' => 'Status Changed',
                'password_reset' => 'Password Reset',
                'password_reset_request' => 'Password Reset Requested',
                'dependency_form' => 'Dependency Form Submitted',
            ),
            'Other' => array(
                'delete' => 'Delete Task (Generic)',
                'note_add' => 'Note Created',
                'note_edit' => 'Note Updated',
                'note_delete' => 'Note Deleted',
                'form_submit' => 'Sub-Form Submit',
            ),
            'Subscription' => array(
                'subscription_cancel' => 'Canceled',
                'subscription_delete' => 'Deleted',
                'subscription_failed' => 'Failed to Renew',
                'subscription_renew' => 'Successful Renewal',
                'subscription_upgrade' => 'Upgrade',
                'subscription_downgrade' => 'Downgrade',
            ),
            'Transactions' => array(
                'transaction' => 'Placed',
                'transaction_delete' => 'Deleted',
            ),
        );

        if (! $html)
            return $array;

        $list = '';
        foreach ($array as $name => $value) {
            $list .= '<optgroup label="' . $name . '">';
            foreach ($value as $item => $display) {
                if ($selected == $item) {
                    $list .= '<option value="' . $item . '" selected="selected">' . $display . '</option>';
                } else {
                    $list .= '<option value="' . $item . '">' . $display . '</option>';
                }
            }
            $list .= '</optgroup>';
        }

        return $list;
    }

}



