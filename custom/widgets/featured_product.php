<?php
/**
 * Zenbership
 * Widget: feature product
 *
 * The program sends the following:
 *
 * @param array $widget
 *      pa($widget)
 * @param array $options
 *      pa($options)
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
 * @date        1/22/13 11:54 PM
 */
// Build the query
if (empty($options['display'])) {
    $display = '1';
} else {
    $display = $options['display'];
}
$where = '';
if (!empty($options['category'])) {
    $where .= " AND `category`='" . $this->mysql_cleans($options['category']) . "'";
}
// Load cart object
$cart = new cart;
// Run the query
$STH = $this->run_query("
    SELECT `id`,`members_only`
    FROM `ppSD_products`
    WHERE
      `featured`='1'
      " . $where . "
    ORDER BY RAND()
    LIMIT " . $this->mysql_cleans($display) . "
");
while ($row = $STH->fetch()) {

    $skip = 0;
    if ($row['members_only'] == '1') {
        $ses = new session();
        $session = $ses->check_session();
        if ($session['error'] == '1') {
            $skip = '1';
        }
    }

    if ($skip != '1') {
        // Load the product options.
        $product = $cart->get_product($row['id']);
        // Generate the template.
        $changes = $product['data'];
        $temp    = new template('widget-featured_product', $changes, '0');
        echo $temp;
    }
}