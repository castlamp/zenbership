<?php


/**
 *
 *
 * This class is included in this project
 * but belongs to the "Castlamp Framework".
 * While the overall project is copyrighted to
 * "Penn Foster", the contents of this file are
 * distributed under the "GPL3" license:
 * http://www.gnu.org/licenses/gpl.html
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
 * @version     v1.0
 *
 * Zenbership:
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

class transaction extends db
{


    public function get_total($id)
    {
        return $this->get_array("
            SELECT *
            FROM ppSD_cart_session_totals
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
    }

    public function get_order_items($id)
    {
        $items = array();
        $q = $this->run_query("
            SELECT
              ppSD_cart_items_complete.*,
              ppSD_products.name as productName
            FROM ppSD_cart_items_complete
            JOIN ppSD_products ON ppSD_products.id = ppSD_cart_items_complete.product_id
            WHERE ppSD_cart_items_complete.cart_session = '" . $this->mysql_clean($id) . "'
        ");
        while ($row = $q->fetch()) {
            $items[] = $row;
        }
        return $items;
    }

    public function get_transaction_by_user($id)
    {
        $sales = array();
        $total = 0;
        $subtotal = 0;
        $savings = 0;
        $refunds = 0;
        $transactions = 0;

        $STH = $this->run_query("
            SELECT
              ppSD_cart_sessions.id,
              ppSD_cart_sessions.date,
              ppSD_cart_sessions.date_completed,
              ppSD_cart_sessions.payment_gateway,
              ppSD_cart_sessions.gateway_order_id,
              ppSD_cart_sessions.state,
              ppSD_cart_sessions.country,
              ppSD_cart_sessions.member_id,
              ppSD_cart_sessions.member_type,
              ppSD_cart_session_totals.total,
              ppSD_cart_session_totals.subtotal,
              ppSD_cart_session_totals.savings,
              ppSD_cart_session_totals.refunds
            FROM `ppSD_cart_sessions`
            JOIN `ppSD_cart_session_totals`
              ON ppSD_cart_sessions.id = ppSD_cart_session_totals.id
            WHERE
              ppSD_cart_sessions.member_id = '" . $this->mysql_clean($id) . "' AND
              ppSD_cart_sessions.status = '1'
        ");

        while ($row =  $STH->fetch()) {
            $sales[] = $row;

            $transactions++;
            $total += $row['total'];
            $subtotal += $row['subtotal'];
            $savings += $row['savings'];
            $refunds += $row['refunds'];
        }

        return array(
            'data' => $sales,
            'transactions' => $transactions,
            'total' => $total,
            'subtotal' => $subtotal,
            'savings' => $savings,
            'refunds' => $refunds,
        );
    }


}

