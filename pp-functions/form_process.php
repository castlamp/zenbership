<?php

/**
 * Processes a form in conjuncture with the
 * $form class.
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
// Load the basics
require "../admin/sd-system/config.php";
// User session
$session = new session;
$ses     = $session->check_session();

// Form session
if (empty($_POST['session'])) {
    if (!empty($_GET['session'])) {
        $use_ses = $_GET['session'];
    } else {
        if (!empty($_POST['form_id'])) {
            $form         = new form();
            $data         = $form->get_form($_POST['form_id']);
            $put_id       = str_replace('campaign-', '', $_POST['form_id']);
            $put_id       = str_replace('register-', '', $put_id);
            $data['type'] = str_replace('-free', '', $data['type']);
            $data['type'] = str_replace('-paid', '', $data['type']);
            $formA        = new form('', $data['type'], $put_id, $ses['member_id'], '', '1');
            $formA->start_session();
            $formA->setType($data['type']);
            $use_ses = $formA->session_id;
        } else {
            $db->show_error_page('F017');
            exit;
        }
    }
} else {
    $use_ses = $_POST['session'];
}

$form     = new form($use_ses);
$formdata = $form->formdata;

// Paypal "loophole" fix.
// Confirm that the session matches
// the user's cookie.
if (! empty($_POST['session'])) {
    if (empty($_COOKIE[$form->session_id])) {
        header('Location: ' . PP_URL . '/register.php?action=reset&code=L033&p=1');
        exit;
    } else {
        //
        if ($_COOKIE[$form->session_id] != $form->session_info['salt']) {
            header('Location: ' . PP_URL . '/register.php?action=reset&code=L033&p=2');
            exit;
        }
    }
}

// Some basics...
// $formdata = $form->get_form($form->session_info['act_id']);
if (empty($form->step)) {
    $form->step = '1';
}
if (!empty($_POST['page']) && $_POST['page'] != 'product') {
    if ($_POST['page'] > $form->step) {
        $submitted_step = $form->step;
        $db->show_error_page('F018');
        exit;
    } else {
        $submitted_step = $_POST['page'];
    }
} else {
    $submitted_step = $form->step;
}

// Captcha?
$holdcaptcha = '';
$captcha_bypass = $form->captcha_bypass();
if ($submitted_step == '1') {
    if (! empty($formdata['captcha']) && $formdata['captcha'] == '1') {
        if (! empty($_POST['zen_complete']) && $captcha_bypass == $_POST['zen_complete']) {
            unset($_POST['captcha']);
        } else {
            $check = $db->check_captcha(get_ip(), 'user', $_POST['captcha']);
            if ($check == '1') {
                // For future bypass...
                $cook_name = md5(get_ip() . md5(date('Y-m')));
                $cook_value = md5(get_ip() . ZEN_SECRET_PHRASE . PP_PATH);
                $db->create_cookie($cook_name, $cook_value);

                $holdcaptcha = $_POST['captcha'];
                unset($_POST['captcha']);
            } else {
                $db->show_error_page('F040');
                exit;
            }
        }
    }
}

if (empty($form->salt)) {
    $db->show_error_page('F016');
    exit;
} else {
    // Req Login?
    if ($form->req_login == 1 && $ses['error'] == '1') {
        $db->show_error_page('F010');
        exit;
    } // Continue..
    else {
        /**
         *  Form preparations, regardlesss
         *  of type.
         */
        // Scope details
        if ($form->type == 'event') {
            $redirect = PP_URL . '/event.php?id=' . $form->{'act_id'} . '&act=register';
        }
        // No skipping steps!
        if (!empty($_POST['step'])) {
            $onstep = $_POST['step'];
        } else {
            $onstep = $form->step;
        }
        //echo "<LI>$onstep";
        //pa($form);
        //echo $onstep . '>' . $form->{'step'};
        if ($onstep > $form->step) {
            header('Location: ' . $redirect . '&code=E001&step=' . $form->step);
            exit;
        }
        //echo $form->{'type'} . "<HR>";
        //echo "$onstep<HR>";
        //pa($_POST);
        //echo "<HR>";


        /**
         * <----------------------------
         * Start Event Checks
         */
        if ($form->type == 'event') {
            $event     = new event;
            $get_event = $event->get_event($form->act_id);
            // Step 1: Product selected?
            if ($onstep == '1') {
                // Paid event!
                if ($get_event['data']['total_ticket_products'] > 0) {
                    // Cart session will be
                    // created when adding the
                    // product, if needed.
                    $cart = new cart;
                    // Product found?
                    /*
                    $products['guests'] = $guests;
                    $products['other'] = $other;
                    $products['early_bird'] = $early_bird;
                    $products['tickets'] = $tickets;
                    */
                    $clear_items1 = $event->clear_products($get_event['products']['early_bird']);
                    $clear_items2 = $event->clear_products($get_event['products']['tickets']);
                    $clear_items3 = $event->clear_products($get_event['products']['other']);
                    // Found ticket?
                    $total_tickets_selected = 0;
                    // Add new items
                    $selected_products = 0;
                    if (!empty($_POST['products'])) {
                        foreach ($_POST['products'] as $id => $qty) {
                            // Check if the user can add
                            // this product
                            if ($qty > 0) {
                                // Check_event_product
                                $check_event_add = $event->check_event_product($id, $form->act_id);
                                if ($check_event_add['error'] == '1') {
                                    header('Location: ' . $redirect . '&code=' . $check_event_add['code'] . '&step=' . $form->{'step'});
                                    exit;
                                }
                                // Stock
                                $check = $cart->add($id, $qty); // Add/re-add the product.
                                if ($check['error'] == '1') {
                                    header('Location: ' . $redirect . '&code=' . $check['code'] . '&step=' . $form->{'step'});
                                    exit;
                                }
                                $selected_products++;
                                if ($check_event_add['type'] == '1' || $check_event_add['type'] == '4' || $check_event_add['type'] == '5' || $check_event_add['type'] == '6') {
                                    $total_tickets_selected++;
                                }
                            }
                        }
                    }
                    // Make sure a ticket was selected
                    // if a ticket is required.
                    if ($total_tickets_selected <= 0) {
                        $tickets = $event->find_ticket_products($form->{'act_id'});
                        if ($tickets > 0) {
                            header('Location: ' . $redirect . '&code=C009&step=1');
                            exit;
                        }
                    } else if ($total_tickets_selected > 1) {
                        header('Location: ' . $redirect . '&code=C011&step=1');
                        exit;
                    }
                    // Product detected?
                    if ($selected_products <= 0) {
                        header('Location: ' . $redirect . '&code=E002' . '&step=' . $form->{'step'});
                        exit;
                    } else {
                        $form->update_session(array('cart_id' => $check['session'], 'step' => '2'));
                        header('Location: ' . $redirect . '&step=2');
                        exit;
                    }
                }
            } // Registration
            else if ($onstep == '2') {
                // Unwanted
                unset($_POST['form_id']);
                // Continue...
                $form_data_put = $form->process_fields($_POST);
                $validate      = $form->validate_form($form_data_put);
                $form->update_step_data($form_data_put);
                $form->update_step('3');
                header('Location: ' . $redirect . '&step=3');
                exit;
            } // Guest product selection
            else if ($onstep == '3') {
                if ($_POST['guests'] > 0) {
                    // Enough spaces available?
                    $new_spaces = $get_event['stats']['spaces_available'] - $_POST['guests'];
                    if ($new_spaces < 0) {
                        header('Location: ' . $redirect . '&code=C006&step=3');
                        exit;
                    }
                    // Max guests?
                    if ($_POST['guests'] > $get_event['data']['max_guests']) {
                        header('Location: ' . $redirect . '&code=C007&step=3');
                        exit;
                    }
                    // Proceed
                    $cart = new cart;
                    if ($get_event['data']['guest_products'] > 0) {
                        $clear_items1 = $event->clear_products($get_event['products']['guests']);
                        // Add new items
                        $selected_products = 0;
                        foreach ($_POST['products'] as $id => $qty) {
                            if ($qty > 0) {
                                // Stock
                                $check = $cart->add($id, $qty); // Add/re-add the product.
                                if ($check['error'] == '1') {
                                    header('Location: ' . $redirect . '&code=' . $check['code'] . '&step=' . $form->{'step'});
                                    exit;
                                }
                                $selected_products++;
                            }
                        }
                        // Product selected?
                        if ($selected_products <= 0) {
                            if (sizeof($get_event['data']['guest_products']) == '1') {
                                $check = $cart->add($id, $_POST['guests']);
                            } else {
                                header('Location: ' . $redirect . '&step=3&code=C005');
                                exit;
                            }
                        } else {
                            $form->update_session(array('cart_id' => $check['session']));
                        }
                    }
                    // Unwanted
                    unset($_POST['form_id']);
                    // Continue...
                    $form_data_put = $form->process_fields($_POST);
                    $form->update_step_data($form_data_put);
                    $form->update_step('4');
                    header('Location: ' . $redirect . '&step=4');
                    exit;
                } else {
                    // Unwanted
                    unset($_POST['form_id']);
                    // Continue...
                    $form_data_put = $form->process_fields($_POST);
                    $form->update_step('5');
                    header('Location: ' . $redirect . '&step=5');
                    exit;
                }
            } else if ($onstep == '4') {
                // Unwanted
                unset($_POST['form_id']);
                // Continue...
                $form_data_put = $form->process_fields($_POST);
                $validate      = $form->validate_form($form_data_put);
                $form->update_step_data($form_data_put);
                $form->update_step('5');
                header('Location: ' . $redirect . '&step=5');
                exit;
            }
        }

        /* End event checks.
		----------------------------> */

        else if ($formdata['type'] == 'dependency') {
            $valid         = $formdata['id'] . '-' . $submitted_step;
            $form_data_put = $form->process_fields($_POST);
            $validate      = new validator($form_data_put, $valid);
            unset($form_data_put['captcha']);
            unset($form_data_put['page']);
            unset($form_data_put['session']);
            unset($form_data_put['zen_complete']);
            $form->update_step_data($form_data_put);
            if ($submitted_step == $formdata['pages']) {
                $go = '0';
                if (! empty($_POST['zen_complete']) && $_POST['zen_complete'] == $captcha_bypass) {
                    $go = '1';
                }
                else if ($formdata['preview'] != '1') {
                    $go = '1';
                }
                if ($go == '1') {
                    if (!empty($form->session_info['member_id'])) {
                        $fmemid = $form->session_info['member_id'];
                    } else {
                        $fmemid = $ses['member_id'];
                    }
                    $form->process_dependency($formdata, $fmemid);
                    exit;
                } else {
                    $form->display_preview();
                    exit;
                }

            } else {
                $next = $submitted_step + 1;
                $form->update_step($next);
                $redirect = PP_URL . '/register.php?id=' . $formdata['id'] . '&step=' . $next;
                header('Location: ' . $redirect);
                exit;

            }

        } /**
         * <----------------------------
         * Start Registration Checks
         */
        else if ($form->type == 'update' || $formdata['type'] == 'update') {
            $task_id = $db->start_task('update', 'user', '', $form->member_id);
            // Make sure user is logged in.
            if (!empty($form->member_id)) { //  && $form->{'member_id'} == $ses['member_id']
                $form_data_put = $form->process_fields($_POST);
                $validate      = $form->validate_form($form_data_put);
                unset($form_data_put['follow']);
                // For the default member update form.
                $user   = new user;
                $member = $user->get_user($form->member_id);

                if ($formdata['id'] == 'update-account' || $_POST['__zen_type'] == 'update-primary') {
                    // Additional fieldsets based on
                    // this user's fields.
                    $add_sets  = $user->get_area_access_ids($member);
                    $validator = new validator($form_data_put, '');
                    foreach ($add_sets as $fid) {
                        $validate = $validator->validate_fieldset($fid);
                    }

                    $db = new db();
                    $value = $db->encode_password($form_data_put['current_password'], $member['data']['salt']);

                    if ($value != $member['data']['password']) {
                        header('Location: ' . PP_URL . '/manage/update_account.php?code=L034');
                        exit;
                    }

                }

                unset($form_data_put['__zen_type']);
                unset($form_data_put['current_password']);

                // This prevents the issue of an empty password field
                // causing the user to have no password after update.
                if (empty($form_data_put['password'])) {
                    unset($form_data_put['password']);
                    unset($form_data_put['repeat_pwd']);
                }

                // Make the update
                $update = $user->edit_member($ses['member_id'], $form_data_put, 'user', $formdata['id']);
                $form->kill_session();
                //$put = 'member_updates';
                //$db->put_stats($put);
                $history = $db->add_history('member_update', '2', $ses['member_id'], $ses['member_id'], '');
            }
            $indata = array(
                'member_id' => $ses['member_id'],
            );
            $task   = $db->end_task($task_id, '1', '', 'update', $formdata['id'], $indata);
            
            // Notify admin of form submission.
            $notice = $form->admin_notify($formdata, $form_data_put, $ses['member_id'], 'member');
            
            $cache  = $user->get_user($ses['member_id'], '', '1');
            if (!empty($_POST['follow'])) {
                header('Location: ' . $_POST['follow'] . '?scode=L018');
            }
            else if (! empty($formdata['redirect'])) {
                header('Location: ' . $formdata['redirect'] . '?scode=L018');
            }
            else {
                header('Location: ' . PP_URL . '/manage/update_account.php?scode=L018');
            }
            exit;

        } /* End registration checks.
        ----------------------------> */
        /**
         * <----------------------------
         * Start Registration Checks
         */
        else if ($form->type == 'register' || $form->type == 'contact' || $form->type == 'campaign') {
            // Here we are returning
            // from a successful cart
            // purchase to complete the
            // registration process.

            $check_salt = md5(md5($form->session_info['id']) . md5($form->session_info['salt'])) . md5($form->session_info['act_id']);
            if (! empty($_GET['s'])) {
                if ($check_salt == $_GET['s']) {
                    if (!empty($_GET['status'])) {
                        $put_status = $_GET['status'];
                    } else {
                        $put_status = '';
                    }
                    $form->complete_reg($formdata, $put_status);
                }
            }
            $valid = $formdata['id'] . '-' . $submitted_step;
            // Don't mess with data when submitting a
            // preview form.
            if (empty($_POST['zen_complete']) && empty($_POST['product'])) {
                $form_data_put = $form->process_fields($_POST);
                $validate      = new validator($form_data_put, $valid);
                $form->update_step_data($form_data_put);
            }

            if ($formdata['type'] == 'register-paid' && $_POST['page'] == 'product') {
                $form->assign_products($_POST['product']);
                $redirect = PP_URL . '/register.php?id=' . $formdata['id'] . '&step=' . $form->session_info['step'];
                header('Location: ' . $redirect);
                exit;
            }

            if ($submitted_step == $formdata['pages']) {
                if ($submitted_step == '1' && empty($_POST['zen_complete'])) {
                    $form_data_put = $form->process_fields($_POST);
                    $validate      = new validator($form_data_put, $valid);
                    $form->update_step_data($form_data_put);
                }
                $go = '0';
                if (! empty($_POST['zen_complete']) && $_POST['zen_complete'] == $captcha_bypass) {
                    $go = '1';
                }
                else if ($formdata['preview'] != '1') {
                    $go = '1';
                }
                // else if (! empty($_POST['product'])) { $go = '1'; }
                if ($go == '1') {
                    // Assemble the page data.
                    $data = $form->assemble_data();
                    if (! empty($form->session_info['products'])) {
                        $unser = unserialize($form->session_info['products']);
                        // Generate member ID
                        if (!empty($data['id'])) {
                            $id = $data['id'];
                        } else {
                            $format = $db->get_option('member_id_format');
                            $id     = $db->generate_id($format);
                        }
                        $cart = new cart;
                        // Remove form's products from cart
                        // This leaves other products in cart.
                        foreach ($form->formdata['products'] as $aProdA) {
                            $cart->remove_cart_item($aProdA['product_id']);
                        }
                        // $cart->empty_cart();
                        foreach ($unser as $prod => $qty) {
                            $add = $cart->add($prod, $qty, '', $id, '', '', '', '1', '1');
                        }
                        $secure = PP_URL . '/pp-cart/checkout.php';
                        // The &s=SALT confirms the purchase
                        // avoids this on the return. See above
                        // line 267.
                        //$return = 'pp-functions/form_process.php?session=' . $form->{'session_id'} . '&s=' . $check_salt;
                        // Update return
                        //$update = $cart->update_return($add['session'],$return);
                        $update1 = $cart->update_session_regid($form->{'session_info'}['id']);
                        // $setcookie = $db->create_cookie('zen_reg_in',$id);
                        header('Location: ' . $secure);
                        exit;
                    }
                    $form->complete_reg($formdata);
                    exit;
                } else {
                    $form->display_preview();
                    exit;
                }
            } else {
                $next = $submitted_step + 1;
                $form->update_step($next);
                $redirect = PP_URL . '/register.php?id=' . $formdata['id'] . '&step=' . $next;
                header('Location: ' . $redirect);
                exit;
            }

        } /* End registration checks.
		----------------------------> */
        else {
            // $form_data_put = $form->process_fields($_POST);
            // $validate = $form->validate_form($form_data_put);
            // $kill_session = $form->kill_session();
        }

    }

}
