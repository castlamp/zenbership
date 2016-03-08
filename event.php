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
// Load the basics
require "admin/sd-system/config.php";
// Check a user's session
$session = new session;
$ses     = $session->check_session();
// Calendar ID
if (empty($_GET['id'])) {
    $session->reject('calendar', 'C001');
    exit;
} else {
    $event_id = $_GET['id'];
}
if (empty($_GET['act'])) {
    $act = '';
} else {
    $act = $_GET['act'];
}
// Accessible?
$event     = new event();
$get_event = $event->get_event($event_id);
if ($get_event['data']['members_only_view'] == '1' && $ses['error'] == '1') {
    $session->reject('calendar', 'C001');
    exit;
} else if ($get_event['data']['status'] == '0') {
    $session->reject('calendar', 'C012');
    exit;
} else if ($get_event['data']['status'] == '2') {
    $session->reject('calendar', 'C019');
    exit;
}



$changes                         = $get_event['data'];

// checkMemberRegistration
$changes['member_registered'] = '';
if ($ses['error'] != '1') {
    $checkRsvp = $event->checkMemberRegistration($event_id, $ses['member_id']);
    if (! empty($checkRsvp)) {
        $msg = $db->get_error('C017');
        $msg = str_replace('%date%', format_date($checkRsvp['date']), $msg);
        $msg = str_replace('%order_id%', $checkRsvp['order_id'], $msg);
        $msg = str_replace('%ticket_id%', $checkRsvp['id'], $msg);
        $changes['member_registered'] = "<div id=\"zen_success_code\">" . $msg . "</div>";
    }
}
$changes['timeline']             = $get_event['timeline'];
$changes['products_tickets']     = $event->format_products($get_event['products'], '0', 'tickets');
$changes['products_guests']      = $event->format_products($get_event['products'], '0', 'guests');
$changes['products_others']      = $event->format_products($get_event['products'], '0', 'other');
$changes['products_early_bird']  = $event->format_products($get_event['products'], '0', 'early_bird');
$changes['products_member_only'] = $event->format_products($get_event['products'], '0', 'member_only');
//$changes['timeline']             = $event->format_timeline($get_event['timeline']);
$changes['photos']               = $event->format_images($get_event['uploads']['photos']);
$changes['cover_photos']         = $event->create_slider($get_event['uploads']['cover_photos'], 'scrollLeft');
$changes['map']                  = generate_map($get_event['data'], '100%', '250px');
$changes['stats']                = $get_event['stats'];
//$changes['timeline']             = $event->format_timeline($get_event['timeline']);

//pa($get_event);
//pa($changes);
/**
 * <----------------------------
 * Event Registration
 */
