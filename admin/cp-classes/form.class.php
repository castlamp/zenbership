<?php

/**
 *    ppSD2
 *    (c) 2012 Castlamp.
 *    http://www.ppsd2.com/
 *
 *  Purpose:
 */

class form extends db
{

    public $scope;
    public $form_id;
    public $session_id;
    public $session_info;
    public $step;
    public $member_id;
    public $salt;
    public $form_location;
    public $req_login;
    public $formdata;
    public $redirect;
    public $order_id;
    public $s1, $s2, $s3, $s4, $s5;


    /**
     * Set up the output for this template.
     * $use_id    -> Session ID, based on $this->cookie_name()
     * $scope    -> 'register','lead','update','event','forced_update'
     * $form_id    -> [scope]-[act_id]
     * $member_id    -> ID of member registering
     * $step    -> Page or step in the process
     * $req_login    -> Require login.
     */
    function __construct($use_id = '', $scope = '', $form_id = '', $member_id = '', $step = '1', $req_login = '0', $force_clear = false)
    {
        $use_id = trim($use_id);

        if ($force_clear) {
            $this->kill_session();

            if (! empty($scope)) { $this->scope = $scope; }
            if (! empty($act_id)) { $this->act_id = $form_id; }
        }

        if (! empty($use_id)) {
            $this->session_id = $use_id;

            $this->get_session();
            $this->check_session();
        } else {
            $this->scope     = $scope;
            $this->type      = $scope;
            $this->form_id   = $form_id;
            $this->act_id    = $form_id;
            $this->member_id = $member_id;
            if (! empty($step)) {
                $this->step = $step;
                $this->update_step($step);
            }
            $this->step          = $step;
            $this->form_location = $this->scope . '-' . $this->form_id;

            $this->session_id    = $this->cookie_name();
        }

        // Get the form
        if (!empty($this->form_id)) {
            $this->form_id  = str_replace('-free', '', $this->form_id);
            $this->form_id  = str_replace('-paid', '', $this->form_id);
            $this->form_id  = str_replace('update-', 'register-', $this->form_id);

            $this->formdata = $this->get_form($this->form_id);
        }
        /*
        $check = $this->check_session();
        if ($check != 1) {
            $this->start_session();
        }
        $this->get_session();
        */
    }




    public function setType($type)
    {
        $this->scope = $type;

        return $this;
    }




