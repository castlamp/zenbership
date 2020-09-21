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
class content extends db
{

    public function get_name($id)
    {
        $q1 = $this->get_array("
            SELECT `name`
            FROM `ppSD_content`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
		");

        return $q1['name'];
    }


    public function get_all()
    {
        $q = $this->run_query("
            SELECT *
            FROM ppSD_content
            ORDER BY name asc
        ");
        $go = array();
        while ($row = $q->fetch()) {
            $go[] = $row;
        }
        return $go;
    }

    function get_permalink_name($id)
    {
        $q1 = $this->get_array("
            SELECT `permalink`
            FROM `ppSD_content`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
		");

        return $q1['permalink'];
    }

    function get_content_link($id)
    {
        $q1 = $this->get_array("
            SELECT `permalink`,`section`
            FROM `ppSD_content`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
		");
        return $this->build_permalink($q1['permalink'], $q1['section']);
    }

    /**
     * Get content data

     */
    function get_content($id, $basic = '0')
    {
        $q1 = $this->get_array("
            SELECT *
            FROM `ppSD_content`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
		");
        if (empty($q1['id'])) {
            $q1['error']         = '1';
            $q1['error_details'] = 'Could not find content.';

        } else {
            if ($basic != '1') {
                // Page?
                if ($q1['type'] == 'page') {
                    // Template
                    $template       = $this->template_data('content-' . $q1['id']);
                    $q1['template'] = $template;
                    // Full link
                    if (empty($q1['permalink'])) {
                        $q1['full_link'] = $q1['url'];
                    } else {
                        // $section_permalink = $this->get_permalink_name($template['section']);
                        $q1['full_link']   = $this->build_permalink($q1['permalink'], $q1['section']);
                    }
                    // Menus
                    $site        = new site;
                    $q1['menus'] = $site->detect_menus($q1['permalink']);

                }
                else if ($q1['type'] == 'section') {

                }
                else if ($q1['type'] == 'redirect') {
                    // Full link
                    $q1['full_link'] = $this->build_permalink($q1['permalink'], '');
                    // Menus
                    $site        = new site;
                    $q1['menus'] = $site->detect_menus($q1['permalink']);

                }
                else if ($q1['type'] == 'folder') {
                    // Full link
                    $q1['full_link'] = $q1['url'];
                    $q1['fieldsets'] = explode(',', $q1['additional_update_fieldsets']);
                    // Menus
                    $site        = new site;
                    $q1['menus'] = $site->detect_menus($q1['permalink']);

                }
            }
            // Error stuff
            $q1['error']         = '0';
            $q1['error_details'] = '';

        }

        return $q1;

    }

    function language_content($id, $lang ='')
    {
        $id = str_replace('content-', '', $id);
        if (empty($lang)) {
            $lang = $this->determine_language();
        }
        $get = $this->get_array("
	        SELECT `content`
	        FROM `ppSD_templates_lang`
	        WHERE
                `id`='" . $this->mysql_clean('content-' . $id) . "' AND
                `lang`='" . $this->mysql_clean($lang) . "'
	        LIMIT 1
	    ");
        return $get['content'];
    }

    // language_title
    function language_title($id, $lang ='')
    {
        $id = str_replace('content-', '', $id);
        if (empty($lang)) {
            $lang = $this->determine_language();
        }
        $get = $this->get_array("
	        SELECT `meta_title`
	        FROM `ppSD_templates_lang`
	        WHERE
                `id`='" . $this->mysql_clean('content-' . $id) . "' AND
                `lang`='" . $this->mysql_clean($lang) . "'
	        LIMIT 1
	    ");
        return $get['meta_title'];
    }

    /**
     * Get content access.
     */
    function get_content_access($id)
    {
        $q1 = $this->get_array("
		SELECT *
		FROM `ppSD_content_access`
		WHERE `id`='" . $this->mysql_clean($id) . "'
		LIMIT 1
	");
	if (! empty($q1['expires'])) {
	        $q1['format_expires'] = format_date($q1['expires']);
	        $q1['format_added']   = format_date($q1['added']);
        } else {
	        $q1['format_expires'] = '';
	        $q1['format_added']   = '';
        }
        return $q1;
    }


    function get_access_granter($id)
    {
        $q1 = $this->get_array("

			SELECT *

			FROM `ppSD_access_granters`

			WHERE `id`='" . $this->mysql_clean($id) . "'

			LIMIT 1

		");

        return $q1;

    }

    /**
     * Get a section data

     */
    function get_section($id = '', $permalink = '')
    {
        /*

        $q1 = $this->get_array("

            SELECT *

            FROM `ppSD_sections`

            WHERE `name`='" . $this->mysql_clean($id) . "'

            LIMIT 1

        ");

        */
        if (!empty($permalink)) {
            $where = "`permalink`='" . $this->mysql_clean($permalink) . "'";

        } else {
            $where = "`id`='" . $this->mysql_clean($id) . "'";

        }
        $q1 = $this->get_array("

            SELECT *

            FROM `ppSD_content`

            WHERE $where

            LIMIT 1

        ");
        if (empty($q1['name'])) {
            $q1['error']         = '1';
            $q1['error_details'] = 'Section not found';

        } else {
            $q1['error']         = '0';
            $q1['error_details'] = '';

        }

        return $q1;

    }

    /**
     * Check if a permalink exists.

     */
    function check_permalink($permalink)
    {
        $q1 = $this->get_array("

            SELECT `id`

            FROM `ppSD_content`

            WHERE `permalink`='" . $this->mysql_clean($permalink) . "'

            LIMIT 1

        ");
        if (!empty($q1['id'])) {
            return $q1['id'];

        } else {
            return '';

        }

    }

    /**
     * Format a permalink

     */
    function format_permalink($permalink)
    {
        // Only alpha-numeric, spaces, and underscores
        $permalink = preg_replace("/[^A-Za-z0-9- ]/", '', $permalink);
        $permalink = ucwords($permalink);
        // Replace spaces with dashes
        $permalink = str_replace(' ', '_', $permalink);

        return $permalink;

    }

    /**
     * For content that adds fields to the
     * update account page, we build a CSV
     * here of fieldset IDs.

     */
    function build_fieldset_csv($sets)
    {
        $csv = '';
        if (!empty($sets)) {
            foreach ($sets as $aSet) {
                $csv .= ',' . $aSet;

            }

            return ltrim($csv, ',');

        } else {
            return $csv;

        }

    }

}



