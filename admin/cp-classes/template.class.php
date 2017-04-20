<?php

/**
 * Template rendering
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
class template extends db
{

    private $name;
    private $changes;
    private $theme;
    private $page_data;
    private $data;
    private $lang;
    private $final_content;
    private $pre_screened;
    private $site_name;
    private $use_link;
    protected $editing;
    protected $user_data;


    /**
     * Set up the output for this template.
     * @param array $page_data => Used for previewing.
     *      'content' => '',
     *      'title' => '',
     *      'desc' => '',
     *
     * @param $editing  1 = Doesn't render widgets, etc.
     *                  2 = Content page add. Rendered widgets, etc., but makes header/footer uneditable.
     */
    function __construct($name, $changes, $head_and_foot = '1', $user_id = '', $page_data = '', $security_confirmed = '0', $editing = '0')
    {
        $this->name = $name;
        $this->changes = $changes;
        // Secure Connection?
        $this->use_link = PP_URL;
        $this->use_link = str_replace('http://', '//', $this->use_link);
        $this->use_link = str_replace('https://', '//', $this->use_link);
        // Additional data...
        $this->page_data = $page_data;
        $this->site_name = $this->get_option('site_name');
        $this->pre_screened = $security_confirmed;
        $this->editing = $editing;
        $this->theme = $this->get_theme();
        $this->lang = $this->determine_language();
        $this->def_lang = $this->get_option('language');
        // On the off-chance that there is
        // GET information posted, like for
        // shopping cart sorting, we need
        // to sanitize it.
        $this->clean_get_elements();
        // Template information
        $this->data = $this->get_data();
        // Construct template
        if ($head_and_foot == '1') {
            $this->final_content .= $this->header();
        }

        $this->final_content .= $this->get_template();

        if ($head_and_foot == '1') {
            $this->final_content .= $this->footer();
        }
        if ($editing != '1' && $editing != '2') {
            $this->final_content = str_replace('contenteditable="false"','',$this->final_content);
        } else {
            //$this->final_content = htmlentities($this->final_content);
            $this->final_content = str_replace('</textarea>','---/textarea>',$this->final_content);
        }
        // User changes
        $found_user = '0';
        if (! empty($user_id)) {
            $user = new user;
            $datain = $user->get_user($user_id);
            $user_data = $datain['data'];
            $found_user = '1';
            $this->user_id = $user_id;
            $this->logged_in = '1';
            $this->username = $datain['data']['username'];
        } else {
            $session = new session;
            $ses = $session->check_session();
            if ($ses['error'] != '1') {
                $found_user = '1';
                $user = new user;
                $ut = $user->get_user($ses['member_id']);
                $user_data = $ut['data'];
                $account_data = $ut['account'];
                $this->user_id = $ses['member_id'];
                $this->logged_in = '1';
                //$this->user_data = $user_data;
            } else {
                // Template or page secure?
                if ($this->data['secure'] == '1' && $this->pre_screened != '1') {
                    $session->reject('login', 'L027', '');
                    exit;
                }
            }
        }
        if ($found_user == '1') {
            $this->user_data = $user_data;
            $this->final_content = $this->user_changes($this->final_content, $user_data);
            if (! empty($account_data)) {
                $this->final_content = $this->account_changes($this->final_content, $account_data);
            }
        }
        //$this->final_content = $this->block_changes($this->final_content);
        $this->final_content = $this->basic_changes($this->final_content);
        $this->final_content = $this->check_error_codes($this->final_content);


        if (ZEN_PERFORM_TESTS == '1' && ZEN_HIDE_DEBUG_TIME === false && $head_and_foot == '1') {
            $zen_performance_end = microtime(true);
            $dif = $zen_performance_end - ZEN_PERFORM_START;
            $this->final_content .= "<div style=\"z-index:9999999;position:fixed;bottom:0;right:0;padding:8px;background-color:#222;color:#fff;font-family:tahoma,arial;font-size:0.75em;border:1px solid #000;margin:0 8px 8px 0;\"><b>Load time: $dif seconds.</b></div>";
        }
    }


    /**
     * Return the final template
     */
    function __toString()
    {
        return (string)$this->final_content;
    }


    /**
     * Get basic data from the DB
     */
    function clean_get_elements()
    {

        if (!empty($_GET)) {
            foreach ($_GET as $name => $value) {
                if (!empty($value) && !is_array($value)) {
                    $_GET[$name] = htmlspecialchars($value, ENT_QUOTES);
                }
            }
        }
    }


    /**
     * Get basic data from the DB
     */
    function get_data()
    {

        if (empty($this->page_data)) {
            $array = $this->get_template_data($this->name);
            $this->get_array = $array;
        } else {
            // Custom template?
            if (!empty($this->page_data['custom_template'])) {
                $def = $this->blank_template($this->page_data['custom_template']);
            } else {
                $def = $this->blank_template();
            }
            $array = array(
                'title' => $this->page_data['title'],
                'desc' => $this->page_data['desc'],
                'content' => $def,
                'id' => 'na',
                'secure' => '0',
                'type' => 'x',
            );
            $this->get_array = $array;
        }
        return $array;
    }


    /**
     * Multiple language pages?
     */
    function find_language_content()
    {
        if (! empty($this->page_data['id'])) {
            $use = $this->page_data['id'];
        } else {
            $use = $this->get_array['id'];
        }
        $content = new content;
        $data = $content->language_content($use, $this->lang);
        return $data;
        /*
        if (!empty($this->page_data['id'])) {
            $use = $this->page_data['id'];
        } else {
            $use = $this->get_array['id'];
        }
        $get = $this->get_array("
	        SELECT `content`
	        FROM `ppSD_templates_lang`
	        WHERE
                `id`='" . $this->mysql_clean($use) . "' AND
                `lang`='" . $this->mysql_clean($this->lang) . "'
	        LIMIT 1
	    ");
        return $get['content'];
        */
    }


    /**
     * Get content data
     */
    function get_template_data($id)
    {
        // Moved to db.class
        $array = $this->template_data($id);
        return $array;
    }


    /**
     * Get the header
     */
    function header()
    {
        // Default
        $lit = PP_PATH . "/pp-templates/html/" . "/" . $this->theme['name'] . "/" . $this->lang . "/header.php";
        if (! file_exists($lit)) {
            $lit = PP_PATH . "/pp-templates/html/" . "/" . $this->theme['name'] . "/" . $this->def_lang . "/header.php";
        }
        ob_start();
        include($lit);
        $headcontent = ob_get_contents();
        ob_end_clean();
        $content = $this->make_changes($headcontent, $this->changes);

        // <link type="text/css" rel="stylesheet" media="all" href="%theme_url%/css/jquery_ui/jquery.ui.css"/>

        $load_jquery = $this->get_option('load_jquery');
        if ($load_jquery == '1') {
            $content = str_replace('</head>', '<link type="text/css" rel="stylesheet" media="all" href="' . $this->use_link . '/pp-templates/html/' . $this->theme['name'] . '/css/jquery_ui/jquery.ui.css" />' . "\n" . '<script src="' . $this->use_link . '/pp-js/jquery.js" type="text/javascript"></script>' . "\n" . '<script src="' . $this->use_link . '/pp-js/jquery.ui.js" type="text/javascript"></script>' . "\n" . '<script type="text/javascript">var zen_url=\'' . $this->use_link . '\';var zen_theme=\'' . $this->theme['name'] . '\';var check_pwd_strength=\'' . $this->get_option('required_password_strength') . '\';</script>' . "\n" . '</head>', $content);
        } else {
            $content = str_replace('</head>', '<script type="text/javascript">var zen_url=\'' . $this->use_link . '\';var zen_theme=\'' . $this->theme['name'] . '\';var check_pwd_strength=\'' . $this->get_option('required_password_strength') . '\';</script>' . "\n" . '</head>', $content);
        }

        //$content = str_replace('</head>', '<link type="text/css" rel="stylesheet" media="all" href="' . $this->use_link . '/pp-templates/html/' . $this->theme['name'] . '/css/jquery_ui/jquery.ui.css" />' . "\n" . '<script src="' . $this->use_link . '/pp-js/jquery.js" type="text/javascript"></script>' . "\n" . '<script src="' . $this->use_link . '/pp-js/jquery.ui.js" type="text/javascript"></script>' . "\n" . '<script type="text/javascript">var zen_url=\'' . $this->use_link . '\';var zen_theme=\'' . $this->theme['name'] . '\';var check_pwd_strength=\'' . $this->get_option('required_password_strength') . '\';</script>' . "\n" . '</head>', $content);
        return $content . "\n\n" . '<!--END:HEADER:ZEN513KESS-->' . "\n\n";
    }


    /**
     * Get the default template for
     * custom pages.
     * @param string $template_id If this template is using a custom default template, this is the id.
     */
    function blank_template($template_id = '')
    {

        if (! empty($template_id)) {
            $data = $this->get_template_data($template_id);
            $temp_content = $data['content'];
        } else {
            /*
            $template_id = 'default_page';
            $lit = PP_PATH . "/pp-templates/html/" . $this->theme['name'] . "/" . $this->lang . "/" . $template_id . ".php";
            if (!file_exists($lit)) {
                $lit = PP_PATH . "/pp-templates/html/" . "/" . $this->theme['name'] . "/" . $this->def_lang . "/" . $template_id . ".php";
            }
            ob_start();
            include($lit);
            $temp_content = ob_get_contents();
            ob_end_clean();
            */
        }
        // Language substitute?
        $find = 1;
        if ($this->lang != $this->def_lang) {
            $language_template = $this->find_language_content();
            if (!empty($language_template)) {
                $use_data = $language_template;
                $find = 0;
            }
        }
        if ($find == 1) {
            if (!empty($this->page_data['template'])) {
                $use_data = $this->page_data['template'];
            } else {
                $use_data = $this->get_array['content'];
            }
        }
        // $temp_content = str_replace('%place_content%', $use_data, $temp_content);
        $temp_content = $use_data;
        $content = $this->make_changes($temp_content, $this->changes);
        return $content;
    }


    /**
     * Get the footer
     */
    function footer()
    {

        $lit = PP_PATH . "/pp-templates/html/" . $this->theme['name'] . "/" . $this->lang . "/footer.php";
        if (!file_exists($lit)) {
            $lit = PP_PATH . "/pp-templates/html/" . "/" . $this->theme['name'] . "/" . $this->def_lang . "/footer.php";
        }
        ob_start();
        include($lit);
        $footcontent = ob_get_contents();
        ob_end_clean();
        $content = $this->make_changes($footcontent, $this->changes);
        // Google Analytics
        $goog = $this->get_option('google_analytics');
        $goog_code = '';
        if (!empty($goog)) {
            $goog_code = "<script type=\"text/javascript\">var _gaq = _gaq || [];_gaq.push(['_setAccount', '" . $goog . "']); _gaq.push(['_trackPageview']); (function() { var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s); })();</script>";
            // $content = str_replace('</body>', $goog_code . '</body>', $content);
        }
        $content = str_replace('</body>','<script src="' . $this->use_link . '/pp-js/general.js" type="text/javascript"></script>' . "\n" . '<script src="' . $this->use_link . '/pp-js/form_functions.js" type="text/javascript"></script>' . $goog_code . '</body>',$content);

        return "\n\n" . '<!--START:FOOTER:ZEN513KESS-->' . "\n\n" . $content;
    }


    /**
     * Get the template
     */
    function get_template()
    {

        if (! empty($this->page_data) || $this->get_array['type'] == '4') {
            // Template wrapper.
            // Already done for previews.
            if ($this->get_array['type'] == '4') {
                $tempcontent = $this->blank_template($this->get_array['custom_template']);
            } else {
                $tempcontent = $this->get_array['content'];
            }
        } else {

            // For loading just the header/footer
            if ($this->name == 'header') {
                $tempcontent = $this->header();
            }
            else if ($this->name == 'footer') {
                $tempcontent = $this->footer();
            }
            else {
                // If a full path was submitted, this is
                // most likely a template for a plugin.
                if (strpos($this->name, PP_PATH) !== false) {
                    $lit = $this->name;
                    ob_start();
                    include($lit);
                    $tempcontent = ob_get_contents();
                    ob_end_clean();
                } else {
                    $lit = PP_PATH . "/pp-templates/html/" . $this->theme['name'] . "/" . $this->lang . "/" . $this->name . ".php";
                    if (!file_exists($lit)) {
                        $lit = PP_PATH . "/pp-templates/html/" . "/" . $this->theme['name'] . "/" . $this->def_lang . "/" . $this->name . ".php";
                    }
                    if ($this->editing == '1') {
                        $tempcontent = file_get_contents($lit);
                    } else {
                        ob_start();
                        include($lit);
                        $tempcontent = ob_get_contents();
                        ob_end_clean();
                    }
                }
            }
        }
        $tempcontent = $this->make_changes($tempcontent, $this->changes);
        // Encrypt with crappy Javascript
        // "encryption"?
        if (! empty($this->data['encrypt'])) {
            $key = uniqid();
            $tempcontent = "<script src=\"" . PP_URL . "/pp-js/bo.js\" type=\"text/javascript\"></script><script type=\"text/javascript\">var kk='" . $key . "';var ss = '" . base64_encode($this->rc4($key, $tempcontent)) . "';var qq=decode64(ss);var rr=rc4(kk,qq);document.write(rr);</script>";
        }
        return $tempcontent;
    }


    /**
     * Used for PHP to Javascript
     * "encryption".
     *
     * @param $key
     * @param $str
     *
     * @return string
     */
    function rc4($key, $str) {
        $s = array();
        for ($i = 0; $i < 256; $i++) {
            $s[$i] = $i;
        }
        $j = 0;
        for ($i = 0; $i < 256; $i++) {
            $j = ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
            $x = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $x;
        }
        $i = 0;
        $j = 0;
        $res = '';
        for ($y = 0; $y < strlen($str); $y++) {
            $i = ($i + 1) % 256;
            $j = ($j + $s[$i]) % 256;
            $x = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $x;
            $res .= $str[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
        }
        return $res;
    }

    /**
     * Make changes to a template
     */
    function make_changes($content, $changes, $prefix = '')
    {

        if (!empty($changes)) {
            foreach ($changes as $item => $value) {
                if (is_array($value)) {
                    $content = $this->make_changes($content, $value, $item . ':');
                } else {
                    $use_item = $prefix . $item;
                    $use_value = $value;
                    $content = str_replace('%' . $use_item . '%', $use_value, $content);
                }
            }
        }
        return $content;
    }


    /**
     * Check if there is an error code to display
     */
    function check_error_codes($content)
    {

        if (!empty($_GET['code']) || !empty($this->changes['ecode'])) {
            if (!empty($this->changes['ecode'])) {
                $ecode = $this->changes['ecode'];
            } else {
                $ecode = $_GET['code'];
            }
            $get_code = $this->get_error($ecode);
            if (!empty($get_code)) {
                $content = str_replace('%error_code%', '<div id="zen_error_code">' . $get_code . '</div>', $content);
            } else {
                $content = str_replace('%error_code%', '', $content);
            }
        } else {
            $content = str_replace('%error_code%', '', $content);
        }
        if (!empty($_GET['scode']) || !empty($this->changes['scode'])) {
            if (!empty($this->changes['scode'])) {
                $scode = $this->changes['scode'];
            } else {
                $scode = $_GET['scode'];
            }
            $get_code = $this->get_error($scode);
            if (!empty($get_code)) {
                $content = str_replace('%success_code%', '<div id="zen_success_code">' . $get_code . '</div>', $content);
            } else {
                $content = str_replace('%success_code%', '', $content);
            }
        } else {
            $content = str_replace('%success_code%', '', $content);
        }
        return $content;
    }


    /**
     * User Changes
     */
    function user_changes($content, $user_data)
    {
        foreach ($user_data as $name => $value) {
            $content = str_replace("%$name%", $value, $content);
        }
        return $content;
    }

    /**
     * Account Changes
     */
    function account_changes($content, $account_data)
    {
        if (! empty($account_data)) {
            foreach ($account_data as $name => $value) {
                if (! is_array($value)) {
                    $content = str_replace("%account:$name%", $value, $content);
                }
            }
        }
        return $content;
    }


    /**
     * Basic changes
     */
    function basic_changes($content)
    {

        // Widgets
        if ($this->editing != '1') {
            $content = $this->get_widgets($content);
            $content = $this->get_includes($content);
            $content = $this->get_options($content);
        }
        // Some basics
        // $secure_url = str_replace('http://', 'https://', PP_URL);
        $secure_url = $this->getSecureLink();
        $home_link = $this->get_option('homepage');
        $final_home = PP_URL . '/' . trim($home_link, '/');
        // Array of changes
        if (! empty($_GET['query'])) { $query = htmlspecialchars($_GET['query']); }
        else { $query = ''; }
        // Array of changes
        $basics = array(
            'pp_date' => current_date(),
            'home_link' => $final_home,
            'pp_company' => COMPANY,
            'pp_company_url' => $this->get_option('company_url'),
            'meta_title' => $this->get_title(),
            'page_title' => $this->get_title(true),
            'meta_desc' => $this->data['desc'],
            'pp_breadcrumbs' => $this->get_crumbs(),
            'template_name' => $this->data['id'],
            'site_name' => $this->site_name,
            'query' => $query,
            'company_address' => $this->get_option('company_address'),
            'company_contact' => $this->get_option('company_contact'),
            'pp_secure_url' => $secure_url,
            'theme' => $this->theme['name'],
            'theme_url' => $this->theme['url'],
            'pp_url' => $this->use_link,
            'logo' => $this->get_logo(),
            'lang' => $this->lang,
        );
        foreach ($basics as $name => $value) {
            $content = str_replace("%$name%", $value, $content);
        }
        // Cart Calls
        if (strpos($content, '%items_in_cart%') !== false) {
            $cart = new cart;
            $total_items_in_cart = $cart->total_items_in_cart();
            $content = str_replace('%items_in_cart%', $total_items_in_cart, $content);
        }
        if (strpos($content, '%cart_total%') !== false) {
            $cart = new cart;
            $content = str_replace('%cart_total%', $cart->order['pricing']['format_total'], $content);
        }

        return $content;
    }


    /**
     * Block Changes
     * {start:FIELDNAME}
     *   If FIELDNAME isn't empty,
     *   leave it. Otherwise delete
     *   the block.
     * {end:FIELDNAME}
     * $str = preg_replace('#(<a.*).*?(</a>)#', '$1$2', $str);
     */
    function block_changes($changes)
    {

        return $changes;
    }


    /**
     * Breadcrumbs
     * Site Name / Section / Page
     */
    function get_crumbs()
    {
        $crumb = "<a href=\"" . PP_URL . "\">" . $this->site_name . "</a>";
        if (! empty($this->changes['breadcrumbs'])) {
            $crumb .= ' / ' . $this->changes['breadcrumbs'];
        } else {
            if (! empty($this->data['section'])) {
                $crumb .= $this->get_section_url();
            }
        }
        if (empty($this->changes['title'])) {
            $crumb .= " / <a href=\"" . $this->current_url() . "\">" . $this->data['title'] . "</a>";
        }
        return $crumb;
    }


    /**
     * Page Title
     * Site Name / Section / Page | Company Name
     */
    function get_title($skip_padding = false)
    {
        if (! empty($this->changes['meta_title'])) {
            return $this->changes['meta_title'];
        }
        else if (! empty($this->data['meta_title'])) {
            return $this->data['meta_title'];
        }
        else if ($this->lang != $this->def_lang) {
            $content = new content;
            return $content->language_title($this->data['id'], $this->lang);
        }
        else {
            if ($skip_padding) {
                return $this->data['title'];
            }

            $title = $this->site_name;

            if (!empty($this->data['section'])) {
                $sectitle = strip_tags($this->get_section_url()); // strip_tags()
                if (!empty($sectitle)) {
                    $title .= $sectitle;
                }
            }

            $title .= " / ";
            $title .= $this->data['title'];
            $title .= " | " . COMPANY;

            return $title;
        }
    }


    /**
     * Get Section
     */
    function get_section_url($id = '')
    {

        $add_url = '';
        if (!empty($id)) {
            $use_id = $id;
        } else {
            $use_id = $this->data['section'];
        }
        $q = $this->get_array("
	 		SELECT
	 		    ppSD_content.name,
	 		    ppSD_content.permalink_clean,
	 		    ppSD_content.permalink,
	 		    ppSD_content.section
	 		FROM
	 		    `ppSD_content`
	 		WHERE
	 		    `id`='" . $this->mysql_clean($use_id) . "'
	 		LIMIT 1
	 	");
        if (!empty($q)) {
            //if (! empty($q['subsection'])) {
            if (!empty($q['section'])) {
                $add_url .= $this->get_section_url($q['section']);
            }
            // $put_url = PP_URL . "/" . $q['url'];
            $put_url = $this->build_permalink($this->data['title'], $q['permalink']);
            $add_url .= " / <a href=\"" . $put_url . "\">" . $q['permalink_clean'] . "</a>";
            return $add_url;
        } else {
            return '';
        }
    }


    /**
     * Find widgets
     */
    function get_options($content)
    {
        preg_match_all('/\{\[(.*?)\]\}/i', $content, $options);

        foreach ($options['1'] as $aOption) {
            $value = $this->get_option($aOption);
            $content = str_replace('{[' . $aOption . ']}', $value, $content);
        }
        return $content;
    }



    /**
     * Run Include
     */
    function get_includes($content)
    {
        preg_match_all('/\{\!(.*?)\!\}/i', $content, $widgets);
        foreach ($widgets['1'] as $aWidget) {
            ob_start();
            include($aWidget);
            $include_content = ob_get_contents();
            ob_end_clean();
            $content = str_replace('{!' . $aWidget . '!}', $include_content, $content);
        }
        return $content;
    }

    /**
     * Find widgets
     */
    function get_widgets($content)
    {

        preg_match_all('/\{\-(.*?)\-\}/i', $content, $widgets);
        foreach ($widgets['1'] as $aWidget) {
            // Form
            if (strpos($aWidget, 'form_') !== false) {
                $aWidget = str_replace('form_', '', $aWidget);
                // Get the form
                $data = $this->get_basic_form($aWidget);
                $ignore = 0;
                if ($data['type'] == 'contact' || $data['type'] == 'dependency') {
                    $send_id = 'register-' . $aWidget . '-1';
                }
                else if ($data['type'] == 'update') {
                    $send_id = 'register-' . $aWidget;
                    if ($this->logged_in != 1) {
                        $ignore = 1;
                    }
                }
                else {
                    $send_id = $aWidget;
                }
                // Proceed...
                if ($ignore != 1) {
                    $field = new field('', '1');
                    $form = $field->generate_form($send_id, $this->user_data);
                    if (empty($form)) {
                        $form = $field->generate_form($send_id . '-1', $this->user_data);
                    }
                    $content = str_replace('{-form_' . $aWidget . '-}', $form, $content);
                } else {
                    $content = str_replace('{-form_' . $aWidget . '-}', '', $content);
                }
            }
            else if (strpos($aWidget, 'news_') !== false) {
                $aWidget = str_replace('news_', '', $aWidget);
                // Zero means region defaults will take over.
                $page = (! empty($_GET['page'])) ? $_GET['page'] : 0;
                $display = (! empty($_GET['display'])) ? $_GET['display'] : 10;
                $search = (! empty($_GET['search'])) ? $_GET['search'] : '';
                $newsFeed = news($aWidget, $page, $display, $search);
                $content = str_replace('{-news_' . $aWidget . '-}', $newsFeed, $content);
            }
             // Widget
            else {
                $widget = new widget($aWidget, '0', $this->user_data);
                $content = str_replace('{-' . $aWidget . '-}', $widget, $content);
            }
        }
        return $content;
    }

}
