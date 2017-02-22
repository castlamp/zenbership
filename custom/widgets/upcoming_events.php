<?php
/**
 * Zenbership
 * Widget: upcoming events
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
    $display = '5';
} else {
    $display = $options['display'];
}
$where = '';
if (!empty($options['calendar'])) {
    $where .= "`calendar_id`='" . $this->mysql_clean($options['calendar']) . "'";
} else {
    $where .= "`calendar_id`='1'";
}
if (!empty($options['timeframe'])) {
    $timeframe = add_time_to_expires($options['timeframe']);
    $where .= " AND `starts`>='" . current_date() . "' AND `starts`<='" . $timeframe . "'";
} else {
    $timeframe = add_time_to_expires('000600000000');
}
$where .= " AND `starts`>='" . current_date() . "' AND `starts`<='" . $timeframe . "'";
$where .= " AND `status`='1'";
// Load cart object
$event = new event;
// Run the query
$STH = $this->run_query("
    SELECT `id`
    FROM `ppSD_events`
    WHERE
      " . $where . "
    ORDER BY `starts` ASC
    LIMIT " . $this->mysql_cleans($display) . "
");
while ($row = $STH->fetch()) {
    // Load the product options.
    $data = $event->get_event($row['id']);
    // Generate the template.
    $changes = $data['data'];
    $temp    = new template('widget-upcoming_events', $changes, '0');
    echo $temp;
}

?>