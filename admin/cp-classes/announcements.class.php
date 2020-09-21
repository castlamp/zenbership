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
class announcements extends db
{

    private $viewing_member_id;

    private $mark_to_seen;

    /**
     * Begin... (great comment eh?)

     */
    function __construct($member_id)
    {
        $this->viewing_member_id = $member_id;
        $this->mark_to_seen      = $this->get_option('announcement_views_to_seen');

    }

    /**
     * Get announcements to display

     */
    function find_announcements()
    {
        // Loop announcements
        $STH = $this->run_query("
			SELECT `id`,`ends`,`starts`
			FROM `ppSD_login_announcements`
			WHERE `active`='1'
			ORDER BY `starts` DESC
		");
        $formatted = '';
        // AND `ends`>'" . current_date() . "'
        while ($row = $STH->fetch()) {
            $good = 0;
            if ($row['ends'] == '1920-01-01 00:01:01' || $row['ends'] > current_date()) {
                if (current_date() >= $row['starts']) {
                    $good = 1;
                }
            }
            if ($good == '1') {
                $data = $this->get_announcement($row['id']);
                $formatted .= $this->format_announcement($data);
            }
        }
        return $formatted;

    }

    /**
     * Get an announcement

     */
    function get_announcement($id)
    {
        $go = $this->get_array("

   			SELECT * FROM `ppSD_login_announcements`

   			WHERE `id`='" . $this->mysql_clean($id) . "'

   			LIMIT 1

   		");

        return $go;

    }

    /**
     * Format an announcement

     */
    function format_announcement($data)
    {
        // Seen?
        $seen = $this->check_viewed($data['id']);
        if ($seen >= $this->mark_to_seen) {
            $class = 'zen_announce_viewed';

        } else {
            $class = 'zen_announce_new';
            $mark  = $this->marked_viewed($data['id']);

        }
        // HTML?
        if (strlen($data['content']) != strlen(strip_tags($data['content']))) {
            $final_content = $data['content'];

        } else {
            $final_content = nl2br($data['content']);

        }
        // Template
        $changes           = array(
            'title'        => $data['title'],
            'content'      => $final_content,
            'viewed_class' => $class,
            'start_date'   => format_date($data['starts']),
            'end_date'   => format_date($data['ends']),
        );

        $this_announcement = new template('manage_announcement_entry', $changes, '0');

        return $this_announcement;

    }

    /**
     * Check if a user has seen an
     * announcement

     */
    function check_viewed($id)
    {
        $q1 = $this->get_array("

			SELECT COUNT(*) FROM `ppSD_login_announcement_logs`

			WHERE `member_id`='" . $this->mysql_clean($this->viewing_member_id) . "' AND `announcement_id`='" . $this->mysql_clean($id) . "'

		");

        return $q1['0'];

    }

    /**
     * Mark an announcement viewed

     */
    function marked_viewed($id)
    {
        $q2 = $this->insert("

			INSERT INTO `ppSD_login_announcement_logs` (`announcement_id`,`date`,`member_id`)

			VALUES ('" . $this->mysql_clean($id) . "','" . current_date() . "','" . $this->mysql_clean($this->viewing_member_id) . "')

		");

    }

}



