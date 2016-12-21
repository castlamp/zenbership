<?php
/**
 * Zenbership
 * Widget: member spotlight
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
if (!empty($options['criteria_id'])) {
    $criteria = new criteria($options['criteria_id']);
    $where .= $criteria->query;
}
// Load cart object
$user = new user;
// Run the query
$STH = $this->run_query("
    SELECT `id`
    FROM `ppSD_members`
    WHERE
      ppSD_members.listing_display='1'
      " . $where . "
    ORDER BY RAND()
    LIMIT " . $this->mysql_cleans($display) . "
");
while ($row = $STH->fetch()) {
    // Load the product options.
    $member = $user->get_user($row['id']);
    // Generate the template.
    $changes = $member['data'];
    $temp    = new template('widget-member_spotlight', $changes, '0');
    echo $temp;
}

?>