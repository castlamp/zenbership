<?php

/**
 * Social Media Functions
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
class socialmedia extends db
{

    protected $twitter_consumer_key, $twitter_secret, $twitter_oauth_token, $twitter_oauth_secret, $twitter_handle;

    protected $facebook_app_id, $facebook_app_secret, $facebook, $token, $code, $post_login_url, $fb_user_id;

    protected $facebook_graph = 'https://graph.facebook.com';

    protected $employee, $facebook_confirmed = '0';

    function __construct($type = 'twitter', $fb_code = '')
    {
        global $employee;
        $this->employee             = $employee;
        $this->twitter_handle       = $this->get_option('twitter_handle');
        $this->twitter_consumer_key = $this->get_option('twitter_consumer_key');
        $this->twitter_secret       = $this->get_option('twitter_secret');
        $this->twitter_oauth_token  = $this->get_option('twitter_oauth_token');
        $this->twitter_oauth_secret = $this->get_option('twitter_oauth_secret');
        $this->facebook_profile_url = $this->get_option('facebook_url');
        $this->facebook_app_id      = $this->get_option('facebook_app_id');
        $this->facebook_app_secret  = $this->get_option('facebook_app_secret');
        $this->post_login_url       = PP_URL . '/admin/index.php?l=social_media_facebook';
        $this->fb_code              = $fb_code;

    }

    /**
     * ----------------------------
     * Facebook Functions

     */
    function confirm_fb_setup()
    {
        if (!empty($this->facebook_profile_url) && !empty($this->facebook_app_secret) && !empty($this->facebook_app_id)) {
            if ($this->employee['permissions']['admin'] == '1' || $this->employee['permissions']['scopes']['facebook'] == 'all') {
                return array('error' => '0');
            } else {
                return array('error' => '1', 'error_message' => 'You are not permitted to perform this task.');
            }
        } else {
            return array('error' => '1', 'error_message' => 'Facebook application has not been set up.');

        }
    }

    function fb_connect()
    {
        /*
        // Feature removed until further testing can
        // take place.
        $confirm = $this->confirm_fb_setup();
        if ($confirm['error'] != '1') {
            $this->facebook = new Facebook(array(
                'appId'  => $this->facebook_app_id,
                'secret' => $this->facebook_app_secret,
            ));
            $this->getAccessToken();
            //if (empty($this->token)) {
            //    $this->login();
            //}
            //$this->getAccessToken();
        }
        */

    }

    function fb_id($url)
    {
        if ($this->facebook_confirmed == '1') {
            $username = $this->get_facebook_username($url);
            if (!empty($username)) {
                if ($username['type'] == 'username') {
                    $username['id'] = str_replace('-', '.', $username['id']);
                    try {
                        $public_user = $this->facebook->api('/' . $username['id']);

                        return $public_user['id'];

                    } catch (Exception $e) {
                        return '';

                    }

                } else {
                    return $username['id'];

                }

            } else {
                return '';

            }

        }

    }

    function format_fb_post($aPost, $user_id = '', $user_type = '')
    {
        if (!empty($aPost->message)) {
            // Date
            $exp           = explode('_', $aPost->id);
            $fb_id         = $exp['0'];
            $post_id       = $exp['1'];
            $permalink     = 'http://www.facebook.com/' . $fb_id . '/posts/' . $post_id;
            $profile_image = $this->fb_picture($fb_id);
            $date          = format_date(date('Y-m-d H:i:s', strtotime($aPost->created_time)));
            // Format
            $format = "<div class=\"tweet\" id=\"fbpost-" . $aPost->id . "\">";
            $format .= "<div class=\"tweet_image\">";
            if (!empty($user_id)) {
                $format .= "<a href=\"null.php\" onclick=\"return load_page('" . $user_type . "','view','" . $user_id . "');\">";

            } else {
                $format .= "<a href=\"" . $permalink . "\" target=\"_blank\">";

            }
            $format .= $profile_image;
            $format .= "</a>";
            $format .= "</div>";
            $format .= "<div class=\"tweet_data\"><div class=\"pad12\">";
            $format .= "<p class=\"tweet_date\"><a href=\"" . $permalink . "\" target=\"_blank\">Posted on " . $date . "</a>";
            if ($this->fb_user_id == $fb_id) {
                $format .= "<span style=\"margin-left:24px;\"><a href=\"null.php\" onclick=\"return json_add('fb_post','" . $aPost->id . "','0','','action=delete');\">Delete</a></span>";

            }
            $format .= "</p>";
            $format .= "<p class=\"tweet_content\">" . $aPost->message . "</p>";
            $format .= "</div></div>";
            $format .= "</div>";

            return $format;

        }

    }

    function post_status($data, $attach = array())
    {
        if (!empty($this->fb_user_id)) {
            $attach['message'] = $data;
            $post              = $this->fb_post($this->fb_user_id, 'feed', $attach);
            if (!empty($post->error)) {
                return array('error' => '1', 'error_message' => $post->error->message);

            } else {
                return array('error' => '0', 'error_message' => '');

            }

        } else {
            return array('error' => '1', 'error_message' => 'No facebook profile detected.');

        }

    }

    function delete_status($id)
    {
        if (!empty($this->fb_user_id)) {
            $post = $this->fb_post($id, '', array('method' => 'delete'));
            if (!empty($post->error)) {
                return array('error' => '1', 'error_message' => $post->error->message);

            } else {
                return array('error' => '0', 'error_message' => '');

            }

        } else {
            return array('error' => '1', 'error_message' => 'No facebook profile detected.');

        }

    }

    /*

    function get_fb_user($id)

    {

        if (! empty($final_id)) {

            $data = $this->fb_graph($final_id,'');

            if (empty($data->location)) {

                $data->location = array();

            }

            //$profile_pic = $this->fb_profile($final_id);

            //$data->profile_pic = $profile_pic;

            return $data;

        } else {

            return array('error' => '1', 'error_message' => 'Could not load user data.');

        }

    }

    */
    function login($post_login_url = '')
    {
        $dialog_url = "http://www.facebook.com/dialog/oauth?client_id=" . $this->facebook_app_id . "&redirect_uri=" . urlencode($this->post_login_url) . '&scope=email,publish_stream,manage_pages'; // ,user_photos
        echo "<script>top.location.href='" . $dialog_url . "'</script>";

    }

    function getAccessToken()
    {
        // Establish this user's ID
        $this->fb_user_id = $this->fb_id($this->facebook_profile_url);
        // Get stored access code
        $get        = 1;
        $token      = $this->get_eav_value('option', 'facebook_auth_token');
        $token_date = $this->get_eav_value('option', 'facebook_auth_token_set');
        $addtime    = add_time_to_expires('000030000000', $token_date); // Limit is 60, but to be safe, go with 30.
        if (!empty($token_date) && current_date() >= $addtime) {
            $get = 1;

        } else {
            if (!empty($token)) {
                $get         = 0;
                $this->token = $token;

            } else {
                $get = 1;

            }

        }
        if ($get == 1) {
            if (!empty($this->fb_code)) {
                $token_url = $this->facebook_graph . "/oauth/access_token?client_id=" . $this->facebook_app_id . "&redirect_uri=" . urlencode($this->post_login_url) . "&client_secret=" . $this->facebook_app_secret . "&code=" . $this->fb_code;
                $response  = file_get_contents($token_url);
                $params    = null;
                parse_str($response, $params);
                $this->update_eav('option', 'facebook_auth_token', $params['access_token']);
                $this->update_eav('option', 'facebook_auth_token_set', current_date());
                $this->token = $params['access_token'];

            } else {
                $this->login();

            }

        }

    }

    /**
     * @param        $id
     * @param string $method
     *
     * @return mixed
     */
    function fb_graph($id, $method = '', $url_add = '')
    {
        $url = $this->facebook_graph . '/' . $id;
        if (!empty($method)) {
            $url .= '/' . $method;

        }
        $url .= '?access_token=' . $this->token;
        if (!empty($url_add)) {
            $url .= '&' . ltrim($url_add, '&');

        }
        $data = file_get_contents($url);

        return json_decode($data);

    }

    function fb_post($id, $method, $data)
    {
        if (!empty($this->token)) {
            $url = $this->facebook_graph . '/' . $id;
            if (!empty($method)) {
                $url .= '/' . $method;

            }
            // $url .= '?access_token=' . $this->token;
            $data['access_token'] = $this->token;
            $reply                = $this->curl_call($url, $data);

            return json_decode($reply);

        } else {
            return array('error' => '1', 'error_message' => 'Access token invalid or does not exist.');

        }

    }

    function fb_picture($id, $size = '', $width = '', $height = '')
    {
        $url = $this->facebook_graph . '/' . $id . '/picture?type=' . $size;
        $img = "<img src=\"$url\" border=\"0\"";
        $img .= " width=\"$width\" height=\"$height\"";
        $img .= " class=\"profile_pic border\" />";

        return $img;

    }

    /**
     *    Get Twitter username from URL

     */
    function get_facebook_username($url)
    {
        if (strpos($url, 'id=') !== false) {
            $exp = explode('?', $url);
            $qs  = parse_str($exp['1']);

            return array('type' => 'id', 'id' => $qs['id']);

        } else {
            $exp  = explode('/', $url);
            $last = sizeof($exp) - 1;

            return array('type' => 'username', 'id' => $exp[$last]);

        }

    }

    /**
     * Returns a list of tweets by
     * contacts, members, accounts, and
     * event registrants owned by this
     * employee.
     *
     * @return PDOStatement
     */
    function get_facebook_users($page = '1', $display = '25')
    {
        if ($this->employee['permissions']['admin'] == '1') {
            $owner = '';

        } else {
            $owner = "`owner`='" . $this->employee['id'] . "' AND ";

        }
        $low     = $page * $display - $display;
        $get_all = $this->run_query("

            (

                SELECT `member_id` AS id,`facebook`,`status`

                FROM `ppSD_member_data`

                JOIN ppSD_members

                ON ppSD_members.id=ppSD_member_data.member_id

                WHERE $owner `facebook`!='' AND `facebook`!='http://'

                LIMIT $low,$display

            )

            UNION

            (

                SELECT `contact_id`,`facebook`,`status`

                FROM `ppSD_contact_data`

                JOIN ppSD_contacts

                ON ppSD_contacts.id=ppSD_contact_data.contact_id

                WHERE $owner `facebook`!='' AND `facebook`!='http://'

                LIMIT $low,$display

            )

            UNION

            (

                SELECT `account_id`,`facebook`,`name`

                FROM `ppSD_account_data`

                JOIN ppSD_accounts

                ON ppSD_accounts.id=ppSD_account_data.account_id

                WHERE $owner `facebook`!='' AND `facebook`!='http://'

                LIMIT $low,$display

            )

        ");

        return $get_all;

    }

    /**
     * ----------------------------
     *    Twitter changes

     */
    function twitter_changes($text)
    {
        $text = preg_replace('#@([\\d\\w]+)#', '<a href="http://twitter.com/$1">$0</a>', $text);
        $text = preg_replace('/#([\\d\\w]+)/', '<a href="http://twitter.com/#search?q=%23$1">$0</a>', $text);

        return $text;

    }

    /**
     *    Get Twitter username from URL

     */
    function get_twitter_username($twit_url)
    {
        preg_match("/https?:\/\/(www\.)?twitter\.com\/(#!\/)?@?([^\/]*)/", $twit_url, $matches);

        return $matches['3'];

    }

    /**
     *    Get tweets by user

     */
    function get_tweets($twitter_username, $total = '8')
    {
        $connection = new tmhoauth(array(
            'consumer_key'    => $this->twitter_consumer_key,
            'consumer_secret' => $this->twitter_secret,
            'user_token'      => $this->twitter_oauth_token,
            'user_secret'     => $this->twitter_oauth_secret,
        ));
        $connection->request(
            'GET',
            $connection->url('1.1/statuses/user_timeline.json'),
            array('screen_name' => $twitter_username, 'count' => $total)
        );
        $reply = json_decode($connection->response['response']);
        if (empty($reply->error)) {
            return $reply;
        }
    }

    /**
     *    Format Tweet
     *    Uses a twitter API object

     */
    function format_tweet($entry, $user_id = '', $user_type = '')
    {
        if (! empty($entry->created_at)) {
            // Basics
            $date          = $entry->created_at;
            $tweet         = $entry->text;
            $profile_image = $entry->user->profile_image_url;
            // Work with it
            $put_date    = format_date(date('Y-m-d H:i:s', strtotime($date)));
            $plain_tweet = $tweet;
            $tweet       = $this->twitter_changes($this->parse_urls($tweet, '120', '_blank'));
            // Format
            $format = "<div class=\"tweet\" id=\"tweet-" . $entry->id_str . "\">";
            $format .= "<div class=\"tweet_image\">";
            if (!empty($user_id)) {
                $format .= "<a href=\"null.php\" onclick=\"return load_page('" . $user_type . "','view','" . $user_id . "');\">";

            } else {
                $format .= "<a href=\"http://twitter.com/" . $entry->user->screen_name . "\" target=\"_blank\">";

            }
            $format .= "<img src=\"$profile_image\" border=0 alt=\"" . $entry->user->screen_name . "\" title=\"" . $entry->user->screen_name . "\" />";
            $format .= "</a>";
            $format .= "</div>";
            $format .= "<div class=\"tweet_data\"><div class=\"pad12\">";
            $format .= "<p class=\"tweet_date\">Tweeted on " . $put_date;
            if ($this->twitter_handle == $entry->user->screen_name) {
                $format .= "<span style=\"margin-left:24px;\"><a href=\"null.php\" onclick=\"return json_add('tweet','" . $entry->id_str . "','0','','action=delete');\">Delete</a></span>";

            } else {
                $confirm = $this->confirm_twitter_setup();
                if ($confirm['error'] != '1') {
                    $format .= "<span style=\"margin-left:24px;\"><a href=\"null.php\" onclick=\"return popup('twitter','to=" . $entry->user->{'screen_name'} . "');\">Reply</a></span>";
                    $format .= "<span style=\"margin-left:12px;\"><a href=\"null.php\" onclick=\"return json_add('tweet','" . $entry->id_str . "','0','','action=retweet');\">Retweet</a></span>";

                }

            }
            $format .= "</p>";
            $format .= "<p class=\"tweet_content\">" . $tweet . "</p>";
            $format .= "</div></div>";
            $format .= "</div>";

            return $format;
        } else {
            return 'Could not retrieve Tweets. Please confirm that you have set up your <a href="http://documentation.zenbership.com/FAQ/How-do-I-integrate-Twitter-with-Zenbership%253F" target="_blank">Twitter integration</a> properly.';
        }
    }

    function post_tweet($tweet_data)
    {
        $setup = $this->confirm_twitter_setup();
        if ($setup['error'] != '1') {
            $connection = new tmhoauth(array(
                'consumer_key'    => $this->twitter_consumer_key,
                'consumer_secret' => $this->twitter_secret,
                'user_token'      => $this->twitter_oauth_token,
                'user_secret'     => $this->twitter_oauth_secret,
            ));
            $connection->request(
                'POST',
                $connection->url('1.1/statuses/update'),
                array('status' => $tweet_data)
            );
            $reply = json_decode($connection->response['response']);
            if (!empty($reply->id_str)) {
                return array('error' => '0', 'message' => 'Tweet Posted');

            } else {
                return array('error' => '1', 'error_message' => $reply->error);

            }

        } else {
            return $setup;

        }

    }

    function delete_tweet($tweet_id)
    {
        $setup = $this->confirm_twitter_setup();
        if ($setup['error'] != '1') {
            $connection = new tmhoauth(array(
                'consumer_key'    => $this->twitter_consumer_key,
                'consumer_secret' => $this->twitter_secret,
                'user_token'      => $this->twitter_oauth_token,
                'user_secret'     => $this->twitter_oauth_secret,
            ));
            $connection->request(
                'POST',
                $connection->url('1.1/statuses/destroy/' . $tweet_id . '.json'),
                ''
            );
            $reply = json_decode($connection->response['response']);
            if (!empty($reply->id_str)) {
                return array('error' => '0', 'message' => 'Tweet Deleted');

            } else {
                return array('error' => '1', 'error_message' => $reply->error);

            }

        } else {
            return $setup;

        }

    }

    function retweet($tweet_id)
    {
        $setup = $this->confirm_twitter_setup();
        if ($setup['error'] != '1') {
            $connection = new tmhoauth(array(
                'consumer_key'    => $this->twitter_consumer_key,
                'consumer_secret' => $this->twitter_secret,
                'user_token'      => $this->twitter_oauth_token,
                'user_secret'     => $this->twitter_oauth_secret,
            ));
            $connection->request(
                'POST',
                $connection->url('1.1/statuses/retweet/' . $tweet_id . '.json'),
                ''
            );
            $reply = json_decode($connection->response['response']);
            if (!empty($reply->id_str)) {
                return array('error' => '0', 'message' => 'Retweet Successful!');

            } else {
                return array('error' => '1', 'error_message' => $reply->error);

            }

        } else {
            return $setup;

        }

    }

    function twitter_user($url)
    {
        $setup = $this->confirm_twitter_setup();
        if ($setup['error'] != '1') {
            $connection = new tmhoauth(array(
                'consumer_key'    => $this->twitter_consumer_key,
                'consumer_secret' => $this->twitter_secret,
                'user_token'      => $this->twitter_oauth_token,
                'user_secret'     => $this->twitter_oauth_secret,
            ));
            $user_id    = $this->get_twitter_username($url);
            $connection->request(
                'GET',
                $connection->url('1.1/users/lookup.json'),
                array('screen_name' => $user_id)
            );
            $reply = json_decode($connection->response['response']);
            if (!empty($reply->errors)) {
                return '';

            } else {
                return $reply['0'];

            }

        } else {
            return $setup;

        }

    }

    function confirm_twitter_setup()
    {
        if (!empty($this->twitter_consumer_key) && !empty($this->twitter_secret) && !empty($this->twitter_oauth_token) && !empty($this->twitter_oauth_secret)) {
            if ($this->employee['permissions']['admin'] == '1' || $this->employee['permissions']['scopes']['twitter'] == 'all') {
                return array('error' => '0');

            } else {
                return array('error' => '1', 'error_message' => 'You are not permitted to perform this task.');

            }

        } else {
            return array('error' => '1', 'error_message' => 'Twitter application has not been setup.');

        }

    }

    /**
     * Returns a list of tweets by
     * contacts, members, accounts, and
     * event registrants owned by this
     * employee.
     *
     * @return PDOStatement
     */
    function get_tweet_feed($page = '1', $display = '25')
    {
        if ($this->employee['permissions']['admin'] == '1') {
            $owner = '';

        } else {
            $owner = "`owner`='" . $this->employee['id'] . "' AND ";

        }
        $low     = $page * $display - $display;
        $get_all = $this->run_query("

            (

                SELECT `member_id` AS id,`twitter`,`status`

                FROM `ppSD_member_data`

                JOIN ppSD_members

                ON ppSD_members.id=ppSD_member_data.member_id

                WHERE $owner `twitter`!='' AND `twitter`!='http://'

                LIMIT $low,$display

            )

            UNION

            (

                SELECT `contact_id`,`twitter`,`status`

                FROM `ppSD_contact_data`

                JOIN ppSD_contacts

                ON ppSD_contacts.id=ppSD_contact_data.contact_id

                WHERE $owner `twitter`!='' AND `twitter`!='http://'

                LIMIT $low,$display

            )

            UNION

            (

                SELECT `account_id`,`twitter`,`name`

                FROM `ppSD_account_data`

                JOIN ppSD_accounts

                ON ppSD_accounts.id=ppSD_account_data.account_id

                WHERE $owner `twitter`!='' AND `twitter`!='http://'

                LIMIT $low,$display

            )

        ");

        return $get_all;

    }

    function determine_user_type($row)
    {
        $user_id = $row['id'];
        if (is_numeric($row['status']) && strlen($row['status']) == 1) {
            $user_type = 'contact';

        } else if (!is_numeric($row['status']) && strlen($row['status']) == 1) {
            $user_type = 'member';

        } else {
            $user_type = 'account';

        }

        return array('id' => $user_id, 'type' => $user_type);

    }

}



