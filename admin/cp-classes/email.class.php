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
class email extends db
{

    private $email_id;
    private $template;
    private $user_id;
    private $user_type;
    private $data;
    private $changes;
    private $template_data;
    private $body;
    private $hold_user_email;
    private $fail;
    private $template_db;
    private $subject;
    private $attachment_content;
    private $mime_boundary;
    private $headers;
    private $msg_top;
    private $save;
    private $format;
    private $trackback;
    private $body_hold;
    private $from;
    private $to;
    private $cc;
    private $bcc;
    private $preview;
    private $return;
    private $email_theme;
    private $track_links;
    private $fail_reason;
    private $preview_content;
    private $bounced;
    private $programmaticId = null;
    private $programmatic = null;
    private $programmaticSend = null;
    private $vendorid = null;


    /**
     * $template -> template id, used for fixed mailings like reg complete
     * $user_id : contact, member, or RSVP ID
     * $type : contact, member, rsvp
     * $data : see array built in $db->email_data();
     *    Needs to include 'type' => 'email', 'scheduled_email', 'targeted_email', 'newsletter', 'template_email'
     * $changes : array of caller tag changes
     *
     * $attachments : These get uploaded and are tagged
     *   with the $email_id that is issued when composing.
     */
    function __construct($email_id, $user_id, $user_type, $data, $changes = '', $template = '', $preview = '0', $preview_content = '')
    {
        // Using programmatic email platform?
        $email_external = $this->get_option('email_plugin');
        if (! empty($email_external)) {
            $check = PP_PATH . '/custom/plugins/' . $email_external . '/functions/send.php';
            if (file_exists($check)) {
                $this->programmatic = new plugin($email_external);
                $this->programmaticSend = $this->programmatic->load('send');
                $this->programmaticId = $email_external;
            }
        }

        if (empty($email_id)) {
            $email_id = generate_id('random', '35');
        }
        $this->preview         = $preview;
        $this->template        = $template;
        $this->email_id        = $email_id;
        $this->user_id         = $user_id;
        $this->user_type       = $user_type;
        $this->data            = $data;
        $this->changes         = $changes;
        $this->preview_content = $preview_content;
        $this->bounced         = $this->get_option('bounced_email_inbox');
        if (!empty($data['track_links'])) {
            $this->track_links = $data['track_links'];
        } else {
            $this->track_links = '';
        }
        if (!empty($data['trackback'])) {
            $this->trackback = $data['trackback'];
        } else {
            $this->trackback = '';
        }
        // Email Theme
        $this->email_theme = $this->get_option('email_theme');
        if (empty($this->theme)) {
            $this->email_theme = "threefiveten";
        }
        // mime_boundary
        $this->mime_boundary = '==Multipart_Boundary_' . md5(time() . uniqid()) . "_" . $email_id;
        // Build the message
        $this->find_attach();
        $this->build_msg();
        $this->build_subject();
        $this->process_callers();
        $this->build_headers();
        if ($this->preview > 0) {
            $this->preview_email();
        } else {
            // Check hourly limits
            // and send if possible.
            $this->check_limits();
        }
    }

    /**
     * Return data
     */
    function __toString()
    {
        return (string)$this->return;
    }

    /**
     * Make sure that sending this email
     * will not conflict with hourly
     * limits. If it does, add it to the
     * queue for sending later.
     */
    function check_limits()
    {
        $hourly_limit = $this->get_option('emails_per_hour');
        if ($hourly_limit > 0) {
            $stat = new stats('emails_sent', 'get', 'hour');
            if ($stat >= $hourly_limit) {
                $temp_id = substr(uniqid() . md5(rand(100,99999999)),0,25);
                $connect = new connect($temp_id);
                $connect->save_mass_email($this->data);
                $connect->queue_email($this->user_id, $this->user_type, '1');
            } else {
                $this->send_email();
            }
        } else {
            $this->send_email();
        }
    }

