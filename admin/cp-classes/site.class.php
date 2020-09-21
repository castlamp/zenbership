<?php

/**
 * CMS
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
class site extends db
{

    private $string;
    private $category;
    private $data;
    private $url_string;
    public $final_page;

    /**
     * @param string $url_string Sent to index.php as GET:in
     */
    function __construct($url_string = '')
    {
        if (! empty($_GET['in'])) {
            $this->url_string = $_GET['in'];
        } else {
            $this->url_string = $url_string;
        }

        // Source tracking tools
        // This may redirect the user from the requested page!
        if (! empty($_GET['src'])) {
            $source = new source;
            $source->determine_source_by_origin($_GET['src']);
        }

        // Route?
        $route = $this->check_routes();
        if (! empty($route['found'])) {
            $path = PP_PATH . "/custom/plugins/" . $route['plugin'] . '/' . $route['resolve'];
            if (file_exists($path)) {
                include $path;
                exit;
            }
        }
        // Standard content page.
        if (!empty($url_string) && $url_string != 'home') {
            $this->string = $url_string;
            $this->break_up_url();
            $this->get_page();
        } else {
            $homepage = $this->get_option('startpage');
            if (! empty($homepage)) {
                $this->string   = $homepage;
                if (strpos($homepage, '/') !== false) {
                    $this->string = $homepage;
                    $this->break_up_url();
                    $this->get_page();
                } else {
                    $this->string   = $homepage;
                    $this->category = '';
                }
            } else {
                $this->string   = 'home';
                $this->category = '';
            }
            $this->process_page();
        }
    }

    /**
     * Return the final template
     */
    function __toString()
    {
        return (string)$this->final_page;

    }

    function get_page()
    {
        $add_where = '';
        if (! empty($this->category)) {
            $add_where = " AND `section`='" . $this->mysql_cleans($this->category) . "'";

        }
        $find = $this->get_array("
            SELECT
                *
            FROM
                `ppSD_content`
            WHERE
                `permalink`='" . $this->mysql_cleans($this->page) . "'
                $add_where
            LIMIT 1
        ");
        if (!empty($find['id'])) {
            $this->data = $find;

            // Section security
            if (!empty($find['section'])) {
                $content = new content;
                $section = $content->get_section($find['section']);
            } else {
                $secure = array();
                $section['secure'] = '0';
            }

            // Section security
            if ($section['secure'] == '1') {
                $session = new session;
                $ses     = $session->check_session();
                if ($ses['error'] == '1') {
                    $session->reject('login', 'L027', '');
                    exit;
                } else {
                    $user  = new user();
                    $check = $user->check_content_access_id($section['id'], $ses['member_id']);
                    if (empty($check)) {
                        $session->reject('login', 'L027', '');
                        exit;
                    }
                }
            } // Page Security
            else {

                if ($find['secure'] == '1') {
                    $session = new session;
                    $ses     = $session->check_session();
                    if ($ses['error'] == '1') {
                        $session->reject('login', 'L027', '');
                        exit;
                    } else {
                        $user  = new user();
                        $check = $user->check_content_access_id($find['id'], $ses['member_id']);
                        if (empty($check)) {
                            $session->reject('login', 'L027', '');
                            exit;
                        }
                    }
                }
            }

        } else {
            $this->string = '';
        }

        $this->process_page();

    }
    
    

    function process_page()
    {
        $changes = array();
        if ($this->string == 'home') {
            $get_template = 'homepage';
        } else {
            if ($this->data['type'] == 'redirect') {
                $this->data['url'] = str_replace('%pp%', PP_URL, $this->data['url']);
                header('Location: ' . $this->data['url']);
                exit;
            } else if ($this->data['type'] == 'page') {
                $get_template = 'content-' . $this->data['id'];
            } else {
                $changes['details'] = $this->get_error('W004');
                $get_template       = 'error';
            }
        }
        // Get template
        $this->final_page = new template($get_template, $changes, '1');
    }


    function check_routes()
    {
        if ( ! empty($this->url_string)) {
            $split = explode('/', $this->url_string);
            $query = $this->run_query("
                SELECT *
                FROM `ppSD_routes`
                WHERE `resolve` LIKE '/" . $this->mysql_cleans( trim($split['0'], '/') ) . "%'
            ");
            while ($row = $query->fetch()) {
                $row['resolve'] = trim($row['resolve'], '/');
                $exp = explode('/', $row['resolve']);
                if (sizeof($exp) > 1) {
                    if (! empty($split['1'])) {
                        $use_page = $row['path'];
                        $use_plugin = $row['plugin'];
                        // Break up route into components
                        $up = 0;
                        foreach ($exp as $item) {
                            if (substr($item, 0, 1) == '(') {
                                $clean = trim($item, '()');
                                $_GET[$clean] = htmlspecialchars($split[$up]);
                            }
                            $up++;
                        }
                        break;
                    } else {
                        continue;
                    }
                } else {
                    if (empty($split['1'])) {
                        $use_page = $row['path'];
                        $use_plugin = $row['plugin'];
                        break;
                    } else {
                        continue;
                    }
                }
            }
            if (! empty($use_page)) {
                return array(
                    'found' => '1',
                    'resolve' => $use_page,
                    'plugin' => $use_plugin,
                );
            } else {
                return array(
                    'found' => '0',
                );
            }
        } else {
            return array(
                'found' => '0',
            );
        }
    }


    function break_up_url()
    {
        // Break up URL
        $data = explode('/', $this->string);
        if (empty($data['1'])) {
            $this->category = $data['0'];
            $this->page     = '';
        } else {
            $content = new content;
            $section = $content->get_section('', $data['0']);
            if (!empty($section['id'])) {
                if ($section['secure'] == '1') {
                    $session = new session;
                    $ses     = $session->check_session();
                    if ($ses['error'] == '1') {
                        $session->reject('login', 'L027', '');
                        exit;
                    }
                }
                $this->category = $section['id'];
                // Page selected?
                if (!empty($data['1'])) {
                    $this->page = $data['1'];
                } else {
                    if (!empty($section['section_homepage'])) {
                        $this->page = $section['section_homepage'];
                    } else {
                        $this->page = '';
                    }
                }
            } else {
                $this->category = '';
                $this->page     = $data['0'];
            }
        }
    }

    /**
     * Get menu
     */
    function get_menu($id)
    {

    }

    /**
     * @param $menu_id -> Widget ID
     * @param $type    -> 1 = cms page, 2 = full url, 3 = onsite build url
     * @param $link    -> URL or the permalink
     * @param $submenu -> ID of ppSD_widgets_menu entry that is the primary entry.
     */
    function add_to_menu($menu_id, $title, $type, $link, $content_id, $submenu = '', $target = 'same', $position = '')
    {
        $check = $this->find_in_menu($menu_id, $type, $link);
        if ($check <= 0) {
            // widget_id	submenu	title	link	link_type	link_target	position
            $id = $this->insert("

                INSERT INTO `ppSD_widgets_menus` (

                  `widget_id`,

                  `submenu`,

                  `title`,

                  `link`,

                  `link_type`,

                  `link_target`,

                  `position`,

                  `content_id`

                )

                VALUES (

                  '" . $this->mysql_clean($menu_id) . "',

                  '" . $this->mysql_clean($submenu) . "',

                  '" . $this->mysql_clean($title) . "',

                  '" . $this->mysql_clean($link) . "',

                  '" . $this->mysql_clean($type) . "',

                  '" . $this->mysql_clean($target) . "',

                  '" . $this->mysql_clean($position) . "',

                  '" . $this->mysql_clean($content_id) . "'

                )

            ");
            // Re-cache
            $widget = new widget($menu_id, '1');

            // Return ID
            return $id;

        } else {
            return '0';

        }

    }

    /**
     * Finds which menus a permalink is found in.

     */
    function detect_menus($id)
    {
        $STH   = $this->run_query("

            SELECT *

            FROM `ppSD_widgets_menus`

            WHERE `link`='" . $this->mysql_clean($id) . "'

            GROUP BY `widget_id`

        ");
        $menus = array();
        while ($row = $STH->fetch()) {
            $menus[] = $row['widget_id'];

        }

        return $menus;

    }

    function find_in_menu($menu_id, $type, $link)
    {
        $find = $this->get_array("

            SELECT COUNT(*)

            FROM `ppSD_widgets_menus`

            WHERE

              `widget_id`='" . $this->mysql_clean($menu_id) . "' AND

              `link_type`='" . $this->mysql_clean($type) . "' AND

              `link`='" . $this->mysql_clean($link) . "'

        ");

        return $find['0'];

    }

    function delete_link_from_menu($menu, $link)
    {
        $del = $this->delete("

            DELETE FROM `ppSD_widgets_menus`

            WHERE

                `widget_id`='" . $this->mysql_clean($menu) . "' AND

                `link`='" . $this->mysql_clean($link) . "'

        ");
        // Re-cache
        $widget = new widget($menu, '1');

    }

}