    /**
     * Check if a form session is active
     */
    function check_session()
    {
        if (! empty($_COOKIE[$this->session_id])) {

            $check_salt = $this->get_array("
				SELECT
                    `id`,
                    `step`,
                    `type`,
                    `closed_code`,
                    `code_approved`,
                    `form_id`
				FROM
				    `ppSD_form_sessions`
				WHERE
				    `salt`='" . $this->mysql_clean($_COOKIE[$this->session_id]) . "' AND
				    `id`='" . $this->mysql_clean($this->session_id) . "'
				LIMIT 1
			");

            if (!empty($check_salt['id'])) {
                if (!empty($check_salt['closed_code']) && $check_salt['code_approved'] != '1') {
                    $changes = array(
                        'session' => $check_salt['id'],
                        'step'    => 'code'
                    );
                    $wrapper = new template('reg_code_required', $changes, '1');
                    echo $wrapper;
                    exit;
                }
                $this->salt = $_COOKIE[$this->session_id];
                $this->update_activity();
                $this->step = $check_salt['step'];

                if (empty($check_salt['type']) && ! empty($this->scope)) {
                    $up = $this->run_query("
                        UPDATE
                            `ppSD_form_sessions`
                        SET
                            `type`='" . $this->mysql_clean($this->scope) . "',
                            `act_id`='" . $this->mysql_clean($this->act_id) . "',
                            `step`='1'
                        WHERE
                            `id`='" . $this->session_id . "'
                        LIMIT 1
                    ");
                }

                return '1';
            } else {
                $this->kill_session();

                return '0';
            }
        } else {
            return '0';
        }
    }

    /**
     * Get session data
     */
    function get_session($force_id = '')
    {
        if (empty($force_id)) {
            $force_id = $this->session_id;
        }
        $kess_is_happy      = $this->get_array("
			SELECT *
			FROM `ppSD_form_sessions`
			WHERE `id`='" . $this->mysql_clean($force_id) . "'
			LIMIT 1
		");
        $this->session_info = $kess_is_happy;
        $this->session_id   = $kess_is_happy['id'];
        $this->scope        = $kess_is_happy['type'];
        $this->step         = $kess_is_happy['step'];
        $this->form_id      = $kess_is_happy['form_id'];
        $this->order_id     = $kess_is_happy['cart_id'];
        $this->type         = $kess_is_happy['type'];
        $this->act_id       = $kess_is_happy['act_id'];
        $this->member_id    = $kess_is_happy['member_id'];
        $this->salt         = $kess_is_happy['salt'];
        $this->req_login    = $kess_is_happy['req_login'];
        $this->redirect     = $kess_is_happy['redirect'];
        $this->s1           = $kess_is_happy['s1'];
        $this->s2           = $kess_is_happy['s2'];
        $this->s3           = $kess_is_happy['s3'];
        $this->s4           = $kess_is_happy['s4'];
        $this->s5           = $kess_is_happy['s5'];

        return $kess_is_happy;
    }

    /**
     * Get session data
     */
    function update_activity()
    {
        $kess_is_happy = $this->update("
			UPDATE
                `ppSD_form_sessions`
			SET
                `last_activity`='" . current_date() . "'
			WHERE
                `id`='" . $this->mysql_clean($this->session_id) . "'
			LIMIT 1
		");
    }

    /**
     * Kill Session
     */
    function kill_session($redirect = '0', $theSessId = '', $salt = '', $skip_cart = '0')
    {
        foreach ($_COOKIE as $name => $value) {
            // zen_c4e033ba3dc20991d4b199eed
            if (substr($name, 0, 4) == 'zen_' && strlen($name) >= 20 && strlen($value) == 6) {
                $this->delete_cookie($name);

                $del = $this->delete("
                    DELETE FROM
                        `ppSD_form_sessions`
                    WHERE
                        `id`='" . $this->mysql_clean($name) . "'
                ");
            }
        }

        if ($skip_cart != '1') {
            $cart = new cart;
            $cart->reset_cart();
        }

        return true;

        /*
        if (empty($salt)) {
            if (! empty($_COOKIE[$theSessId])) {
                $salt = $_COOKIE[$theSessId];
            }
        }

        if (! empty($salt)) {
            $sesdets = $this->get_session($theSessId);

            // $formData = $this->get_basic_form($sesdets['form_id']);

            if (md5($sesdets['salt']) == $salt) {
                $del = '1';
            } else {
                $del = '0';
            }
        } else {
            $del = '1';
        }

        // Delete
        if ($del == 1) {
            // Cart?
            // $form = $this->get_form($sesdets['act_id']);
            //$cart = new cart;
            //$cart->update_session_regid('');

            if ($skip_cart != '1') {
                $cart = new cart;
                $cart->reset_cart();
            }

            // Session
            $del = $this->delete("
                DELETE FROM `ppSD_form_sessions`
                WHERE `id`='" . $this->mysql_clean($theSessId) . "'
                LIMIT 1
		    ");
            $this->delete_cookie($theSessId);

            if (! empty($sesdets['redirect']) && $redirect == '1') {
                $this->create_cookie('zen_forms_later', '1');
                header("Location: " . $sesdets['redirect']);
                exit;
            }
        }
        // Delete by IP
        else {
            $del = $this->delete("
                DELETE FROM `ppSD_form_sessions`
                WHERE `ip`='" . $this->mysql_clean( get_ip() ) . "'
		    ");

            if ($skip_cart != '1') {
                $cart = new cart;
                $cart->reset_cart();
            }
        }
        */
    }

    /**
     * Processes a dependency form.
     *
     * @param array  $formdata      Form data for the submitted form.
     * @param string $member_id     ID of the member or contact.
     * @param string $member_type   contact or member
     * @param array  $force_data    If you don't have a form session, pass the submitted
     *                              form data through this array.
     * @param string $skip_redirect "1" skips redirect, "0" redirects. If you are
     *                              programmatically entering data, set this to "0".
     *
     * @return string Standard redirect, otherwise returns "1" for success.
     */
    function process_dependency($formdata, $member_id, $member_type = 'member', $force_data = array(), $skip_redirect = '0')
    {
        $task_id = $this->start_task('dependency_form', 'user', $formdata['id'], '');

        // Check if member ID is associated
        // with the form already.
        if (empty($member_id)) {
            $member_id = $this->session_info['member_id'];
        }
        if (empty($member_id)) {
            $this->show_error_page('F035');
            exit;
        } else {

            // Update fields if they exist
            // in the scope. Otherwise add
            // all into EAV table with
            // submission ID attached.

            // Establish the redirection.
            if (! empty($formdata['redirect']) && $formdata['redirect'] != 'http://') {
                $this->redirect = $formdata['redirect'];
            }
            else if (! empty($this->session_info['redirect'])) {
                $this->redirect = $this->session_info['redirect'];
                $formdata['redirect'] = $this->redirect;
            }

            if (empty($force_data)) {
                $data = $this->assemble_data();
            } else {
                $data = $force_data;
            }

            // $data = $formdata;
            $id   = $this->submit_eav_form($formdata['id'], $formdata['name'], $member_id, $data, $member_type);

            $this->kill_session('0','','','1');

            $indata = array(
                'member_id' => $member_id,
                'member_type' => $member_type,
                'form_id' => $formdata['id'],
                'data' => $data,
                'id' => $id,
            );
            $task = $this->end_task($task_id, '1', '', 'dependency_form', $formdata['id'], $indata);

            $add = add_history('dependency_submit', '', $member_id, '1', $formdata['id']);

            // Notify admin of form submission.
            $this->admin_notify($formdata, $data, $member_id, $member_type);

            if ($skip_redirect != '1') {
                if (! empty($formdata['redirect'])) {
                    if (strpos($formdata['redirect'], 'pp-cart/checkout.php?complete') !== false) {
                        $code = 'F038';
                    }
                    else if (strpos($formdata['redirect'], 'pp-cart/checkout.php') !== false) {
                        $code = 'F037';
                    }
                    else {
                        $code = 'F036';
                    }
                    if (strpos($formdata['redirect'], '?') !== false) {
                        header('Location: ' . $formdata['redirect'] . '&scode=' . $code);
                    } else {
                        header('Location: ' . $formdata['redirect'] . '?scode=' . $code);
                    }
                }
                else if ($member_type == 'member') {
                    header('Location: ' . PP_URL . '/manage/?scode=F036');
                }
                else {
                    header('Location: ' . PP_URL . '/?scode=F036');
                }
            } else {
                return '1';
            }
        }
    }

    /**
     * Notify admin of a form submission
     * @param $formdata array Form information from the database
     * @param $data array Data submitted
     * @param $user_id string ID of the member/contact who submitted the form.
     * @param $user_type string member or contact.
     */
    function admin_notify($formdata, $data, $user_id, $user_type = 'member')
    {

        if (! empty($formdata['email_forward'])) {

            // Assemble data
            $add_to_email = '<table cellspacing="0" cellpadding="0" border="0" width="600">';
            foreach ($data as $name => $value) {
                $add_to_email .= '<tr>
                <td width="150">' . format_db_name($name) . '</td>
                <td>' . nl2br($value) . '</td>
                </tr>';
            }
            $add_to_email .= '</table>';

            if ($user_type == 'member') {
                $user = new user;
                $user_details = $user->get_user($user_id);
                $display = $user_details['data']['username'];
            } else {
                $contact = new contact;
                $user_details = $contact->get_contact($user_id);
                $display = $user_details['data']['first_name'] . ' ' . $user_details['data']['last_name'];
            }

            // E-mail the user
            $changes    = array(
                'form' => $formdata,
                'data' => $add_to_email,
                'user_display' => $display,
                'date' => format_date(current_date()),
                'user' => $user_details['data'],
            );

            $send_to = explode(',', $formdata['email_forward']);
            foreach ($send_to as $to) {
                $email_data = array(
                    'to' => $to,
                );
                if (! empty($data['email'])) {
                    $email_data['from'] = $display . ' <' . $data['email'] . '>';
                }
                else if (! empty($user_details['data']['email'])) {
                    $email_data['from'] = $display . ' <' . $user_details['data']['email'] . '>';
                }
                $sendASI      = new email('', '', '', $email_data, $changes, 'admin_email_notify');
            }
        }
    }


    /**
     * @param $form_id
     * @param $form_name
     * @param $member_id
     * @param $data
     * @param string $user_type
     *
     * @return string
     */
    function submit_eav_form($form_id, $form_name, $member_id, $data, $user_type = 'member')
    {
        $id  = generate_id('random', '28');

        $task_name = 'form_submit';
        $task_id = $this->start_task($task_name, 'user', '', $id);

        $q12 = $this->insert("
            INSERT INTO `ppSD_form_submit` (
                `id`,
                `form_id`,
                `date`,
                `user_id`,
                `user_type`,
                `form_name`
            )
            VALUES (
              '" . $this->mysql_clean($id) . "',
              '" . $this->mysql_clean($form_id) . "',
              '" . current_date() . "',
              '" . $this->mysql_clean($member_id) . "',
              '" . $this->mysql_clean($user_type) . "',
              '" . $this->mysql_clean($form_name) . "'
            )
        ");

        // Add EAV Data
        foreach ($data as $key => $value) {
            $this->update_eav($id, $key, $value);
        }

        $indata = array(
            'id' => $id,
            'form' => array(
                'id' => $form_id,
                'name' => $form_name,
            ),
            'data' => $data,
            'user' => array(
                'id' => $member_id,
                'type' > $user_type,
            ),
        );

        $task = $this->end_task($task_id, '1', '', $task_name, $form_id, $indata);

        $history = $this->add_history('form_submit', '2', $member_id, $user_type, $id, '');

        return $id;
    }


    /**
     * @param $form_id
     * @param $member_id
     *
     * @return string
     */
    function find_previous_eav($form_id, $member_id)
    {
        $count = $this->get_array("
            SELECT `id`
            FROM `ppSD_form_submit`
            WHERE `form_id`='" . $this->mysql_clean($form_id) . "' AND `user_id`='" . $this->mysql_clean($member_id) . "'
            LIMIT 1
        ");
        if (!empty($count['id'])) {
            return $count['id'];
        } else {
            return '';
        }
    }

    /**
     * Complete a registration
     */
    function complete_reg($formdata, $force_status = '', $skip_redirect = '0')
    {
        // Assemble submitted data
        $data = $this->assemble_data();
        // Forced member ID?
        $data['id'] = $this->session_info['final_member_id'];
        unset($data['page']);
        unset($data['session']);
        unset($data['product']);

        // Override source?
        $user_type = '';
        if (! empty($_COOKIE['zen_source'])) {
            $source = new source();
            $source_data = $source->get_tracking($_COOKIE['zen_source']);
            $formdata['source'] = $source_data['source_id'];
        }

        if (! empty($formdata['source'])) {
            $data['source'] = $formdata['source'];
        } else {
            $source         = new source;
            $data['source'] = $source->get_source_id($formdata['id']);
        }
        if (! empty($formdata['account'])) {
            $data['account'] = $formdata['account'];
        }
        else {
            if ($formdata['type'] == 'contact') {
                $accty = 'default';
            }
            else if ($formdata['type'] == 'campaign') {
                $accty = 'campaign_default';
            }
            else {
                $accty = 'member_default';
            }
            $account         = new account;
            $acct            = $account->get_account($accty);
            $data['account'] = $acct['id'];
        }
        // Stats
        $put = 'forms';
        $this->put_stats($put);
        $put = 'form_' . $formdata['id'];
        $this->put_stats($put);
        // Prep email BCC
        $email_data = array();
        //if (!empty($formdata['email_forward'])) {
        //   $email_data['bcc'] = $formdata['email_forward'];
        //}
        // Create the member
        if ($formdata['type'] == 'contact' || $formdata['type'] == 'campaign') {
            if (empty($create_id)) {
                // Public?
                $new_contact_public = $this->get_option('new_contact_public');
                if ($new_contact_public == '1') {
                    $data['public'] = '1';
                } else {
                    $data['public'] = '0';
                }
                $data['next_action'] = date('Y-m-d H:i:s',strtotime(current_date()) + 86400);
                $data['type']  = $this->get_option('new_contact_form_type'); // 'Lead';
                $contact       = new contact;
                $create        = $contact->create($data, '', $formdata['id']);
                $create_id     = $create['id'];
                // If the contact was already found,
                // no need to assign the contact.
                if ($create['error'] != '1') {
                    $data['owner'] = $contact->assign($create_id);
                }
                $user_type     = 'contact';
                $put           = 'forms_contact';
                $this->put_stats($put);
                $template_id = '';

                // Notify admin of form submission.
                if ($formdata['type'] == 'contact') {
                    $this->admin_notify($formdata, $data, $create_id, 'contact');
                }
            }
        } else {

            if ($formdata['type'] == 'register-free') {
                $put = 'forms_free';
                $this->put_stats($put);
            } else {
                $put = 'forms_paid';
                $this->put_stats($put);
            }
            $data['owner'] = '2';
            // Different status than registration
            // would occur for something like an
            // invoice request.
            if (!empty($force_status)) {
                $data['status'] = $force_status;
            } else {
                $data['status'] = $formdata['reg_status'];
            }
            // Member Type
            if (! empty($formdata['member_type'])) {
                $data['member_type'] = $formdata['member_type'];
            }
            // Assemble data
            $finaldata = array(
                'member'      => $data,
                'content'     => $formdata['content'],
                'newsletters' => $formdata['newsletters'],
            );
            // Create user
            if ($formdata['email_thankyou'] == '1') {
                $skipem = '0';
            } else {
                $skipem = '1';
            }
            // Auto-create account?
            if ($formdata['account_create'] == '1' && ! empty($finaldata['member']['company_name'])) {
                $array                          = array(
                    'name'         => $finaldata['member']['company_name'],
                    'company_name' => $finaldata['member']['company_name'],
                );
                $account                        = new account;
                $make                           = $account->create($array);
                $account_id                     = $make['id'];
                $finaldata['member']['account'] = $account_id;
            }
            $user        = new user;
            $getem       = $user->create_member($finaldata, $skipem, $formdata['template'], $email_data, $formdata['id']);
            $user_type   = 'member';
            $create_id   = $getem['member_id'];
            $template_id = $create_id;

            // Notify admin of form submission.
            $this->admin_notify($formdata, $data, $create_id, 'member');
        }
        // Create an account?
        /*
        if ($formdata['account_create'] == '1') {
            $account = new account;
            $account->create($finaldata);
        }
        */
        // Add form submission...
        // submit_eav_form($form_id,$form_name,$member_id,$data,$user_type = 'member')
        // Here we log the submitted form in the EAV table.
        // Useful for several reasons:
        // 1. Lumps together the form fields.
        // 2. For existing contacts, it $contact->create() will
        //    skip the create process. This ensures that the data
        //    is stored.
        if ($formdata['type'] == 'contact') {
            $putdata = $data;
            unset($putdata['session']);
            unset($putdata['step']);
            unset($putdata['form_id']);
            unset($putdata['account']);
            unset($putdata['id']);
            unset($putdata['source']);
            unset($putdata['public']);
            unset($putdata['owner']);
            unset($putdata['type']);
            $insert = $this->submit_eav_form($formdata['id'], $formdata['name'], $create_id, $putdata, 'contact');
        }
        //else if ($formdata['type'] == 'register-free' || $formdata['type'] == 'register-paid') {
        //    $insert = $this->submit_eav_form($formdata['id'],$formdata['name'],$create_id,$data,'member');
        //}
        // Check Conditions
        $conditions       = new conditions;
        $check_conditions = $conditions->check_conditions($data, $formdata['conditions'], $create_id);
        foreach ($check_conditions as $met) {
            if ($met['type'] == 'expected_value' && $formdata['type'] == 'contact') {
                $conditions->perform_condition($met, $create_id, $user_type);
            } else {
                if ($met['type'] == 'campaign' || $met['type'] == 'content') {
                    $conditions->perform_condition($met, $create_id, $user_type);
                }
            }
        }

        // Kill the session
        $kill_session = $this->kill_session();
        // Delete the cookie
        // $this->delete_cookie('zen_reg_in');
        // E-Mail For Contacts
        // Skipped if a purchase was made.
        if ($skip_redirect != '1') {
            if ($user_type == 'contact' && $formdata['email_thankyou'] == '1') {
                if (empty($formdata['template'])) {
                    $formdata['template'] = 'contact_thankyou';
                }
                // E-mail the user
                $changes = array();
                $email   = new email('', $create_id, 'contact', $email_data, $changes, $formdata['template']);
            }
            if (!empty($formdata['redirect']) && strtolower($formdata['redirect']) != 'http://') {
                header('Location: ' . $formdata['redirect']);
                exit;
            } else {
                $changes = array();
                if ($formdata['type'] == 'contact') {
                    $template = 'contact_thankyou';
                } else if ($formdata['type'] == 'campaign') {
                    $campaign            = new campaign($formdata['act_id']);
                    $campdata            = $campaign->get_campaign();
                    $changes['campaign'] = $campdata;
                    if ($campdata['optin_type'] == 'double_optin') {
                        $template = 'campaign_confirm_needed';
                    } else {
                        $template = 'campaign_subscription';
                    }
                } else {
                    // Display the correct template
                    if ($data['status'] == 'P') {
                        $template = 'reg_activation_code';
                    } else if ($data['status'] == 'Y') {
                        $template = 'reg_await_activation';
                    } else if ($data['status'] == 'S') {
                        $last_invoice       = $this->get_array("
                            SELECT `id`
                            FROM `ppSD_invoices`
                            WHERE `member_id`='" . $this->mysql_clean($data['id']) . "'
                            ORDER BY `date` DESC
                            LIMIT 1
                        ");
                        $cart               = new cart;
                        $invoice            = $cart->get_invoice($last_invoice['id']);
                        $changes['invoice'] = $invoice['data'];
                        $template           = 'reg_awaiting_payment';
                    } else {
                        $template = 'reg_complete';
                    }
                };


                if ($this->isAjax()) {
                    $this->ajaxReply(false, $changes);
                } else {
                    // Template
                    $wrapper = new template($template, $changes, '1', $template_id);
                    echo $wrapper;
                    exit;
                }
            }
        }
        else {
            return $getem;
        }
    }

    /**
     * Start session
     */
    function start_session($code_in = '', $force_clear = false)
    {
        // In case the cookie got
        // deleted by there was
        // an active session
        $this->salt = substr(md5(time() . uniqid()), 0, 6);

        $find       = $this->get_array("
			SELECT `id`,`salt`
			FROM `ppSD_form_sessions`
			WHERE `id`='" . $this->mysql_clean($this->session_id) . "'
		");

        if (empty($find['id'])) {
            // Continue
            $code           = '';
            $full_code      = '';
            $this->formdata = $this->get_form($this->form_id);
            if (!empty($this->formdata['code_required'])) {
                $code_req = $this->formdata['code_required'];
            } else {
                $code_req = '0';
            }
            if ($code_req == '1') {
                // Code submitted?
                $show_code = '1';
                if (!empty($_GET['code'])) {
                    $full_code  = $_GET['code'];
                    $check_code = $this->check_code($full_code);
                    if ($check_code == '1') {
                        $show_code = '0';
                    }
                }
                if ($show_code == '1') {
                    // Code Required
                    if ($this->formdata['type'] == 'register') {
                        $type_page = PP_URL . '/register.php';
                    } else if ($this->formdata['type'] == 'event') {
                        $type_page = PP_URL . '/event.php';
                    } else if ($this->formdata['type'] == 'contact') {
                        $type_page = PP_URL . '/contact.php';
                    } else {
                        $type_page = PP_URL . '/register.php';
                    }
                    $changes = array(
                        'session'   => $this->session_id,
                        'page'      => 'code',
                        'form_id'   => $this->form_id,
                        'type_page' => $type_page,
                    );
                    $err     = new template('reg_code_required', $changes, '1');
                    echo $err;
                    exit;
                }
            }
            if (!empty($this->formdata['type'])) {
                if ($this->formdata['type'] == 'register-free' || $this->formdata['type'] == 'register-paid') {
                    $gen_id = generate_id($this->get_option('member_id_format'));
                } else {
                    $gen_id = generate_id('random', '25');
                }
            } else {
                $gen_id = '';
            }

            if (empty($this->session_id)) {
                $this->session_id    = $this->cookie_name();
            }

            $kess_is_happy = $this->insert("
				INSERT INTO `ppSD_form_sessions` (
                    `id`,
                    `act_id`,
                    `form_id`,
                    `date`,
                    `step`,
                    `ip`,
                    `member_id`,
                    `last_activity`,
                    `salt`,
                    `type`,
                    `code_approved`,
                    `closed_code`,
                    `final_member_id`
				)
				VALUES (
				    '" . $this->mysql_clean($this->session_id) . "',
				    '" . $this->mysql_clean($this->form_id) . "',
				    '" . $this->mysql_clean($this->form_location) . "',
				    '" . current_date() . "',
				    '" . $this->mysql_clean($this->step) . "',
				    '" . $this->mysql_clean(get_ip()) . "',
				    '" . $this->mysql_clean($this->member_id) . "',
				    '" . current_date() . "',
				    '" . $this->mysql_clean($this->salt) . "',
				    '" . $this->mysql_clean($this->scope) . "',
				    '1',
				    '" . $this->mysql_clean($full_code) . "',
				    '" . $this->mysql_clean($gen_id) . "'
                )
			");
            // Cart
            if (! empty($this->formdata['type']) && $this->formdata['type'] == 'register-paid') {
                //$cart = new cart();
                $data = array(
                    'reg_session' => $this->form_id,
                );
                //$cart->update_order($cart->id, $data);
                $cart = new cart;
                $cart->reset_cart();
            }

            $this->create_cookie($this->session_id, $this->salt);
        } else {
            if (empty($_COOKIE[$this->session_id])) {
                $this->create_cookie($this->session_id, $find['salt']);
            }
        }

        return $this->session_id;
    }

    function set_member_id($id)
    {
        $q1 = $this->update("
            UPDATE `ppSD_form_sessions`
            SET `final_member_id`='" . $this->mysql_clean($id) . "'
            WHERE `id`='" . $this->mysql_clean($this->session_id) . "'
            LIMIT 1
        ");
        $this->member_id = $id;
    }

    function set_redirect($redirect)
    {
        $q1             = $this->update("
            UPDATE `ppSD_form_sessions`
            SET `redirect`='" . $this->mysql_clean($redirect) . "'
            WHERE `id`='" . $this->mysql_clean($this->session_id) . "'
            LIMIT 1
        ");
        $this->redirect = $redirect;
    }


    function public_list()
    {
        $STH  = $this->run_query("
            SELECT `id`
            FROM `ppSD_forms`
            WHERE
              `public_list`='1' AND `disabled`!='1' AND (
                  `type`='register-free' OR
                  `type`='register-paid'
              )
            ORDER BY `name` ASC
        ");
        $list = '';
        $forms = array();
        while ($row = $STH->fetch()) {
            $forms[] = $row['id'];
            $form = $this->get_form(str_replace('register-', '', $row['id']));
            $list .= new template('register_list_entry', $form, '0');
        }

        if (empty($list)) {
            $list .= new template('register_list_entry_none', '', '0');
        }

        return array(
            'forms' => $forms,
            'list' => $list,
        );
    }

    /**
     * Check closed registration code.
     */
    function check_code($code)
    {
        $q1    = $this->get_array("
			SELECT *
			FROM `ppSD_form_closed_sessions`
			WHERE `code`='" . $this->mysql_clean($code) . "'
			LIMIT 1
		");
        $ecode = '';
        if (empty($q1['code'])) {
            //$this->show_error_page('F015');
            $ecode = 'F015';
        }
        if ($q1['used'] == '1') {
            if ($q1['form_session'] != $this->session_id) {
                //$this->show_error_page('F013');
                $ecode = 'F013';
            }
        } else {
            if (!empty($q1['form_id']) && $q1['form_id'] != $this->form_id) {
                //$this->show_error_page('F014');
                $ecode = 'F014';
            }
        }
        if (!empty($ecode)) {
            $ev      = $this->get_error($ecode);
            $changes = array(
                'details' => $ev,
            );
            $temp    = new template('error', $changes, '1');
            echo $temp;
            exit;
        } else {
            $q1 = $this->update("
				UPDATE `ppSD_form_closed_sessions`
				SET
                    `date_used`='" . current_date() . "',
                    `used`='1',
                    `form_session`='" . $this->mysql_clean($this->session_id) . "'
				WHERE `code`='" . $this->mysql_clean($code) . "'
				LIMIT 1
			");
        }

        return '1';
    }

    /**
     * Step UL for registration forms.
     */
    function generate_step_array($form_data, $current = '1')
    {
        $steps      = "<ul id=\"zen_event_steps\">";
        $temp_pages = $form_data['pages'];
        if ($temp_pages <= 0) {
            $temp_pages = '1';
        }
        $currentA = 0;
        if ($form_data['type'] == 'register-paid') {
            $opt = $this->get_error('F030');
            if ($current == 'product') {
                $steps .= '<li class="on">' . $opt . '</li>';
            } else {
                $steps .= '<li><a href="' . PP_URL . '/register.php?id=' . $form_data['id'] . '&step=membership_option">' . $opt . '</a></li>';
            }
        }
        while ($temp_pages > 0) {
            $currentA++;
            $stepnm = 'step' . $currentA . '_name';
            if (!empty($form_data[$stepnm])) {
                $sname_f = $form_data[$stepnm];
            } else {
                $sname_f = 'Step ' . $currentA;
            }
            if ($currentA < $current || $current == 'preview' || $current == 'product') {
                $put = '<a href="' . PP_URL . '/register.php?id=' . $form_data['id'] . '&step=' . $currentA . '">' . $sname_f . '</a>';
            } else {
                $put = $sname_f;
            }
            if ($currentA == $current) {
                $steps .= '<li class="on">' . $put . '</li>';
            } else {
                $steps .= '<li>' . $put . '</li>';
            }
            $temp_pages--;
        }
        if ($form_data['preview'] == '1') {
            $opt1 = $this->get_error('F031');
            if ($current == 'preview') {
                $steps .= '<li class="on">' . $opt1 . '</li>';
            } else {
                $steps .= '<li>' . $opt1 . '</li>';
            }
        }
        if ($form_data['type'] == 'register-paid') {
            $opt2 = $this->get_error('F032');
            if ($current == 'payment') {
                $steps .= '<li class="on">' . $opt2 . '</li>';
            } else {
                $steps .= '<li>' . $opt2 . '</li>';
            }
        }
        $steps .= '<li>Complete</li>';
        $steps .= "</ul>";

        return $steps;
    }

    /**
     * List of membership options
     */
    function format_products($products, $type = '1')
    {
        $cart = new cart;
        $list = '';
        $size = sizeof($products);
        foreach ($products as $aProd) {
            $id = uniqid();

            if ($aProd['type'] == $type) {

                if ($size == '1' && $type == '1') {
                    if ($aProd['qty_control'] == '1') {
                        $checked = ' checked="checked"';
                    } else {
                        $checked = '1';
                    }
                } else {
                    if ($aProd['qty_control'] == '1') {
                        $checked = '';
                    } else {
                        $checked = '0';
                    }
                }

                if ($aProd['qty_control'] == '1') {
                    $field = '<input type="checkbox" class="zenRegCheckers" id="' . $id . '" name="product[' . $aProd['product_id'] . ']" value="1" ' . $checked . ' />';
                } else {
                    if ($type == '1') {
                        $class = ' req';
                    } else {
                        $class = '';
                    }
                    $field = '<input type="text" id="' . $id . '" name="product[' . $aProd['product_id'] . ']" value="' . $checked . '" style="width:50px;" maxlength="3" class="zen_num' . $class . '" />';
                }

                $aprod                  = $cart->get_product($aProd['product_id']);
                $aChange                = $aprod['data'];
                $aChange['qty_control'] = $field;
                $list .= new template('reg_select_product_entry', $aChange, '0');
            }
        }
        if (empty($list)) {
            $list = '';
        }
        return $list;
    }

    function assign_products($products)
    {
        $found_req = 0;
        $need_req  = 0;
        foreach ($this->formdata['products'] as $prod) {
            if ($prod['type'] == '1') {
                $need_req = '1';
            }
            if (!empty($products[$prod['product_id']])) {
                $qty = $products[$prod['product_id']];
                if (!is_numeric($qty)) {
                    $qty = 1;
                }
                if ($qty > 0) {
                    if ($prod['type'] == '1') {
                        $found_req = '1';
                    }
                    $allprods[$prod['product_id']] = $qty;
                }
            }
        }
        if ($found_req <= 0 && $need_req == 1) {
            $this->show_error_page('F033');
            exit;
        } else {
            $data = array(
                'products' => serialize($allprods),
            );
            $this->update_session($data);
        }
    }

    /**
     * Generate a preview
     */
    function display_preview()
    {
        $formdata  = $this->get_form($this->{'act_id'});
        $data      = $this->assemble_data();
        $f12       = new field('', '0', '', '', '', '', '1');
        $full_form = '';
        $cur       = 0;
        $pages     = $formdata['pages'];
        while ($pages > 0) {
            $cur++;
            $page_name = 'register-' . $this->act_id . '-' . $cur;
            $full_form .= $f12->generate_form($page_name, $data);
            $pages--;
        }
        $step_ul = $this->generate_step_array($formdata, 'preview');
        $captcha = $this->captcha_bypass();
        $changes = array(
            'session'   => $this->session_id,
            'step'      => $formdata['pages'],
            'step_list' => $step_ul,
            'form'      => $full_form,
            'captcha_bypass' => $captcha,
        );
        $wrapper = new template('reg_preview', $changes, '1');
        echo $wrapper;
        exit;
    }

    function captcha_bypass()
    {
        return md5(SALT . date('Y-m') . SALT1);
    }

    function get_form_product($id)
    {
        $q1 = $this->get_array("
            SELECT *
            FROM `ppSD_form_products`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        if (!empty($q1['id'])) {
            $q1['error']   = '0';
            $cart          = new cart;
            $q1['product'] = $cart->get_product($q1['product_id']);
        } else {
            $q1['error']   = '1';
            $q1['product'] = '';
        }

        return $q1;
    }

    /**
     * Assign a code to a form
     */
    function add_code($form_id, $code = '', $email = '')
    {
        $formdata = $this->get_form($form_id);
        if ($formdata['error'] != '1') {
            if (empty($code)) {
                $code = generate_id('random', '29');
            }
            $q1 = $this->insert("
                INSERT INTO `ppSD_form_closed_sessions` (`code`,`form_id`,`date_issued`,`sent_to`)
                VALUES (
                    '" . $this->mysql_clean($code) . "',
                    '" . $this->mysql_clean($form_id) . "',
                    '" . current_date() . "',
                    '" . $this->mysql_clean($email) . "'
                )
            ");
            if (!empty($email)) {
                // E-mail the user
                $changes    = array(
                    'form' => $formdata,
                    'code' => $code,
                );
                $email_data = array(
                    'to' => $email,
                );
                $email      = new email('', '', '', $email_data, $changes, 'reg_code');
            }

            return true;
        } else {
            return false;
        }
    }


    function get_form_name($id)
    {
        $data = $this->get_array("
            SELECT `name`
            FROM `ppSD_forms`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $data['name'];
    }

    /**
     * Get a Form
     */
    function get_form($id, $recache = '0')
    {
        $cache = $this->get_cache($id);
        if ($cache['error'] != '1' && $recache != '1') {
            $getform = $cache['data'];
        } else {
            // Basics
            if (strpos($id, 'register-') !== false) {
                $use_id   = str_replace('register-', '', $id);
                $check_id = 'register-' . $use_id;
            }
            else if (strpos($id, 'contact-') !== false) {
                $use_id   = str_replace('contact-', '', $id);
                $check_id = 'register-' . $use_id;
            }
            else if (strpos($id, 'dependency-') !== false) {
                $use_id   = str_replace('dependency-', '', $id);
                $check_id = 'register-' . $use_id;
            }
            else {
                $use_id   = $id;
                $check_id = $id;
            }

            // Just in case...
            // For campaigns, since this form ID naming
            // system isn't ideal.
            $put_id       = str_replace('campaign-', '', $id);

            // $check_id1 = 'register-' . $id;
            $getform = $this->get_basic_form($check_id);

            /*
            $getform   = $this->get_array("
				SELECT *
				FROM `ppSD_forms`
				WHERE `id`='" . $this->mysql_clean($check_id) . "' OR `id`='" . $this->mysql_cleans($check_id1) . "'
				LIMIT 1
			");
            */
            if (!empty($getform['id'])) {
                // Link
                $getform['link'] = PP_URL . '/register.php?action=reset&id=' . $id;
                if ($getform['type'] == 'register-free') {
                    $getform['show_type'] = $this->get_error('C013');
                } else if ($getform['type'] == 'register-paid') {
                    $getform['show_type'] = $this->get_error('C014');
                } else {
                    $getform['show_type'] = 'Other';
                }
                // Content access granting
                $access_granting         = array();
                $access_granting_letters = array();
                $STH                     = $this->run_query("
                    SELECT *
                    FROM `ppSD_access_granters`
                    WHERE `item_id`='" . $this->mysql_clean($use_id) . "'
			    ");
                while ($row = $STH->fetch()) {
                    if ($row['type'] == 'content') {
                        $access_granting[] = $row;
                    } else {
                        $access_granting_letters[] = $row;
                    }
                }
                $getform['content']     = $access_granting;
                $getform['newsletters'] = $access_granting_letters;
                if (empty($getform['pages'])) {
                    $getform['pages'] = '1';
                }
                // Products
                $STHA     = $this->run_query("
                    SELECT *
                    FROM `ppSD_form_products`
                    WHERE `form_id`='" . $this->mysql_clean($use_id) . "'
                    ORDER BY `order` ASC
			    ");
                $products = array();
                while ($row = $STHA->fetch()) {
                    $products[] = $row;
                }
                $getform['products'] = $products;
                // Conditions
                $STHA       = $this->run_query("
                    SELECT *
                    FROM `ppSD_form_conditions`
                    WHERE `act_id`='" . $this->mysql_clean($use_id) . "' OR `act_id`='" . $this->mysql_clean($put_id) . "'
		        ");
                $conditions = array();
                while ($row = $STHA->fetch()) {
                    $conditions[] = $row;
                }
                $getform['conditions'] = $conditions;
                $getform['error']      = '0';
                // Cache the data
                $cache = $this->add_cache($id, $getform);
            } else {
                $getform['error'] = '1';
            }

        }

        return $getform;
    }


    /**
     * Update step
     */
    function update_step($new_step)
    {
        $up         = $this->update("
			UPDATE
                `ppSD_form_sessions` 
			SET
                `step`='" . $this->mysql_clean($new_step) . "',
                `last_activity`='" . current_date() . "'
			WHERE
                `id`='" . $this->mysql_clean($this->session_id) . "'
			LIMIT 1
		");
        $this->step = $new_step;
    }

    /**
     * Submit Step Data
     */
    function update_step_data($data)
    {
        // Check conditions
        // $formdata['conditions']
        if (!empty($this->formdata{'conditions'})) {
            $conditions       = new conditions;
            $check_conditions = $conditions->check_conditions($data, $this->formdata{'conditions'});
            foreach ($check_conditions as $met) {
                if ($met['type'] == 'kill') {
                    $code_changes = array(
                        'field' => $met['field'],
                        'value' => $met['value'],
                    );
                    $this->show_error_page('F034', $code_changes);
                    exit;
                } else if ($met['type'] == 'product') {
                    $cart = new cart;
                    if (!empty($met['act_qty'])) {
                        $qty = $met['act_qty'];
                    } else {
                        $qty = 1;
                    }
                    $add = $cart->add($met['act_id'], $qty, '', '', '', '', '');
                }
            }
        }
        $step_name = 's' . $this->step;
        $data      = serialize($data);
        $up        = $this->update("
			UPDATE `ppSD_form_sessions` 
			SET `" . $step_name . "`='" . $this->mysql_clean($data) . "'
			WHERE `id`='" . $this->mysql_clean($this->session_id) . "'
			LIMIT 1
		");
    }

    /**
     * Assemble data from a
     * submitted form.
     */
    function assemble_data()
    {
        $this->get_session();
        $current_up = 0;
        $down       = 5;
        $data       = array();
        while ($down > 0) {
            $current_up++;
            $use = 's' . $current_up;
            if (!empty($this->{$use})) {
                $data = array_merge(unserialize($this->{$use}), $data);
            }
            $down--;
        }
        return $data;
    }

    /**
     * Update session data
     */
    function update_session($data)
    {
        $a_q = '';

        foreach ($data as $name => $value) {
            $a_q .= ",`" . $this->mysql_cleans($name) . "`='" . $this->mysql_cleans($value) . "'";
        }

        $a_q = substr($a_q, 1);

        $up  = $this->update("
			UPDATE `ppSD_form_sessions` 
			SET $a_q
			WHERE `id`='" . $this->mysql_clean($this->session_id) . "'
			LIMIT 1
		");
    }

    /**
     * Process fields for entry
     * into temporary table.
     */
    function process_fields($data, $table = 'ppSD_member_data')
    {
        $form_data_put = array();
        foreach ($_POST as $name => $value) {
            if ($name == 'step' || $name == 'act' || $name == 'session' || $name == 'page') {
                continue;
            } else {
                $form_data_put[$name] = $value;
                // $find = $this->find_field($name,$table);
            }
        }

        return $form_data_put;
    }

    /**
     * Find field
     * Deprecated for performance.
     * Snyc fields using cron job
     * and when a form is created.
     */
    function find_field($field, $table = 'ppSD_member_data')
    {
        $find = $this->get_array("
			SHOW COLUMNS FROM `$table` LIKE '$field';
		");
        if (empty($find['Field'])) {

        }
    }

    /**
     * Validate a form
     */
    function validate_form($data)
    {
        $use_location = $this->form_id;
        if (!empty($this->step)) {
            $use_location .= '-' . $this->step;
        }
        $validator = new validator($data, $use_location);
    }

    /**
     * Generate a form session cookie name
     */
    function cookie_name()
    {
        // This could cause issues on internal networks with a shared ip...
        return 'zen_' . substr(md5(md5($this->form_location) . md5(get_ip())), 0, 25);
    }

    /**
     * Get step of current form.
     * $field_name_array is an optional prefix on
     * the form. So for example, for guest RSVPs,
     * your would want guest1, guest2, etc. to
     * create arrays of data: guest1[first_name], etc.
     */
    function generate_form_step($field_name_array = '', $force_step = '', $ssl = '0', $form_tag = '1')
    {
        if (empty($force_step)) {
            $force_step = $this->step;
        }
        $get_step_data = 's' . $force_step;
        $step_data     = $this->session_info[$get_step_data];
        if (!empty($step_data)) {
            $step_data = unserialize($step_data);
        } else {
            // Session
            $session = new session;
            $ses     = $session->check_session();
            if (!empty($ses['member_id'])) {
                $user      = new user;
                $udata     = $user->get_user($ses['member_id']);
                $step_data = $udata['data'];
            }
        }
        $use_location = $this->form_location . '-' . $force_step;
        // Need to get full form with page
        // with a cache.
        $step_data_for_form = $this->get_form($use_location);
        $fields             = new field($field_name_array, $form_tag, $force_step, $this->session_id, $ssl, $step_data_for_form['type']);
        $data               = $fields->generate_form($use_location, $step_data);

        return $data;
    }
}
