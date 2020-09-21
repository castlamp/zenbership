<?php

/**
 * SPECIAL FIELD CONSIDERATIONS
 * Used extensively in printing and exporting.
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
class special_fields extends db
{

    protected $type;

    public $current_row;

    /**
     * @param string $type member, contact, account, rsvp
     * @param bool   $cp   Whether HTML should be included, like links.
     *                     Set to "1" for when called from the admin CP,
     *                 "0" for when called in exporting/importing.

     */
    function __construct($type = '', $cp = '0')
    {
        $this->type = $type;
        $this->cp   = $cp;

    }

    /**
     * Update the row array that we are working
     * with currently.
     *
     * @param array $row Row taken directly from the DB.
     */
    function update_row($row)
    {
        $this->current_row = $row;

    }

    /**
     * Process the actual field we are working
     * with, with considerations for special
     * fields.
     *
     * @param string $name  Field name
     * @param string $value Field value
     *
     * @return string Final value.
     */
    function process($name, $value)
    {
        if ($value == '1920-01-01 00:01:01') {
            return '';

        } else {

            // ----------------------------
            //  Member
            if ($this->type == 'member') {
                if ($name == 'id') {
                    if ($this->cp == '1') {
                        return $value;

                    } else {
                        return "<a href=\"null.php\" onclick=\"return load_page('member','view','" . $value . "');\">" . $value . "</a>";

                    }

                } else if ($name == 'member_type') {
                    $user  = new user();
                    $mtype = $user->get_member_type($value);

                    return $mtype['name'];

                } else if ($name == 'status') {
                    $ret = "<a href=\"null.php\" onclick=\"return popup('activate','id=" . $this->current_row['id'] . "');\" id=\"member-status-" . $this->current_row['id'] . "\">";

                    //$ret = '';

                    if ($value == 'A') {
                        $ret .= "Active";

                    } else if ($value == 'C') {
                        $ret .= "Suspended";

                    } else if ($value == 'P') {
                        $ret .= "Pending E-Mail Approval";

                    } else if ($value == 'R') {
                        $ret .= "Rejected";

                    } else if ($value == 'S') {
                        $ret .= "Pending Invoice Payment";

                    } else if ($value == 'Y') {
                        $ret .= "Pending Staff Approval";

                    } else if ($value == 'I') {
                        $ret .= "Inactive";

                    } else {
                        $ret .= "Unpaid";

                    }

                    $ret .= "</a>";

                    if ($this->cp == '1') {
                        return strip_tags($ret);

                    } else {
                        return $ret;

                    }

                }

            }

            // ----------------------------
            //  Contact
            else if ($this->type == 'contact') {
                if ($name == 'id') {
                    $link = "<a href=\"null.php\" onclick=\"return load_page('contact','view','" . $value . "');\">" . $value . "</a>";
                    if ($this->cp == '1') {
                        return strip_tags($link);

                    } else {
                        return $link;

                    }

                }
                else if ($name == 'type') {
                    $contact = new contact;
                    $typeContact = $contact->getType($value);
                    return $typeContact['name'];
                }
                else if ($name == 'status') {
                    if ($value == '1') {
                        return 'Active';

                    } else if ($value == '2') {
                        return 'Converted';

                    } else {
                        return 'Dead';

                    }

                }

            }

            // ----------------------------
            //  RSVP
            else if ($this->type == 'rsvp') {

                if ($name == 'id') {
                    $link = "<a href=\"null.php\" onclick=\"return load_page('rsvp','view','" . $value . "');\">" . $value . "</a>";
                    if ($this->cp == '1') {
                        return strip_tags($link);

                    } else {
                        return $link;

                    }

                }
                else if ($name == 'purchased' && $this->type == 'rsvp') {
                    if ($this->current_row['type'] == '1') {
                        $products = '';
                        $cart = new cart;
                        $order = $cart->get_order($this->current_row['order_id'], '0');
                        foreach ($order['components'] as $product) {
                            $products .= '<LI>' .$product['data']['name'] . ' (' . $product['pricing']['qty'] . 'x)</li>';
                        }
                        if (! empty($products)) {
                            return '<ul style="list-style:inside;margin:0;padding:0;">' . $products . '</ul>';
                        } else {
                            return '';
                        }
                    } else {
                        return '';
                    }

                } else if ($name == 'type' && $this->type == 'rsvp') {
                    if ($value == '1') {
                        return 'Attendee';

                    } else {
                        $event = new event;
                        $rsvp  = $event->get_rsvp($this->current_row['primary_rsvp']);

                        return 'Guest of ' . $rsvp['first_name'] . ' ' . $rsvp['last_name'];

                    }

                } else if ($name == 'status' && $this->type == 'rsvp') {
                    if ($value == '1') {
                        return 'Paid';
                    }
                    else if ($value == '3') {
                        return 'Comped';
                    }
                    else {
                        return 'Unpaid';

                    }

                }

            }

            // ----------------------------
            //  General
            if ($name == 'source') {
                if (!empty($value)) {
                    if ($value == '9999') {
                        return "<span class='weak'>Unassigned</span>";

                    } else {
                        $source = new source;
                        $sdata  = $source->get_source($value);

                        return $sdata['source'];

                    }

                } else {
                    return "<span class='weak'>N/A</span>";

                }

            } else if ($name == 'profile_pic') {
                $db = new db;

                return $db->get_profile_pic($this->current_row['id'], $this->current_row['facebook'], $this->current_row['twitter'], $this->type, '24', '24');

            } else if (
                $name == 'date' ||
                $name == 'created' ||
                $name == 'last_updated' ||
                $name == 'last_login' ||
                $name == 'next_action' ||
                $name == 'joined' ||
                $name == 'date_completed' ||
                $name == 'activated'
            ) {
                return format_date($value);

            } else if ($name == 'account') {
                if (!empty($value)) {
                    $account = new account;
                    $adata   = $account->get_account_name($value);
                    $link    = "<a href=\"null.php\" onclick=\"return load_page('account','view','" . $value . "');\">" . $adata . "</a>";

                } else {
                    $link = "<span class='weak'>N/A</span>";

                }
                if ($this->cp == '1') {
                    return strip_tags($link);

                } else {
                    return $link;

                }

            } else if ($name == 'owner' || $name == 'checked_in_by') {
                if (empty($value) || $value == '2') {
                    if ($this->type == 'contact') {
                        $link = '<a href="returnnull.php" onclick="return popup(\'assign_contact\',\'id=' . $this->current_row['id'] . '\');"><img src="imgs/icon-attention-on.png" width="16" height="16" alt="Alerts!" class="icon" />Assign</a>';

                    } else {
                        $link = '<span class="weak">N/A</span>';

                    }
                    if ($this->cp == '1') {
                        return strip_tags($link);

                    } else {
                        return $link;

                    }

                } else {
                    $admin = new admin;
                    $adata = $admin->get_employee('', $value);

                    return $adata['username'];

                }

            }
            // For notes...
            else if ($name == 'user_id') {

                // Confirms it's a note...
                if (!empty($this->current_row['item_scope'])) {
                    $user = new user;
                    $username = $user->get_username($value);

                    if (! empty($username)) {
                        $link = "<a href=\"null.php\" onclick=\"return load_page('member','view','" . $value . "');\">" . $username . "</a>";
                    }
                    else {
                        $contact = new contact;
                        $contactName = $contact->get_name($value);
                        if (! empty($contactName)) {
                            $link    = "<a href=\"null.php\" onclick=\"return load_page('contact','view','" . $value . "');\">" . $contactName . "</a>";
                        } else {
                            $link = $value;
                        }
                    }
                } else {
                    $link = $value;
                }
                if ($this->cp == '1') {
                    return strip_tags($link);

                } else {
                    return $link;

                }

            } else if ($name == 'member_id') {
                if (!empty($this->current_row['member_type']) && $this->current_row['member_type'] == 'member') {
                    $user = new user;
                    $link = "<a href=\"null.php\" onclick=\"return load_page('member','view','" . $value . "');\">" . $user->get_username($value) . "</a>";

                } else if (!empty($this->current_row['member_type']) && $this->current_row['member_type'] == 'contact') {
                    $contact = new contact;
                    $link    = "<a href=\"null.php\" onclick=\"return load_page('contact','view','" . $value . "');\">" . $contact->get_name($value) . "</a>";

                } else {
                    $link = $value;

                }
                if ($this->cp == '1') {
                    return strip_tags($link);

                } else {
                    return $link;

                }

            } else if ($name == 'contact_frequency') {
                $tf = format_timeframe($value);

                return $tf['formatted'];

            } else if ($name == 'expected_value') {
                return place_currency($value);

            } // ----------------------------
            //  Everything else...
            else {
                $prices = array(
                    'price',
                    'cost',
                    'total',
                    'gateway_fees',
                    'fee_flat',
                    'subtotal',
                    'shipping',
                    'tax',
                    'tax_rate',
                    'savings',
                    'refunds',
                    'invoice_due',
                    'invoice_paid',
                    'credits',
                    'paid',
                    'due',
                    'new_balance',
                    'dollars_off'
                );
                $date   = check_date($value);
                if ($date == '1') {
                    if ($value == '1920-01-01 00:01:01' || $value == '1920-01-01') {
                        return '';

                    } else {
                        return format_date($value);

                    }

                } else if (in_array($name, $prices)) {
                    return place_currency($value);

                } else {
                    $formatting = $this->field_formatting($name);
                    $encrypt    = $this->field_encryption($name);
                    if ($encrypt) {
                        if ($this->cp == '1') {
                            return '';

                        } else {
                            return decode($value);

                        }

                    } else if (!empty($formatting)) {
                        if ($formatting == 'phone') {
                            $replacements = array('-', '.', ' ', '(', ')');
                            $value        = str_replace($replacements, '', $value);

                            return $this->format_phone($value);

                        } else {
                            return $value;

                        }

                    } else {
                        return $value;

                    }

                }

            }

        }

    }

    /**
     * Cleans a "raw" database field name
     * for display in a more readable fashion.
     *
     * @param $name
     */
    function clean_name($name)
    {
        $name = str_replace('_', ' ', $name);

        return ucwords($name);

    }

}

