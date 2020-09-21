<?php

/**
 * SMS Tools
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
class sms extends db
{

    protected $user_id, $user_type;
    protected $sms_id;
    protected $cell, $carrier;
    protected $sentvia;
    protected $provider_id;
    protected $media;

    /**
     * Prep an SMS for sending.
     */
    public function prep_sms($user_id, $user_type, $msg, $mediaUrl = '')
    {
        $this->user_id = $user_id;
        $this->user_type = $user_type;
        $this->media = $mediaUrl;

        if ($user_type == 'contact') {
            $contact = new contact;
            $q1      = $contact->get_contact($user_id);
        }
        else if ($user_type == 'rsvp') {
            $event                      = new event;
            $q1                         = $event->get_rsvp($user_id);
            $q1['data']['cell']         = $q1['cell'];
            $q1['data']['cell_carrier'] = $q1['cell_carrier'];
            $q1['data']['sms_optout']   = $q1['sms_optout'];
        }
        else if ($user_type == 'direct') {
            $q1 = array();
            $q1['data']['cell'] = $user_id;
        }
        else {
            $user = new user;
            $q1   = $user->get_user($user_id, '', '0');
        }

        $external = false;

        // Are we using a 3rd party plugin for SMS messaging?
        // ID example would be "twilio"
        $sms_external = $this->get_option('sms_plugin');

        if (! empty($sms_external)) {
            $check = PP_PATH . '/custom/plugins/' . $sms_external . '/functions/sendSms.php';
            if (file_exists($check)) {

                $this->sentvia = $sms_external;

                $external = true;
                $northamerica = false;

                $q1['data']['cell'] = preg_replace("/[^0-9]/", '', $q1['data']['cell']);

                // Some simple clean up checks for US/Canadian numbers
                if (substr($q1['data']['cell'], 0, 2) == '+1') {
                    $northamerica = true;
                    $q1['data']['cell'] = substr($q1['data']['cell'], 2);
                }

                else if (substr($q1['data']['cell'], 0, 1) == '1') {
                    $northamerica = true;
                    $q1['data']['cell'] = substr($q1['data']['cell'], 1);
                }

                if (strpos('+', $q1['data']['cell']) === false) {
                    if ($northamerica) {
                        $q1['data']['cell'] = ltrim($q1['data']['cell'], '+');
                        $q1['data']['cell'] = '+1' . $q1['data']['cell'];
                    } else {
                        $q1['data']['cell'] += 0; // remove zeros.
                        if (substr($q1['data']['cell'], 0, 1) != '+') {
                            $cur = $this->get_option('currency');
                            switch($cur) {
                                case 'GBP':
                                    $q1['data']['cell'] = '+44' . (ltrim($q1['data']['cell'], '44') + 0);
                                    break;
                                case 'AUD':
                                    $q1['data']['cell'] = '+61' . (ltrim($q1['data']['cell'], '61') + 0);
                                    break;
                                default:
                                    $q1['data']['cell'] = '+1' . (ltrim($q1['data']['cell'], '1') + 0);
                            }
                        }
                    }
                }

                $this->cell = $q1['data']['cell'];

                $msg = $this->replace($msg, $q1['data']);

                $plugin = new plugin($sms_external);
                $send = $plugin->load('sendSms');

                $send
                    ->setKeys(array(
                        'key' => $plugin->option('apikey'),
                        'token' => $plugin->option('apitoken'),
                    ))
                    ->setProviderPhoneNumber($plugin->option('apiphonenumber'))
                    ->setCell($this->cell)
                    ->setMsg($msg);

                if (! empty($this->media)) {
                    $send->setMedia($this->media);
                }

                $json = $send->send();

                // Never even had a chance... poor guy :(
                if (! empty($json['code'])) {
                    $this->save($msg, '0', $json['code'], $json['message']);

                    echo "0+++Failed: " . $sms_external . ' error code ' . $json['code'] . ' with message: ' . $json['message'];
                    exit;
                } else {
                    $this->provider_id = $json['id'];

                    return $this->save($msg);
                }
            }
        }

        // Not using external: use mail-to-sms services.
        if (! $external)
        {

            if (!empty($q1['data']['cell']) && !empty($q1['data']['cell_carrier']) && $q1['data']['cell_carrier'] != 'SMS Unavailable' && $q1['data']['sms_optout'] != '1') {
                $this->replace($msg, $q1['data']);

                $this->cell = $q1['data']['cell'];
                $this->carrier = $q1['data']['cell_carrier'];

                $send = $this->send_sms($msg);

                return '1';
            } else {
                return '0';

            }

        }

    }


    protected function replace($msg, array $data)
    {
        foreach ($data as $name => $value) {
            $msg = str_replace('%' . $name . '%', $value, $msg);
        }

        $basics = array(
            'pp_date'         => current_date(),
            'pp_url'          => PP_URL,
            'pp_company'      => COMPANY,
            'pp_company_url'  => $this->get_option('company_url'),
            'site_name'       => $this->get_option('site_name'),
            'logo'            => $this->get_logo(),
            'company_address' => $this->get_option('company_address'),
            'company_contact' => $this->get_option('company_contact'),
        );

        foreach ($basics as $name => $value) {
            $msg = str_replace("%$name%", $value, $msg);
        }

        return $msg;
    }


    public function carrier_list()
    {
        return array(
            'group:USA' => 'xxx',
            'Alltel' => '@message.alltel.com',
            'AT&T' => '@txt.att.net',
            'Boost Mobile' => '@myboostmobile.com',
            'Sprint' => '@messaging.sprintpcs.com',
            'T-Mobile' => '@tmomail.net',
            'US Cellular' => '@email.uscc.netm',
            'Verizon' => '@vtext.com',
            'Virgin Mobile USA' => '@vmobl.com',
            'Voyager Mobile' => '@text.voyagermobile.com',
            'West Central Wireless' => '@sms.wcc.net',
            'xxx:1' => '',
            'group:Canada' => 'xxx',
            'Bell Mobility' => '@txt.bell.ca',
            'Fido' => '@fido.ca',
            'Rogers Wireless' => '@pcs.rogers.com',
            'Telus' => '@msg.telus.com',
            'Virgin Mobile Canada' => '@vmobile.ca',
            'Wind Mobile' => '@txt.windmobile.ca',
            'xxx:2' => '',
            'group:Australia and New Zealand' => 'xxx',
            'Esendex' => '@echoemail.net',
            'Optus Zoo' => '@optusmobile.com.au',
            'Telecom New Zealand' => '@etxt.co.nz',
            'Vodafone New Zealand' => '@mtxt.co.nz',
            'xxx:3' => '',
            'group:Europe' => 'xxx',
            'O2' => '@o2online.de',
            'Orange' => '@orange.net',
            'T-Mobile Germany' => '@t-mobile-sms.de',
            'Virgin Mobile UK' => '@vxtras.com',
            'Vodafone Germany' => '@vodafone-sms.de',
            'Vodafone Spain' => '@vodafone.es',
            'xxx:4' => '',
            'group:Other' => 'xxx',
            'Claro' => '@sms.ctimovil.com.ar',
            'Movistar' => '@sms.movistar.net.ar',
            'xxx:5' => '',
            'SMS Unavailable' => 'xxx',
        );
    }


    /**
     * Send an SMS

     */
    public function send_sms($msg)
    {
        $fail = '0';
        if (empty($this->cell) || empty($this->carrier)) {
            return '0';

        } else {

            $carrier_list = $this->carrier_list();

            if (array_key_exists($this->carrier, $carrier_list)) {
                $fail = '0';
                $to = clean_phone($this->cell) . $carrier_list[$this->carrier];
            } else {
                $to = '';
                $fail = '1';
            }

            if ($fail == '1') {
                return '0';

            } else {

                $msg = stripslashes($msg);
                $opt = $this->get_option('sms_from');
                if (empty($opt)) {
                    $from = COMPANY . ' <' . COMPANY_EMAIL . '>';
                } else {
                    $from = $this->get_option('sms_from');
                }
                $headers = 'From: ' . $from . "\r\n";
                mail($to, '', $msg, $headers);

                // Save
                $this->sentvia = 'email';
                return $this->save($msg);
            }

        }

    }


    public function save($msg, $success = '1', $code = '', $message = '')
    {
        $this->sms_id = $this->insert("
            INSERT INTO `ppSD_saved_sms` (
                `date`,
                `msg`,
                `user_id`,
                `user_type`,
                `cell`,
                `carrier`,
                `sentvia`,
                `media`,
                `provider_id`,
                `success`,
                `code`,
                `message`
            )
            VALUES (
                '" . current_date() . "',
                '" . $this->mysql_clean($msg) . "',
                '" . $this->mysql_clean($this->user_id) . "',
                '" . $this->mysql_clean($this->user_type) . "',
                '" . $this->mysql_clean($this->cell) . "',
                '" . $this->mysql_clean($this->carrier) . "',
                '" . $this->mysql_clean($this->sentvia) . "',
                '" . $this->mysql_clean($this->media) . "',
                '" . $this->mysql_clean($this->provider_id) . "',
                '" . $this->mysql_clean($success) . "',
                '" . $this->mysql_clean($code) . "',
                '" . $this->mysql_clean($message) . "'
            )
        ");

        if ($success == '1') {
            $this->put_stats('sms_sent');
            $this->history();

            return '1';
        } else {
            echo "0+++SMS Failed: " . $code . ' with message: ' . $message . ' (' . $this->cell . ')';
            exit;
        }
    }


    public function history()
    {
        if (! empty($this->user_id)) {
            global $employee;
            $this->add_history('sms', $employee['id'], $this->user_id, '1', $this->sms_id);
        }
    }

}