    /**
     * Build an unformatted message
     */
    function build_msg()
    {
        $header = '';
        $footer = '';
        // Working with a templated (standardized) email

        if (! empty($this->template)) {

            // Get template data
            $get_db = $this->template_info($this->template);
            if (empty($get_db['template'])) {
                $this->fail        = '1';
                $this->fail_reason = 'Could not find template.';
            }
            else if ($get_db['status'] != 1) {
                $this->fail = '1';
                $this->fail_reason = 'Template is inactive.';
            }
            $this->template_db = $get_db;

            // Custom header?
            if ($get_db['custom'] == '1') {
                $header = '';
                $footer = '';
            } else {
                if (! empty($get_db['header_id']) && $get_db['header_id'] != 'html_header') {
                    $headerA = $this->template_info($get_db['header_id']);
                    if (!empty($headerA['content'])) {
                        $header .= $headerA['content'];
                    }
                } else {
                    $header = $this->get_template_content('html_header');
                }
                // Custom footer?
                if (!empty($get_db['footer_id']) && $get_db['footer_id'] != 'html_header') {
                    $footerA = $this->template_info($get_db['footer_id']);
                    if (!empty($footerA['content'])) {
                        $footer .= $footerA['content'];
                    }
                } else {
                    $footer = $this->get_template_content('html_footer');
                }
            }
            if ($this->preview > 0) {
                $body = $this->preview_content;
            } else {
                if (empty($get_db['content'])) {
                    $body = $this->get_template_content($this->template);
                } else {
                    $body = $get_db['content'];
                }
            }
            $this->save = $get_db['save'];
        } // Not a templated (standardized) email
        else {
            $header     = '';
            $footer     = '';
            $body       = $this->data['message'];
            $this->save = $this->data['save'];
        }

        // Format
        $this->body_hold = $header . $body . $footer;
        if (strlen($this->body_hold) == strlen(strip_tags($this->body_hold))) {
            $this->format = '0';
        } else {
            $this->format = '1';
        }

        // Trackback?
        if ($this->trackback == '1') {
            $body = $body . $this->put_trackback();
        }

        // Track Links?
        if ($this->track_links == '1') {
            // track_links
            $body = $this->add_track_links($body);
        }

        // Campaign Unsubscribe?
        if (!empty($this->data['campaign_id'])) {
            $body .= "\n\n" . $this->add_unsubscribe();
        }

        // Formatting
        if (is_null($this->programmatic)) {
            if (!empty($this->attachment_content)) {
                if ($this->format == '1') {
                    $this->msg_top .= '--' . $this->mime_boundary . PHP_EOL;
                    $this->msg_top .= "Content-Type: text/html; charset=iso-8859-1;" . PHP_EOL;
                    $this->msg_top .= "Content-Transfer-Encoding: 7bit;" . PHP_EOL . PHP_EOL;
                } else {
                    $this->msg_top .= '--' . $this->mime_boundary . PHP_EOL;
                    $this->msg_top .= "Content-Type: text/plain; charset=iso-8859-1;" . PHP_EOL;
                    $this->msg_top .= "Content-Transfer-Encoding: 7bit;" . PHP_EOL . PHP_EOL;
                }
            }
        }

        // Finalize
        $combine_body = $header . $body . $footer;
        if ($this->format == '1') {
            $combine_body = $this->create_inline_css($combine_body); // Create inline CSS
        }

        $this->body = $this->msg_top . $combine_body;
        if (is_null($this->programmatic) && ! empty($this->attachment_content)) {
            $this->body .= PHP_EOL . $this->attachment_content;
        }

        // Set data in case limits have been reached.
        // For templates, it would be blank unless
        // we set this.
        if (! empty($this->template)) {
            $this->data['message'] = $this->body;
        }

    }

    /**
     * From a physical file
     */
    function get_template_content($template)
    {
        $file    = PP_PATH . '/pp-templates/email/' . $this->email_theme . '/' . $template . '.html';
        $content = $this->get_file($file);

        return $content;
    }