if ($act == 'register') {
    // Continue...
    if ($get_event['data']['members_only_rsvp'] == '1' && $ses['error'] == '1') {
        $return = PP_URL . '/calendar.php?id=' . $get_event['data']['id'] . '&act=register';
        $session->reject('login', 'C002', $return);
        exit;
    } else {
        $task_id = $db->start_task('event_rsvp', 'user', $get_event['data']['id'], $ses['member_id']);
        // Generate the form session
        // and the form itself
        $form  = new form('', 'event', $_GET['id'], $ses['member_id'], '', '1');
        $check = $form->check_session();

        if ($check != 1) {
            $sesId = $form->start_session();
        }

        $form_session = $form->get_session($sesId);
        // Completion code
        if (!empty($_GET['complete']) && $form_session['step'] == '5') {
            $changes['step'] = '6';
            $complete        = md5($form_session['id'] . $form_session['date']);
            if ($_GET['complete'] == $complete) {
                if (!empty($_GET['p1'])) {
                    $payment_pass = 'zen';
                } else {
                    $payment_pass = '0';
                }
                if (empty($form_session['cart_id']) || $payment_pass == 'zen') {
                    // Complete the RSVP
                    if (!empty($_GET['status']) && $_GET['status'] == 'S') {
                        $fpaid = '2';
                    } else {
                        $fpaid = '1';
                    }
                    $final_ticket_id = $event->complete_rsvp($form_session['id'], $fpaid);
                    $get_rsvp        = $event->get_rsvp($final_ticket_id, '1');
                    // Update stats
                    if (!empty($form_session['cart_id'])) {
                        if ($fpaid == '1') {
                            $cart   = new cart;
                            $totals = $cart->get_order($form_session['cart_id']);
                            if (!empty($totals['pricing']['total'])) {
                                $money_in = $totals['pricing']['total'];
                                $db->put_stats('event_income', $money_in);
                                $db->put_stats('event_income-' . $get_rsvp['event_id'], $money_in);
                            }
                        }
                    }
                    $changes['primary'] = $get_rsvp;
                    $rendered           = new template('event_register_confirmed', $changes, '0');
                } else {
                    // Continue
                    $cart          = new cart;
                    $path          = 'event.php?id=' . $get_event['data']['id'] . '&act=register&p1=zen&complete=' . $complete;
                    $update_return = $cart->update_return($form_session['cart_id'], $path);
                    $cart->checkout();
                    exit;
                }
            }
        } else {

            if ($get_event['data']['status_code'] == '6') {
                header('Location: ' . PP_URL . '/event.php?id=' . $get_event['data']['id'] . '&code=C015');
                exit;
            }
            else if ($get_event['data']['status_code'] == '7') {
                header('Location: ' . PP_URL . '/event.php?id=' . $get_event['data']['id'] . '&code=C016');
                exit;
            }

            // In process
            if (empty($_GET['step'])) {
                $step = $form_session['step'];
            } else {
                if ($_GET['step'] > $form_session['step']) {
                    header('Location: ' . PP_URL . '/event.php?id=' . $get_event['data']['id'] . '&act=register&step=' . $form->step . '&code=E001');
                    exit;
                } else {
                    $form->update_step($_GET['step']);
                    $step = $_GET['step'];
                }
            }
            if (empty($step)) {
                $step = '1';
                $form->update_step('1');
            }
            // Some standard caller tag changes
            $changes['step']         = $step;
            $changes['onstep']       = $form_session['step'];
            $changes['form_session'] = $form_session['id'];
            // Are we on step 1?
            if ($step == '1') {
                // Clear session
                $cart = new cart;
                $cart->empty_cart();
                // Step 1 selects product options.
                // So we need make sure there are
                // products to select.
                if ($get_event['data']['total_products'] > 0) {
                    $start_step = '1';
                    $rendered   = new template('event_register_pricing', $changes, '0');
                } // If there are no products,
                // move on to step 2.
                else {
                    $start_step = '2';
                    $form->update_step('2');
                    $template = new template('event_register', $changes, '0');
                    header('Location: ' . PP_URL . '/event.php?id=' . $get_event['data']['id'] . '&act=register&step=2');
                    exit;
                }
                //$rendered = $get_event[''];
            } // Register
            else if ($step == '2') {
                $rendered = $form->generate_form_step('', $step);
            } // 3 = Guest Selection
            // 4 = Guest RSVP
            else if ($step == '3' || $step == '4') {
                // Guests allowed?
                //   No: go to step 5.
                //   Yes: Prompt entry of total guests.
                //     Redirect to step 4: select guest product (if any) and input data.
                //       Now on form_process.php, we detect total guests and add that qty of selected product to cart.
                //       If a product is required but not selected: error out!
                // Guests not allowed
                if ($get_event['data']['allow_guests'] != '1') {
                    $form->update_step('5'); // Used to be 4 - changed on 2/26/2013
                    header('Location: ' . PP_URL . '/event.php?id=' . $get_event['data']['id'] . '&act=register&step=5');
                    exit;
                } // Guests allowed
                else {
                    $guest_data   = unserialize($form_session['s3']);
                    $total_guests = $guest_data['guests'];
                    // How many guests are coming?
                    if ($step == '3') {
                        if ($total_guests > 0) {
                            $changes['selected_guests'] = $total_guests;
                        } else {
                            $changes['selected_guests'] = '0';
                        }
                        $rendered = new template('event_register_guests', $changes, '0');
                    } // Guest information
                    else {
                        // Form exist?
                        $fields = new field;
                        $find   = $fields->find_location('event-' . $get_event['data']['id'] . '-4');
                        if ($find > 0) {
                            $cur            = 0;
                            $forms_rendered = '';
                            while ($total_guests > 0) {
                                $cur++;
                                $name = 'guest' . $cur;
                                $forms_rendered .= '<h1>Guest No. ' . $cur . '</h1>';
                                $forms_rendered .= $form->generate_form_step($name, $step, '0', '0');
                                $total_guests--;
                            }
                            $temp_changes                 = array('content' => $forms_rendered);
                            $temp_changes['step']         = $step;
                            $temp_changes['form_session'] = $form_session['id'];
                            $rendered                     = new template('event_register_guests_info', $temp_changes, '0');
                        } else {
                            $form->update_step('5');
                            header('Location: ' . PP_URL . '/event.php?id=' . $get_event['data']['id'] . '&act=register&step=5');
                            exit;
                        }
                    }
                }
            } // Confirmation
            else if ($step == '5') {
                // Total guests
                $guest_data   = unserialize($form_session['s3']);
                $total_guests = $guest_data['guests'];
                // Get recap of RSVPs
                $registration_recap           = $event->registrant_confirm($form->{'s2'});
                $guest_recap                  = $event->registrant_confirm($form->{'s4'});
                $changes['registration_data'] = $registration_recap;
                $changes['guest_data']        = $guest_recap;
                $changes['completion_code']   = md5($form_session['id'] . $form_session['date']);
                if (!empty($total_guests)) {
                    $changes['selected_guests'] = $total_guests;
                } else {
                    $changes['selected_guests'] = '0';
                }
                $rendered = new template('event_register_confirm', $changes, '0');
                $task     = $db->end_task($task_id, '1', '', 'event_rsvp', $get_event['data']['id']);
            }

        }
        $changes['content'] = $rendered;
        $template           = new template('event_register', $changes, '0');
    }
} /* End registration
----------------------------> */
else if ($act == 'timeline') {
    $changes['timeline']             = $event->format_timeline($get_event['timeline']);
    $template = new template('event_timeline', $changes, '0');
} else {
    $put      = 'event_views-' . $get_event['data']['id'];
    $stat     = new stats($put, 'add', 'year');
    $stat     = new stats($put, 'add', 'month');
    $stat     = new stats($put, 'add', 'day');
    $template = new template('event_view', $changes, '0');
}

$wrapper = new template('event', $changes, '1');
$wrapper = str_replace('%wrapper_content%', $template, $wrapper);
echo $wrapper;
exit;