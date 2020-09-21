<?php


/**
 * Widget rendering
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
class widget extends db
{

    private $id;
    private $widget;
    private $final_content;
    private $user_data = array();
    private $recache;

    /**
     * Set up the output for this template.
     */
    function __construct($id, $recache = '0', $user_data = '')
    {
        $this->id = $id;
        $this->get_widget();
        $this->recache = $recache;
        $this->user_data = $user_data;

        if (empty($this->widget['id'])) {
            $this->process_error('W002');
        } else {
            if ($this->widget['active'] != '1') {
                $this->process_error('W001');
            }
            else {
                // Menu
                if ($this->widget['type'] == 'menu') {
                    $this->render_menu();
                } // HTML Block
                else if ($this->widget['type'] == 'html') {
                    $this->final_content = $this->widget['content'];
                } // Code
                else if ($this->widget['type'] == 'code' || $this->widget['type'] == 'plugin') {
                    $this->run_code();

                } // Code
                else if ($this->widget['type'] == 'upload_list') {
                    $this->upload_list();
                }
            }
        }
    }

    /**
     * Return the widget
     */
    function __toString()
    {
        return (string)$this->final_content;
    }

    /**
     * Process error.

     */
    function process_error($id)
    {
        $error               = $this->get_option($id);
        $this->final_content = '<span class="zen_widget_error">';
        $this->final_content .= str_replace('%id%', $this->id, $error);
        $this->final_content .= '</span>';

    }

    /**
     * Get navigation
     */
    function get_widget()
    {
        $widget       = $this->widget_data($this->id);
        $this->widget = $widget;
    }

    /**
     * Runs code

     */
    function run_code()
    {
        $path = PP_PATH . '/custom/widgets/' . $this->id . '.php';
        if (!file_exists($path)) {
            $this->process_error('W003');
        } else {
            $widget  = $this->widget;
            $options = $this->widget['options'];
            ob_start();
            include($path);
            $this->final_content = ob_get_contents();
            ob_end_clean();
        }
    }

    /**
     * Render Menu
     */
    function render_menu()
    {
        $cache = $this->get_cache($this->id);
        if ($cache['error'] != '1' && $this->recache != '1') {
            $this->final_content = $cache['data'];
        } else {
            // Classes?
            /*
            if (!empty($this->widget['add_class'])) {
                $add_class = $this->widget['add_class'];
            } else {
                $add_class = '';
            }
            */

            // Type
            if (! empty($this->widget['add_class'])) {
                $class = $this->widget['add_class'];
            } else if ($this->widget['menu_type'] == 'vertical') {
                $class = 'zen_menu_vertical zen_menu';
            } else {
                $class = 'zen_menu_horizontal zen_menu';
            }

            if (! empty($this->widget['add_id'])) {
                $useId = $this->widget['add_id'];
            } else {
                $useId = 'menu_' . $this->id;
            }


            $this->final_content = '<ul class="' . $class . '" id="' . $useId . '">';

            /*
            if ($this->widget['menu_type'] == 'vertical') {
                $this->final_content = '<ul class="zen_menu_vertical zen_menu ' . $add_class . '" id="menu_' . $this->id . '">';
            }
            else if ($this->widget['menu_type'] == 'horizontal') {
                $this->final_content = '<ul class="zen_menu_horizontal zen_menu ' . $add_class . '" id="menu_' . $this->id . '">';
            }
            */

            // Build the menu
            $STH = $this->run_query("
				SELECT *
				FROM `ppSD_widgets_menus`
				WHERE `widget_id`='" . $this->mysql_clean($this->id) . "'
				ORDER BY `position` ASC
			");
            while ($item = $STH->fetch()) {
                // Main entry
                $submenu = $this->submenu($item['id']);
                $this->final_content .= '<li';
                if (! empty($submenu)) {
                    $this->final_content .= ' class="zen_hoverable"';
                }
                $this->final_content .= ' id="menu_item_' . $this->widget['id'] . '_' . $item['id'] . '">';
                $this->final_content .= $this->build_link($item);
                $this->final_content .= $submenu;
                $this->final_content .= '</li>';
            }
            $this->final_content .= '</ul>';
            // Cache the data
            $cache = $this->add_cache($this->id, $this->final_content);

        }
    }

    /**
     * Upload List

     */
    function upload_list()
    {
        $session = new session;
        $ses     = $session->check_session();
        /**
         * Pagination
         */
        $add_get = array();
        $account = '';

        $skip = '0';
        $filters = array();
        $filters[] = '1||cp_only||neq||ppSD_uploads';

        //$filters = array( 'ppSD_uploads.cp_only' => array('scope' => 'AND', 'value' => '1', 'eq' => 'neq'),);
        if (empty($this->widget['options']['public_list']) || $this->widget['options']['public_list'] == 'No') {
            if (! empty($ses['member_id'])) {
                if (! empty($this->widget['options']['account_files']) && ($this->widget['options']['account_files'] == 'Yes' || $this->widget['options']['account_files'] == '1')) {
                    $user = new user;
                    $account = $user->get_member_account($ses['member_id']);

                    $filters['ppSD_uploads.item_id'] = array();
                    $filters['ppSD_uploads.item_id'][] = $ses['member_id'] . '||item_id||eq||ppSD_uploads';
                    $filters['ppSD_uploads.item_id'][] = $account . '||item_id||eq||ppSD_uploads';

                    //$filters['ppSD_uploads.item_id'][] = array('scope' => 'AND', 'value' => $ses['member_id'], 'eq' => 'eq');
                    //$filters['ppSD_uploads.item_id'][] = array('scope' => 'AND', 'value' => $account, 'eq' => 'eq');
                } else {
                    $filters[] = $ses['member_id'] . '||item_id||eq||ppSD_uploads';
                    //$filters['ppSD_uploads.item_id'] = array('scope' => 'AND', 'value' => $ses['member_id'], 'eq' => 'eq');
                }
            } else {
                $skip = '1';
            }
        } else {
            $filters[] = 'zen_public||item_id||eq||ppSD_uploads';
           // $filters['ppSD_uploads.item_id'] = array('scope' => 'AND', 'value' => 'zen_public', 'eq' => 'eq');
        }
        if (!empty($this->widget['options']['label'])) {
            $filters[] = $this->widget['options']['label'] . '||label||eq||ppSD_uploads';
            // $filters['ppSD_uploads.label'] = array('scope' => 'AND', 'value' => $this->widget['options']['label'], 'eq' => 'eq');
        }
        /*
        else {

            if (! empty($_GET['label'])) {

                $filters['ppSD_uploads.label'] = array('scope'=>'AND','value'=>$_GET['label'],'eq'=>'eq');

            }

        }
        */

        if ($skip != '1') {

            $join = array();
            // Get
            if (!empty($this->widget['options']['organize'])) {
                $_GET['organize'] = $this->widget['options']['organize'];
            }
            if (!empty($_GET['organize'])) {
                if ($_GET['organize'] == 'date_rl') {
                    $_GET['order'] = 'ppSD_uploads.date';
                    $_GET['dir']   = 'DESC';

                } else if ($_GET['organize'] == 'date_rf') {
                    $_GET['order'] = 'ppSD_uploads.date';
                    $_GET['dir']   = 'ASC';

                } else if ($_GET['organize'] == 'name_za') {
                    $_GET['order'] = 'ppSD_uploads.name';
                    $_GET['dir']   = 'DESC';

                } else {
                    $_GET['order'] = 'ppSD_uploads.name';
                    $_GET['dir']   = 'ASC';

                }
                $add_get['organize'] = $_GET['organize'];

            }
            if (empty($_GET['organize'])) {
                $_GET['organize'] = '';
            }
            if (empty($_GET['order'])) {
                $_GET['order'] = 'ppSD_uploads.date';
            }
            if (empty($_GET['dir'])) {
                $_GET['dir'] = 'DESC';
            }
            if (empty($_GET['display'])) {
                $_GET['display'] = '24';
            }

            $url = $this->current_url();
            $third = PP_URL;

            $paginate  = new pagination('ppSD_uploads', $url, $add_get, $_GET, $filters, $join);
            $formatted = '';
            $theme_url = $this->get_theme();
            // $url       = PP_URL;
            $uploads   = new uploads;

            // echo $paginate->query;
            $STH       = $this->run_query($paginate->query);
            while ($row = $STH->fetch()) {
                $changes = $uploads->get_upload($row['id']);
                $formatted .= new template('manage_uploads_entry', $changes, '0');
            }

            $output = '';

            $allow = '0';
            if (! empty($this->widget['options']['allow_uploads'])) {
                if ($this->widget['options']['allow_uploads'] == 'Yes' || $this->widget['options']['allow_uploads'] == '1') {
                    $allow = '1';
                }
            }

            if ( ! empty($this->widget['options']['prevent_uploads']) || $allow == '1' ) {
                $rand = uniqid();
                $output = <<<EOF
            <link href="{$theme_url['url']}/css/jquery.fileuploader.css" rel="stylesheet" type="text/css" />
            <script type="text/javascript" src="{$third}/pp-js/jquery.fileuploader.js"></script>
            <script type="text/javascript">
            $(document).ready(function() {
                var uploader = new qq.FileUploader({
                    element: document.getElementById('$rand'),
                    action: '{$third}/pp-functions/upload.php',
                    debug: false,
                    params: {
EOF;
                if (!empty($this->widget['options']['label'])) {
                    $output .= "label: '" . $this->widget['options']['label'] . "'";
                } else {
                    if (!empty($_GET['label'])) {
                        $output .= "label: '" . $_GET['label'] . "'";
                    }
                }
                if (! empty($account)) {
                    $output .= ",account: '" . sha1(md5($ses['member_id']) . md5($ses['id'])) . "'";
                }

                $output .= <<<EOF
                    }
                });
            });
            </script>
            <div id="$rand">
                <noscript><p>Please enable JavaScript to use file uploader.</p></noscript>
            </div>
            <p class="zen_small zen_gray zen_center zen_botmargin">Drag and drop files above to attach them to this note.</p>
EOF;
            }

            $output .= <<<EOF
            <div class="zen_catalog_description">
                <form action="{$url}" method="get">
                <div class="zen_section_right zen_gray zen_medium">
                    <select name="display" style="width:125px;" onChange="this.form.submit()">

EOF;
            $output .= '<option value="12"';
            if (empty($_GET['display']) || $_GET['display'] == '12') {
                $output .= " selected=\"selected\"";
            }
            $output .= '>12 per page</option>';
            $output .= '<option value="24"';
            if ($_GET['display'] == '24') {
                $output .= " selected=\"selected\"";
            }
            $output .= '>24 per page</option>';
            $output .= '<option value="48"';
            if ($_GET['display'] == '48') {
                $output .= " selected=\"selected\"";
            }
            $output .= '>48 per page</option>';
            $output .= '<option value="96"';
            if ($_GET['display'] == '96') {
                $output .= " selected=\"selected\"";
            }
            $output .= '>96 per page</option>';
            $output .= '</select> <select name="organize" style="width:175px;" onChange="this.form.submit()">';
            $output .= '<option value="name_az"';
            if (empty($_GET['organize']) || $_GET['organize'] == 'name_az') {
                $output .= ' selected="selected"';
            }
            $output .= '>Name (A-Z)</option>';
            $output .= '<option value="name_za"';
            if ($_GET['organize'] == 'name_zq') {
                $output .= ' selected="selected"';
            }
            $output .= '>Name (Z-A)</option>';
            $output .= '<option value="date_rf"';
            if ($_GET['organize'] == 'date_rf') {
                $output .= ' selected="selected"';
            }
            $output .= '>Date uploaded (Recent first)</option>';
            $output .= '<option value="date_rl"';
            if ($_GET['organize'] == 'date_rl') {
                $output .= ' selected="selected"';
            }
            $output .= '>Date uploaded (Recent last)</option>';
            $output .= '</select> <input type="submit" value="Sort" />';
            $output .= '</div>';
            $output .= '</form>';
            $output .= '<div class="zen_clear"></div>';
            $output .= '</div>';
            $output .= '<table cellspacing="0" cellpadding="0" border="0" class="zen_cart"><thead><tr>';
            $output .= '<th>Name</th>';
            $output .= '<th>Date</th>';
            $output .= '<th>Size</th>';
            $output .= '<th class="zen_right" width="85">Ext</th>';
            $output .= '</tr></thead><tbody>';
            $output .= $formatted;
            $output .= '</tbody></table>';

        } else {
            $output = $this->get_error('L001');
        }


        $this->final_content = $output;

    }

    /**
     * Build a menu link
     */
    function build_link($item)
    {

        // Allow for some basic member changes
        if (! empty($this->user_data)) {
            $item['title'] = str_replace('%id%', $this->user_data['id'], $item['title']);
            $item['title'] = str_replace('%username%', $this->user_data['username'], $item['title']);
            $item['title'] = str_replace('%first_name%', $this->user_data['first_name'], $item['title']);
            $item['title'] = str_replace('%last_name%', $this->user_data['last_name'], $item['title']);
            $item['title'] = str_replace('%joined%', format_date($this->user_data['joined']), $item['title']);
        } else {
            $item['title'] = str_replace('%id%', '', $item['title']);
            $item['title'] = str_replace('%username%', '', $item['title']);
            $item['title'] = str_replace('%first_name%', '', $item['title']);
            $item['title'] = str_replace('%last_name%', '', $item['title']);
            $item['title'] = str_replace('%joined%', '', $item['title']);
        }

        if (strpos($item['title'], '%items_in_cart%') !== false) {
            $cart = new cart;
            $total_items_in_cart = $cart->total_items_in_cart();
            $item['title'] = str_replace('%items_in_cart%', $total_items_in_cart, $item['title']);
        }
        if (strpos($item['title'], '%cart_total%') !== false) {
            $cart = new cart;
            $item['title'] = str_replace('%cart_total%', $cart->order['pricing']['format_total'], $item['title']);
        }

        $link = '<a href="';
        // Link type:
        // 1 = cms page, 2 = full url, 3 = onsite build url
        if ($item['link_type'] == '3') {
            $link .= PP_URL . '/' . $item['link'];

        } // CMS page
        // link = permalink name
        else if ($item['link_type'] == '1') {
            $content = new content;
            $getc = $content->get_content_link($item['content_id']);
            $link .= $getc;

        } // Full URL
        else {
            $item['link'] = str_replace('%pp_url%',PP_URL,$item['link']);
            $link .= $item['link'];
        }
        $link .= '"';
        if ($item['link_target'] == 'new') {
            $link .= ' target="_blank"';
        }
        $link .= '>';
        $link .= $item['title'];
        $link .= '</a>';
        return $link;
    }

    /**
     * Build a submenu
     */
    function submenu($main_id)
    {
        $found      = 0;
        $menu_inner = '';
        $STH        = $this->run_query("

   			SELECT * FROM `ppSD_widgets_menus`

   			WHERE `submenu`='" . $this->mysql_clean($main_id) . "'

   			ORDER BY `position` ASC

   		");
        while ($item = $STH->fetch()) {
            $found = 1;
            $menu_inner .= '<li>';
            $menu_inner .= $this->build_link($item);
            $menu_inner .= '</li>';

        }
        if ($found == 1) {
            $submenu = '<ul class="zen_submenu" style="display:none;">' . $menu_inner . '</ul>';
        } else {
            $submenu = '';
        }

        return $submenu;
    }

}