    /**
     * Takes the HTML format email
     * and turns CSS rules into
     * inline rules so that they
     * work in most modern email clients.
     */
    function create_inline_css($html)
    {
        // Find CSS in body
        $pattern = '%<(link|style)(?=[^<>]*?(?:type="(text/css)"|>))(?=[^<>]*?(?:media="([^<>"]*)"|>))(?=[^<>]*?(?:href="(.*?)"|>))(?=[^<>]*(?:rel="([^<>"]*)"|>))(?:.*?</\1>|[^<>]*>)%si';
        preg_match_all($pattern, $html, $out);
        // Remove CSS and create
        // rule list.
        $cur = 0;
        $css = '';
        foreach ($out['4'] as $style_sheet) {
            if (strpos($style_sheet, 'http://') || strpos($style_sheet, 'https://')) {
                $style_path = $style_sheet;
            } else {
                $style_path = PP_PATH . '/pp-templates/email/' . $this->email_theme . '/' . $style_sheet;
            }
            $css .= file_get_contents($style_path);
            $css  = str_replace(array('-->', '<!--'), '', $css);
            $html = str_replace($out['0'][$cur], '', $html);
            $cur++;
        }
        // Create inline rules
        $inline_css      = new inlinecss($html, $css);
        $email_safe_html = $inline_css->convert();

        return $email_safe_html;
    }

    /**
     * Link click tracking
     */
    function add_track_links($content)
    {
        $cur = 0;
        if (!empty($this->data['attachment_id'])) {
            $mass_email_id = $this->data['attachment_id'];
        } else {
            $mass_email_id = '';
        }
        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
        preg_match_all("/$regexp/siU", $content, $body_links);
        foreach ($body_links['2'] as $alink) {
            $linkid   = generate_id('random', '32');
            $new_link = "<a href=\"" . PP_URL . "/pp-functions/etl.php?id=$linkid\">" . $body_links['3'][$cur] . "</a>";
            $content  = str_replace($body_links['0'][$cur], $new_link, $content);
            // DB entry
            $qsl = $this->insert("
   				INSERT INTO `ppSD_link_tracking` (
                    `id`,
                    `email_id`,
                    `campaign_id`,
                    `campaign_email_id`,
                    `clicked`,
                    `link`,
                    `user_id`,
                    `user_type`
   				)
   				VALUES (
                    '" . $this->mysql_clean($linkid) . "',
                    '" . $this->mysql_clean($this->email_id) . "',
                    '" . $this->mysql_clean($this->data['campaign_id']) . "',
                    '" . $this->mysql_clean($mass_email_id) . "',
                    '0',
                    '" . $this->mysql_clean($alink) . "',
                    '" . $this->mysql_clean($this->user_id) . "',
                    '" . $this->mysql_clean($this->user_type) . "'
   				)
   			");
            $cur++;
        }

        return $content;
    }

    /**
     * Build a subject line
     */
    function build_subject()
    {
        if (!empty($this->template)) {
            $this->subject = $this->template_db['subject'];
        } else {
            $this->subject = $this->data['subject'];
        }
        $this->subject = $this->basic_changes($this->subject);
    }

    /**
     * Process callers
     */
    function process_callers()
    {
        // User Data
        if (!empty($this->user_id)) {
            if ($this->user_type == 'contact') {
                $contact      = new contact;
                $contact_data = $contact->get_contact($this->user_id);
                //if ($contact_data['data']['email_optout'] != '0000-00-00 00:00:00') {
                //	$this->fail = '1'; echo "A";
                //} else {
                foreach ($contact_data['data'] as $item => $value) {
                    $this->body    = str_replace('%' . $item . '%', $value, $this->body);
                    $this->subject = str_replace('%' . $item . '%', $value, $this->subject);
                }
                //}
                if (!empty($contact_data['data']['email'])) {
                    $this->hold_user_email = $contact_data['data']['email'];
                }
            } else if ($this->user_type == 'member') {
                $user        = new user;
                $member_data = $user->get_user($this->user_id, '', '0');
                if (!empty($member_data['data']['id'])) {
                    //if ($member_data['data']['email_optout'] != '0000-00-00 00:00:00') {
                    //	$this->fail = '1'; echo "b";
                    //} else {
                    foreach ($member_data['data'] as $item => $value) {
                        $this->body    = str_replace('%' . $item . '%', $value, $this->body);
                        $this->subject = str_replace('%' . $item . '%', $value, $this->subject);
                    }
                    //}
                }
                if (!empty($member_data['data']['email'])) {
                    $this->hold_user_email = $member_data['data']['email'];
                }
            } else if ($this->user_type == 'rsvp') {
                $event     = new event;
                $rsvp_data = $event->get_rsvp($this->user_id);
                foreach ($rsvp_data as $item => $value) {
                    if (!is_array($value)) {
                        $this->body    = str_replace('%' . $item . '%', $value, $this->body);
                        $this->subject = str_replace('%' . $item . '%', $value, $this->subject);
                    }
                }
                if (!empty($rsvp_data['email'])) {
                    $this->hold_user_email = $rsvp_data['email'];
                }
            }
        }
        // Caller Tags
        if (!empty($this->changes)) {
            $this->make_changes($this->changes);
        }
        // Basic Changes
        $this->body = $this->basic_changes($this->body);
        /*
        // Custom Callers
        $q1 = $this->run_query("
            SELECT *
            FROM `ppSD_custom_callers`
        ");
        while ($row =  $STH->fetch($q1)) {
            $this->body = str_replace('%' . $row['caller'] . '%',$row['replacement'],$this->body);
            $this->subject = str_replace('%' . $row['caller'] . '%',$row['replacement'],$this->subject);
        }
        */
    }

    /**
     * Make custom changes.
     */
    function make_changes($array, $prefix = '')
    {
        foreach ($array as $item => $value) {
            if (is_array($value)) {
                $this->make_changes($value, $item . ':');
            } else {
                $use_item      = $prefix . $item;
                $use_value     = $value;
                $this->body    = str_replace('%' . $use_item . '%', $use_value, $this->body);
                $this->subject = str_replace('%' . $use_item . '%', $use_value, $this->subject);
            }
        }
    }

    /**
     * Basic changes
     */
    function basic_changes($content)
    {
        return $this->basic_email_changes($content);
    }

    /**
     * Build e-mail's headers
     */
    function build_headers()
    {
        // To... it's important
        if (empty($this->data['to']) && empty($this->hold_user_email)) {
            $this->fail        = '1';
            $this->fail_reason = 'No to email set.';
        } else {
            if (!empty($this->data['to'])) {
                $this->to = $this->data['to'];
            } else {
                $this->to = $this->hold_user_email;
            }
        }
        if (! empty($this->template)) {
            if (! empty($this->data['from'])) {
                $this->from = $this->data['from'];
            } else {
                $this->from = $this->template_db['from'];
            }
            // CC employees?
            if (!empty($this->template_db['cc'])) {
                $list     = $this->compile_email_list($this->template_db['cc']);
                $this->cc = $list;
            } else {
                $this->cc = '';
            }
            // BCC employees?
            if (!empty($this->template_db['bcc'])) {
                $list      = $this->compile_email_list($this->template_db['bcc']);
                $this->bcc = $list;
            } else {
                $this->bcc = '';
            }
            // BCC additional via email data?
            if (!empty($this->data['bcc'])) {
                $this->bcc .= $this->data['bcc'];
            } else {
                $this->bcc = '';
            }
            // $this->format = $this->template_db['format'];
            $this->trackback   = $this->template_db['track'];
            $this->track_links = $this->template_db['track_links'];
        } else {
            // From
            if (! empty($this->data['from'])) {
                $this->from = $this->data['from'];
            }
            // CC
            if (!empty($this->data['cc'])) {
                $this->cc = $this->data['cc'];
            } else {
                $this->cc = '';
            }
            // BCC
            if (!empty($this->data['bcc'])) {
                $this->bcc = $this->data['bcc'];
            } else {
                $this->bcc = '';
            }
            // Trackback
            if (!empty($this->data['trackback'])) {
                $this->trackback = $this->data['trackback'];
            } else {
                $this->trackback = '';
            }
            // Link Tracking
            if (!empty($this->data['track_links'])) {
                $this->track_links = $this->data['track_links'];
            } else {
                $this->track_links = '';
            }
        }
        if (empty($this->from)) {
            $this->from = $this->get_option('company_name') . " <" . $this->get_option('company_email') . ">";
        }

        // Comma is From?
        if (strpos($this->from, ',') !== false) {
            $from_exp = explode('<', $this->from);
            $format_from = '"' . trim($from_exp['0']) . '"';
            if (! empty($from_exp['1'])) {
                $format_from .= ' <' . $from_exp['1'];
            }
        } else {
            $format_from = $this->from;
        }

        $headers = "From: " . $format_from . PHP_EOL;
        $headers .= "Reply-To: " . $format_from . PHP_EOL;
        if (!empty($this->cc)) {
            $headers .= "CC: " . $this->cc . PHP_EOL;
        }
        if (!empty($this->bcc)) {
            $headers .= "BCC: " . $this->bcc . PHP_EOL;
        }
        if (!empty($this->bounced)) {
            $headers .= "Return-Path: " . $this->bounced . PHP_EOL;
            //$headers .= "Return-Receipt-To: " . $bounced . PHP_EOL;
        }
        $headers .= "Zenbership-ID: " . $this->email_id . PHP_EOL;
        // Some anti-spam stuff
        $headers .= "Message-ID: <message-on " . $this->email_id . "@" . $_SERVER['SERVER_NAME'] . ">" . PHP_EOL;
        $headers .= "X-Mailer: Zenbership Membership Software" . PHP_EOL;
        $headers .= "Organization: " . COMPANY . PHP_EOL;

        // Formatting
        if (empty($this->attachment_content)) {
            if ($this->format == '1') {
                $headers .= "MIME-Version: 1.0" . PHP_EOL;
                $headers .= "Content-Type: text/html; charset=iso-8859-1" . PHP_EOL;
            } else {
                $headers .= "MIME-Version: 1.0" . PHP_EOL;
                $headers .= "Content-Type: text/plain; charset=iso-8859-1" . PHP_EOL;
            }
        } else {
            $headers .= "MIME-Version: 1.0" . PHP_EOL;
            $headers .= "Content-Type: multipart/mixed; boundary=\"{$this->mime_boundary}\"" . PHP_EOL . PHP_EOL;
        }
        $this->headers = $headers;
    }

    /**
     * Compile a list of emails
     * for BCC and CC
     */
    function compile_email_list($list)
    {
        $complie_bcc = '';
        $exp_bcc     = explode(',', $list);
        foreach ($exp_bcc as $aBCC) {
            if (strpos($aBCC, 'staff:') !== false) {
                $employee_id = str_replace('staff:', '', $aBCC);
                $admin       = new admin;
                $emp_email   = $admin->get_email_from_id($employee_id);
                if (!empty($emp_email)) {
                    $complie_bcc .= ', ' . $emp_email;
                }
            } else {
                $complie_bcc .= ', ' . $aBCC;
            }
        }
        $complie_bcc = substr($complie_bcc, 2);

        return $complie_bcc;
    }

    /**
     * Add unsubscription link
     */
    function add_unsubscribe()
    {
        $unsub_link       = $this->get_error('E004');
        $view_online_link = $this->get_error('E005');
        $return           = "\n\n" . '<div id="unsubscribe_section">' . "\n";
        $return .= '<span class="bottom_link"><a href="' . PP_URL . '/pp-functions/unsubscribe.php?eid=' . $this->email_id . '&id=' . $this->data['campaign_id'] . '">' . $unsub_link . '</a></span>' . "\n";
        $return .= '<span class="bottom_link"><a href="' . PP_URL . '/pp-functions/view_email.php?eid=' . $this->email_id . '&id=' . $this->data['campaign_id'] . '">' . $view_online_link . '</a></span>' . "\n";
        $return .= '</div>';

        return $return;
    }

    /**
     * Find attachments
     */
    function find_attach()
    {
        $found_attach = 0;
        $atcontent    = '';
        // Add the attachments
        if (!empty($this->template)) {
            $where = "`email_id`='" . $this->mysql_cleans($this->template) . "'";
        } else if (!empty($this->data['attachment_id'])) {
            $where = "`email_id`='" . $this->mysql_cleans($this->data['attachment_id']) . "'";
        } else {
            $where = "`email_id`='" . $this->mysql_cleans($this->email_id) . "'";
        }
        $STH = $this->run_query("
			SELECT *
			FROM `ppSD_uploads`
			WHERE $where
		");
        while ($row = $STH->fetch()) {
            $file_path = PP_PATH . "/admin/sd-system/attachments/" . $row['filename'];
            $size      = filesize($file_path);
            if ($size > 0) {

                if (! is_null($this->programmatic)) {
                    $this->programmaticSend->setAttachments($file_path);
                } else {
                    $found_attach = 1;
                    $file         = fopen($file_path, 'rb');
                    $data         = fread($file, filesize($file_path));
                    fclose($file);
                    // Add to outgoing message
                    $data = chunk_split(base64_encode($data));
                    $atcontent .= "Content-Type: {\"application/octet-stream\"};" . PHP_EOL;
                    $atcontent .= "name=\"" . $row['name'] . "\"" . PHP_EOL;
                    $atcontent .= "Content-Disposition: attachment;" . PHP_EOL . " filename=\"" . $row['name'] . "\"" . PHP_EOL;
                    $atcontent .= "Content-Transfer-Encoding: base64" . PHP_EOL . PHP_EOL . $data . PHP_EOL . PHP_EOL;
                    $atcontent .= '--' . $this->mime_boundary . PHP_EOL;
                }

            }
        }
        if ($found_attach == '1') {
            $this->attachment_content = '--' . $this->mime_boundary . PHP_EOL;
            $this->attachment_content .= $atcontent;
        }
    }

    /**
     * Send an email
     */
    function send_email()
    {
        if ($this->fail != '1') {

            // External transactional email service being used?
            if (! is_null($this->programmatic)) {
                $this->programmaticSend
                    ->setKeys(array(
                        'key' => $this->programmatic->option('apikey'),
                        'domain' => $this->programmatic->option('domain'),
                    ))
                    ->setTag($this->programmatic->option('tag'))
                    ->setCampaign($this->programmatic->option('campaign'))
                    ->setTo($this->to)
                    ->setFrom($this->from)
                    ->setCc($this->cc)
                    ->setBcc($this->bcc)
                    ->setSubject($this->subject);

                if ($this->track_links == '1') {
                    $this->programmaticSend->setLinkTracking(true);
                }

                if ($this->trackback == '1') {
                    $this->programmaticSend->setTracking(true);
                }

                if ($this->format == '1') {
                    $this->programmaticSend->setHtmlMessage($this->body);
                } else {
                    $this->programmaticSend->setTextMessage($this->body);
                }

                $reply = $this->programmaticSend->send();

                $this->vendorid = $reply['id'];

                if (! empty($reply['error'])) {
                    echo "0+++" . $this->programmaticId . ' threw an error: ' . $reply['message'];
                    exit;

                    $this->fail = 1;
                    $this->fail_reason = $reply['code'] . ': ' . $reply['message'];
                }
            }

            // Using PHP Mail
            else {
                if (! empty($this->bounced)) {
                    mail($this->to, $this->subject, $this->body, $this->headers, '-f ' . $this->bounced);
                } else {
                    mail($this->to, $this->subject, $this->body, $this->headers, '-f ' . $this->get_option('company_email'));
                }
            }

            // Add note
            $note = new notes;
            $this_note = array(
                'user_id' => $this->user_id,
                'item_scope' => $this->user_type,
                'name' => $this->subject,
                'note' => $this->body,
                'label' => $note->get_label_from_code('emailout'),
            );
            $note->add_note($this_note);

            // Update activity?
            $this->update_activity();
            $this->history();

            // Set return
            $this->return = 'Sent';

            // Update hourly stats
            $this->put_stats('emails_sent');

            if (!empty($this->data['campaign_id'])) {
                $this->put_stats('emails_sent-' . $this->data['campaign_id']);
            }
        } else {
            $this->return = 'Failed';
        }

        $this->save_copy();
    }

    function update_activity()
    {
        if (!empty($this->data['update_activity']) && $this->data['update_activity'] == '1' && ! empty($this->user_id)) {
            $up = $this->update_next_action($this->user_id, $this->user_type);
        }
    }

    function history()
    {
        if (! empty($this->user_id)) {
            global $employee;
            $this->add_history('email', $employee['id'], $this->user_id, '1', $this->email_id, $this->subject);
        }
    }

    /**
     * Preview the email
     */
    function preview_email()
    {
        if ($this->preview == '2') {
            $this->return = $this->body;
        } else {
            $this->return = $this->body_hold;
        }
    }

    /**
     * Adds a trackback image to the email
     */
    function put_trackback()
    {
        if ($this->format == '1' && $this->preview != '1') {
            $this_trackback_id = generate_id('random', '27');
            $final_url         = PP_URL . "/pp-functions/etc.php?id=" . $this_trackback_id;
            if (!empty($this->data['attachment_id'])) {
                $mass_email_id = $this->data['attachment_id'];
            } else {
                $mass_email_id = '';
            }
            // DB entry
            $query = $this->insert("
				INSERT INTO `ppSD_email_trackback` (`id`,`email_id`,`date`,`status`,`user_id`,`user_type`,`campaign_id`,`campaign_saved_id`)
				VALUES ('$this_trackback_id','" . $this->mysql_clean($this->email_id) . "','" . current_date() . "','0','" . $this->user_id . "','" . $this->user_type . "','" . $this->data['campaign_id'] . "','" . $mass_email_id . "')
			");

            // Add to body
            return "<img src=\"$final_url\" width=\"0\" height=\"0\" border=\"0\" />";
        } else {
            return '';
        }
    }

    /**
     * Save the message.
     * If the 'save' option is set to
     * "Yes", the body will also be saved.
     */
    function save_copy()
    {
        if ($this->save == '1') {
            $final_save_b = $this->body;
        } else {
            $final_save_b = '';
        }
        $news_id = '';
        if (!empty($this->data['newsletter_id'])) {
            $news_id = $this->data['newsletter_id'];
        }
        $mass_id = '';
        if (!empty($this->data['mass_id'])) {
            $mass_id = $this->data['mass_id'];
        } else if (!empty($this->data['campaign_id'])) {
            $mass_id = $this->data['campaign_id'];
        }
        $use_type = 'template';
        if (!empty($this->data['type'])) {
            $use_type = $this->data['type'];
        }
        $qa1 = $this->insert("
   			INSERT INTO `ppSD_saved_emails` (
                `id`,
                `date`,
                `content`,
                `subject`,
                `to`,
                `user_id`,
                `user_type`,
                `format`,
                `from`,
                `newsletter`,
                `mass_email_id`,
                `cc`,
                `bcc`,
                `fail`,
                `fail_reason`,
                `sentvia`,
                `vendor_id`
   			)
   			VALUES (
   				'" . $this->mysql_clean($this->email_id) . "',
   				'" . current_date() . "',
   				'" . $this->mysql_clean($final_save_b) . "',
   				'" . $this->mysql_clean($this->subject) . "',
   				'" . $this->mysql_clean($this->to) . "',
   				'" . $this->mysql_clean($this->user_id) . "',
   				'" . $this->mysql_clean($this->user_type) . "',
   				'" . $this->mysql_clean($this->format) . "',
   				'" . $this->mysql_clean($this->from) . "',
   				'" . $this->mysql_clean($news_id) . "',
   				'" . $this->mysql_clean($mass_id) . "',
   				'" . $this->mysql_clean($this->cc) . "',
   				'" . $this->mysql_clean($this->bcc) . "',
   				'" . $this->mysql_clean($this->fail) . "',
   				'" . $this->mysql_clean($this->fail_reason) . "',
   				'" . $this->mysql_clean($this->programmaticId) . "',
   				'" . $this->mysql_clean($this->vendorid) . "'
            )
		");
    }

    /**
     * Sends through PHPMailer
     * https://github.com/Synchro/PHPMailer
     */
    function send_smtp()
    {

    }

}
