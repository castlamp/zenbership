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
class criteria extends db
{

    protected $id;
    protected $get;
    public $data;
    public $readable;
    public $query;
    public $query_count;
    public $count;
    private $cron;
    public $error;
    private $lean = false;

    public $errorResults;
    private $totalConditions = 0;
    private $conditionArrayRaw = array();
    private $exclude = array();
    private $include = array();

    function __construct($id = '', $cron = false, $lean = false)
    {
        $this->cron = $cron;
        if ($cron) {
            $this->setLean(true);
        } else {
            $this->setLean($lean);
        }

        if (! empty($id)) {
            $this->load($id);
        }

    }

    public function setId($id)
    {
        $this->load($id);
    }


    /**
     * Load Criteria
     */
    function load($id)
    {
        $this->id = $id;
        $this->get_criteria($id);
        $this->readable();
        $this->form_query();

        if (! $this->errorResults) {
            $this->count();
        }
    }

    public function getInclude()
    {
        return $this->include;
    }

    public function getReadable()
    {
        return $this->readable;
    }


    function getQuery()
    {
        return $this->query;
    }

    public function setLean($type = false)
    {
        $this->lean = $type;

        return $this;
    }

    /**
     * Get Criteria
     */
    function get_criteria($id)
    {
        $this->data = $this->get_array("
            SELECT *
            FROM `ppSD_criteria_cache`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        $this->data['criteria'] = unserialize($this->data['criteria']);

        if (empty($this->data['criteria'])) {
            if ($this->cron) {
                $this->error = true;
            } else {
                echo "0+++Could not find criteria. Your criteria may have expired.";
                exit;
            }
        }
    }


    /**
     * Make criteria readable
     * filter_type
     * filter_tables
     * filters
     */
    function readable()
    {
        $type = (! empty($this->data['type'])) ? $this->data['type'] : '';

        $inclusive = (! empty($this->data['inclusive'])) ? $this->data['inclusive'] : 'all';

        $this->readable = "<ul class=\"criteria\">";
        $this->readable .= "<li>Type: " . $type . "s</li>";
        if ($inclusive) {
            $this->readable .= "<li>Must match any criteria</li>";
        } else {
            $this->readable .= "<li>Must match all criteria</li>";
        }
        if (! empty($this->data['criteria']['all']) && $this->data['criteria']['all'] == '1') {
            $this->readable .= "<li><b>Scope: All</b></li>";
        } else {
            $this->readable .= "<li><b>Scope: Specific</b></li>";
            //$this->form_query();
            $this->readable .= $this->prepare_query('', '1');
        }
        /*
        $this->form_query();
        $this->readable .= "<li><b>Full Query</b></li>";
        $this->readable .= $this->query;
        $this->readable .= "</ul>";
        */
    }


    /**
     * Builds the actual MySQL query.
     */
    function form_query()
    {
        // Tables
        $table3      = '';
        $join_match3 = '';
        if ($this->data['type'] == 'member') {
            $table1     = 'ppSD_members';
            $table2     = 'ppSD_member_data';
            $join_match = 'member_id';
            if ($this->lean) {
                $select     = 'ppSD_members.id, ppSD_member_data.member_id, ppSD_members.email, ppSD_member_data.last_name, ppSD_member_data.first_name';
            } else {
                $select     = '*';
            }
        }
        else if ($this->data['type'] == 'contact') {
            $table1     = 'ppSD_contacts';
            $table2     = 'ppSD_contact_data';
            $join_match = 'contact_id';
            if ($this->lean) {
                $select = 'ppSD_contacts.id, ppSD_contacts.email, ppSD_contact_data.last_name, ppSD_contact_data.first_name';
            } else {
                $select = '*';
            }
        }
        else if ($this->data['type'] == 'transaction') {
            $table1      = 'ppSD_cart_sessions';
            $table2      = 'ppSD_cart_session_totals';
            $join_match  = 'id';
            $table3      = 'ppSD_shipping';
            $join_match3 = 'cart_session';
            $select      = 'ppSD_cart_sessions.*,ppSD_cart_session_totals.*,ppSD_shipping.company_name,ppSD_shipping.name ,ppSD_shipping.first_name,ppSD_shipping.last_name,ppSD_shipping.address_line_1,ppSD_shipping.address_line_2,ppSD_shipping.city,ppSD_shipping.state,ppSD_shipping.zip,ppSD_shipping.country,ppSD_shipping.phone,ppSD_shipping.email,ppSD_shipping.ship_directions,ppSD_shipping.shipped,ppSD_shipping.ship_date,ppSD_shipping.trackable,ppSD_shipping.shipping_number,ppSD_shipping.shipping_provider,ppSD_shipping.remarks';
        }
        else if ($this->data['type'] == 'rsvp') {
            $table1     = 'ppSD_event_rsvps';
            $table2     = 'ppSD_event_rsvp_data';
            $join_match = 'rsvp_id';
            $select     = '*';
        }
        else if ($this->data['type'] == 'campaign') {
            $table1     = 'ppSD_campaign_subscriptions';
            $table2     = '';
            $join_match = '';
            $select     = '*';
        }
        else {
            $table1     = 'ppSD_accounts';
            $table2     = 'ppSD_account_data';
            $join_match = 'account_id';
            $select     = '*';
        }

        // Criteria
        /**
         * JOIN " . $table2 . "
         * ON " . $table1 . ".id=" . $table2 . "." . $join_match . "
         */
        if (! empty($this->data['criteria']['all']) && $this->data['criteria']['all'] == '1') {
            if (empty($table2)) {
                $this->query       = "
                    SELECT $select
                    FROM `" . $table1 . "`
                    WHERE 1
                ";
                $this->query_count = "
                    SELECT COUNT(*)
                    FROM `" . $table1 . "`
                    WHERE 1
                ";
            } else {
                if (empty($table3)) {
                    $this->query       = "
                        SELECT $select
                        FROM `" . $table1 . "`
                        LEFT JOIN " . $table2 . "
                        ON " . $table1 . ".id=" . $table2 . "." . $join_match . "
                        WHERE 1
                    ";
                    $this->query_count = "
                        SELECT COUNT(*)
                        FROM `" . $table1 . "`
                        LEFT JOIN " . $table2 . "
                        ON " . $table1 . ".id=" . $table2 . "." . $join_match . "
                        WHERE 1
                    ";
                } else {
                    $this->query       = "
                        SELECT $select
                        FROM `" . $table1 . "`
                        LEFT JOIN " . $table2 . "
                        ON " . $table1 . ".id=" . $table2 . "." . $join_match . "
                        LEFT JOIN " . $table3 . "
                        ON " . $table1 . ".id=" . $table3 . "." . $join_match3 . "
                        WHERE 1
                    ";
                    $this->query_count = "
                        SELECT COUNT(*)
                        FROM `" . $table1 . "`
                        LEFT JOIN " . $table2 . "
                        ON " . $table1 . ".id=" . $table2 . "." . $join_match . "
                        LEFT JOIN " . $table3 . "
                        ON " . $table1 . ".id=" . $table3 . "." . $join_match3 . "
                        WHERE 1
                    ";

                }

            }

        } else {

            if (empty($table2)) {
                $this->query       = "
                    SELECT $select
                    FROM `" . $table1 . "`
                ";
                $this->query_count = "
                    SELECT COUNT(*)
                    FROM `" . $table1 . "`
                ";
            } else {
                if (empty($table3)) {
                    $this->query       = "
                        SELECT $select
                        FROM `" . $table1 . "`
                        JOIN " . $table2 . "
                        ON " . $table1 . ".id=" . $table2 . "." . $join_match . "
                    ";
                    $this->query_count = "
                        SELECT COUNT(*)
                        FROM `" . $table1 . "`
                        JOIN " . $table2 . "
                        ON " . $table1 . ".id=" . $table2 . "." . $join_match . "
                    ";
                } else {
                    $this->query       = "
                        SELECT $select
                        FROM `" . $table1 . "`
                        LEFT JOIN " . $table2 . "
                        ON " . $table1 . ".id=" . $table2 . "." . $join_match . "
                        LEFT JOIN " . $table3 . "
                        ON " . $table1 . ".id=" . $table3 . "." . $join_match3 . "
                    ";
                    $this->query_count = "
                        SELECT COUNT(*)
                        FROM `" . $table1 . "`
                        LEFT JOIN " . $table2 . "
                        ON " . $table1 . ".id=" . $table2 . "." . $join_match . "
                        LEFT JOIN " . $table3 . "
                        ON " . $table1 . ".id=" . $table3 . "." . $join_match3 . "
                    ";
                }

            }
            $where = $this->prepare_query($table1, '0');

            if (! empty($this->exclude)) {
                $exclude = array();

                if ($this->data['inclusive'] == 'and') {
                    if (! empty($this->exclude)) {
                        foreach ($this->exclude as $member => $total) {
                            if ($total == $this->totalConditions) {
                                $exclude[] = $member;
                            }
                        }
                    }
                } else {
                    $exclude = array_unique(array_values($this->exclude));
                }

                $where .= ' ' . $this->data['inclusive'];
                if ($this->data['type'] == 'member') {
                    $where .= ' ppSD_members.id';
                } else {
                    $where .= ' ppSD_contacts.id';
                }
                $where .= " NOT IN ('" . implode("','", $exclude) . "')";
            }

            if (! empty($this->include)) {
                $include = array();
                if ($this->data['inclusive'] == 'and') {
                    if (! empty($this->include)) {
                        foreach ($this->include as $member => $total) {
                            if ($total == $this->totalConditions) {
                                $include[] = $member;
                            }
                        }
                    }
                } else {
                    $include = array_unique(array_values($this->include));
                }

                $where .= ' ' . $this->data['inclusive'];
                if ($this->data['type'] == 'member') {
                    $where .= ' ppSD_members.id';
                } else {
                    $where .= ' ppSD_contacts.id';
                }
                $where .= " IN ('" . implode("','", $include) . "')";
            }

            if ($this->data['inclusive'] == 'or') {
                $where = substr($where, 4);
            } else {
                $where = substr($where, 5);
            }

            if (empty($where)) {
                $this->errorResults = true;
            } else {
                $this->errorResults = false;
            }
            $this->query .= " WHERE " . $where;
            $this->query_count .= " WHERE " . $where;
        }
    }


    private function pushInclude($aMember)
    {
        if (! isset($this->include[$aMember]))
            $this->include[$aMember] = 0;

        $this->include[$aMember]++;
    }

    private function pushExclude($aMember)
    {
        if (! isset($this->exclude[$aMember]))
            $this->exclude[$aMember] = 0;

        $this->exclude[$aMember]++;
    }

    /**
     * Runs through the parameters and adds
     * them to the query.
     *
     * @param string $table1
     * @param string $readable
     *
     * @return string
     */
    function prepare_query($table1 = '', $readable = '0')
    {
        $where = '';

        if (empty($this->data['criteria']))
            return false;

        foreach ((array)$this->data['criteria'] as $name => $value) {

            $spendids = array();

            // Content Access
            if ($name == '_content_access') {

                foreach ($value as $item) {
                    $this->totalConditions++;

                    if ($readable == '1') {
                        $content = new content;
                        $name = $content->get_name($item['id']);
                        $where = '<br />Has access to content "' . $name . '"';
                    }

                    // Expires within next x days
                    if (! empty($item['expires'])) {
                        $putdate = time() + ($item['expires'] * 86400);
                        $putdate = date('Y-m-d H:i:s', $putdate);
                        $expires = " (expires > '" . current_date() . "' AND  expires <= '" . $putdate . "')";

                        if ($readable == '1') {
                            $where .= ' but expires within ' . $item['expires'] . 'day(s).';
                        }
                    }
                    // Expired within last x days
                    else if (! empty($item['expired'])) {
                        $putdate = time() - ($item['expired'] * 86400);
                        $putdate = date('Y-m-d H:i:s', $putdate);
                        $expires = " (expires < '" . current_date() . "' AND  expires >= '" . $putdate . "')";

                        if ($readable == '1') {
                            $where .= ' but expired within the last ' . $item['expires'] . 'day(s).';
                        }
                    }
                    // Only active content access
                    else {
                        $expires = "expires > '" . current_date() . "'";
                    }

                    $cmem = $this->run_query("
                        SELECT member_id
                        FROM ppSD_content_access
                        WHERE
                          content_id='" . $this->mysql_clean($item['id']) . "'
                          AND $expires
                        GROUP BY member_id
                    ");
                    while ($row = $cmem->fetch()) {
                        $spendids[] = $row['member_id'];
                    }

                    foreach ($spendids as $aMember) {
                        //$this->conditionArrayRaw[$aMember]++;
                        if ($item['eq'] == 'neq') {
                            $this->pushExclude($aMember);
                        } else {
                            $this->pushInclude($aMember);
                        }
                    }
                }

            }

            // Total Money Spent
            else if ($name == '_total_spent') {

                foreach ($value as $item) {
                    $this->totalConditions++;

                    if ($readable == '1') {
                        //$product = new product;
                        //$name = $product->get_name($item['id']);
                        $where = '<br />Has spent "' . place_currency($item['total']) . '"';
                    }

                    if (! empty($item['within'])) {
                        $putdate = time() - ($item['within'] * 86400);
                        $putdate = date('Y-m-d H:i:s', $putdate);
                        $expires = " AND ppSD_cart_sessions.date_completed >= '" . $putdate . "'";

                        if ($readable == '1') {
                            //$product = new product;
                            //$name = $product->get_name($item['id']);
                            $where .= ' within the last ' . $item['within'] . ' day(s).';
                        }
                    }

                    $spend_ids = $this->run_query("
                        SELECT
                            C.member_id
                        FROM ppSD_cart_sessions C
                        JOIN ppSD_cart_session_totals T ON T.id = C.id
                        WHERE
                            C.status='1' AND
                            C.member_type='" . $this->data['type'] . "' AND
                            (
                                SELECT SUM(ppSD_cart_session_totals.total) AS total
                                FROM ppSD_cart_session_totals
                                JOIN ppSD_cart_sessions
                                ON ppSD_cart_sessions.id = ppSD_cart_session_totals.id
                                WHERE ppSD_cart_sessions.member_id = C.member_id AND ppSD_cart_sessions.status = '1'
                            ) > " . $this->mysql_clean($item['total']) . "
                        GROUP BY C.member_id
                    ");

                    while ($row = $spend_ids->fetch()) {
                        $spendids[] = $row['member_id'];
                    }

                    foreach ($spendids as $aMember) {
                        //$this->conditionArrayRaw[$aMember]++;
                        if ($item['eq'] == 'neq') {
                            $this->pushExclude($aMember);
                        } else {
                            $this->pushInclude($aMember);
                        }
                    }
                }

            }

            // Product Bought
            else if ($name == '_product_bought') {

                $expires = '';

                foreach ($value as $item) {
                    $this->totalConditions++;

                    if ($readable == '1') {
                        $product = new product;
                        $name = $product->get_name($item['value']);
                        $where = '<br />Has purchased "' . $name . '"';
                    }

                    if (!empty($item['within'])) {
                        $putdate = time() - ($item['within'] * 86400);
                        $putdate = date('Y-m-d H:i:s', $putdate);
                        $expires = " AND ppSD_cart_sessions.date_completed >= '" . $putdate . "'";

                        if ($readable == '1') {
                            $where .= ' within the last ' . $item['within'] . ' day(s).';
                        }
                    }

                    // ????
                    // $item['value']

                    $bought_ids = $this->run_query("
                        SELECT ppSD_cart_sessions.member_id
                        FROM ppSD_cart_items_complete
                        JOIN ppSD_cart_sessions
                        ON ppSD_cart_items_complete.cart_session=ppSD_cart_sessions.id
                        WHERE
                            ppSD_cart_items_complete.product_id='" . $this->mysql_clean($item['value']) . "' AND
                            ppSD_cart_items_complete.status='1' AND
                            ppSD_cart_sessions.member_type='" . $this->data['type'] . "'
                            $expires
                    ");

                    while ($row = $bought_ids->fetch()) {
                        $spendids[] = $row['member_id'];
                    }

                    foreach ($spendids as $aMember) {
                        //$this->conditionArrayRaw[$aMember]++;
                        if ($item['eq'] == 'neq') {
                            $this->pushExclude($aMember);
                        } else {
                            $this->pushInclude($aMember);
                        }
                    }

                }

            }

            else {
                continue;
            }

        }

        // -----

        foreach ($this->data['criteria'] as $name => $value) {

            if (
                $name == '_content_access' ||
                $name == '_product_bought' ||
                $name == '_total_spent'

                /*||
                $name == '_last_action_within' ||
                $name == '_next_action_within' ||
                $name == '_last_updated_within' ||
                $name == '_created_within' ||
                $name == '_joined_within'*/
            ) {
                continue;
            }




            else if ($name == '_last_action_within') {

            }

            else if ($name == '_next_action_within') {

            }

            else if ($name == '_last_updated_within') {

            }

            else if ($name == '_created_within') {

            }

            else if ($name == '_joined_within') {

            }


            else {
                if ($value == '0') {
                    $value = '-';
                }
                if (! empty($value) && $name != 'all' && $value != '0' && $value != 'http://') {
                    if ($value == '-') {
                        $value = '0';
                    }
                    $prev_name = $name;
                    $plain_name = str_replace('_low', '', $name);
                    $plain_name = str_replace('_high', '', $plain_name);

                    if ($prev_name == $plain_name) {
                        $inc = ' OR ';
                    } else {
                        $inc = ' AND ';
                    }
                    if (sizeof($value) > 1) {
                        if ($readable != '1') {
                            $where .= ' ' . $this->data['inclusive'];
                        }
                        $where .= ' ' . $this->form_sub_statement_loop($table1, $name, $value, $readable);
                    } else {
                        if ($readable != '1') {
                            $where .= ' ' . $this->data['inclusive'];
                        }
                        $where .= ' ' . $this->form_sub_statement($table1, $name, $value['0'], $readable);
                    }
                }
            }


        }

        return $where;
    }


    function form_sub_statement_loop($table1, $name, $value, $readable = '0')
    {
        $statement = '(';
        $sub_inner = '';
        foreach ($value as $subitem) {
            if (! empty($subitem['range'])) {
                $inc = ' AND ';
            } else {
                if ($subitem['eq'] == 'like' || $subitem['eq'] == 'eq' || $subitem['eq'] == 'neq') {
                    $inc = ' AND ';
                } else {
                    $inc = ' OR ';
                }
            }
            // Changed from OR to AND - 10/24/2013
            // OR for date ranges
            // AND for non-date ranges.
            $sub_inner .= $inc . $this->form_sub_statement($table1, $name, $subitem, $readable);
        }
        $sub_inner = substr($sub_inner, 4);
        $statement .= $sub_inner . ')';

        return $statement;

    }

    function form_sub_statement($table1, $name, $value, $readable = '0')
    {
        $equator = $value['eq'];

        if ($equator == 'eq') {
            $name_is = ' equals ';
            $use_sim = '=';
        } else if ($equator == 'neq') {
            $name_is = ' does not equals ';
            $use_sim = '!=';
        } else if ($equator == 'gt') {
            $name_is = ' > ';
            $use_sim = '>';
        } else if ($equator == 'lt') {
            $name_is = ' < ';
            $use_sim = '<';
        } else if ($equator == 'gte') {
            $name_is = ' >= ';
            $use_sim = '>=';
        } else if ($equator == 'lte') {
            $name_is = ' <= ';
            $use_sim = '<=';
        } else {
            $name_is = ' similar to ';
            $use_sim = ' LIKE ';
        }
        if (empty($value['table'])) {
            $table = $table1;
        } else {
            $table = $value['table'];
        }
        if (strpos($name, '_low')) {
            if (empty($this->data['criteria']['filter_tables'][$name])) {
                $table = $table1;
            } else {
                $table = $this->data['criteria']['filter_tables'][$name];
            }
            $plain_name = str_replace('_low', '', $name);
            $plain_name = str_replace('_high', '', $plain_name);
            $skip       = '0';
            $name_is    = ' between ';
            $low_val    = $value[$name];
            $high_val   = $value[$plain_name . '_high'];
            // Add where
            if (empty($low_val) && !empty($high_val)) {
                if ($readable == '1') {
                    $where = '<br />' . $plain_name . ' less than or equal to ' . $high_val;
                } else {
                    $where = " " . $this->data['inclusive'] . " (" . $table . "." . $plain_name . "<=" . "'";
                    $where .= $this->mysql_cleans($high_val);
                    $where .= "')";
                }
            }
            else if (empty($high_val) && !empty($low_val)) {
                if ($readable == '1') {
                    $where = '<br />' . $plain_name . ' greater than or equal to ' . $low_val;
                } else {
                    $where = " " . $this->data['inclusive'] . " (" . $table . "." . $plain_name . ">=" . "'";
                    $where .= $this->mysql_cleans($low_val);
                    $where .= "')";
                }
            }
            else {
                if ($readable == '1') {
                    $where = '<br />' . $plain_name . ' greater than or equal to ' . $low_val;
                    $where .= '<br />' . $plain_name . ' less than or equal to ' . $high_val;
                }
                else {
                    $where = " " . $this->data['inclusive'] . " (" . $table . "." . $plain_name . ">=" . "'";
                    $where .= $this->mysql_cleans($low_val);
                    $where .= "' AND " . $table . "." . $plain_name . "<=" . "'";
                    $where .= $this->mysql_cleans($high_val);
                    $where .= "')";

                }

            }

        }
        else if (strpos($name, '_high')) {
            // ... nothing ...
        }
        else {
            if ($readable == '1') {
                $where = '<LI>' . $name . ' ' . $use_sim . ' ' . $value['value'] . '</LI>';
            } else {

                if ($use_sim == ' LIKE ') {
                    $value['value'] = '%' . $value['value'] . '%';
                }
                $where = $table . '.' . $name . ' ' . $use_sim . ' ' . "'" . $this->mysql_cleans($value['value']) . "'";

            }
        }

        return $where;
    }


    function count()
    {
        $count       = $this->get_array($this->query_count);
        $this->count = $count['0'];
    }


    function preview()
    {
        if ($this->data['criteria']['all'] == '1') {
            if ($this->data['type'] == 'member') {
                $list = '<div id="crit_preview" class="pad24">All members.</div>';

            } else if ($this->data['type'] == 'contact') {
                $list = '<div id="crit_preview" class="pad24">All contact.</div>';

            } else if ($this->data['type'] == 'rsvp') {
                $list = '<div id="crit_preview" class="pad24">All event registrations.</div>';

            } else if ($this->data['type'] == 'account') {
                $list = '<div id="crit_preview" class="pad24">All accounts.</div>';
            }
        } else {
            // Preview list settings
            if ($this->data['type'] == 'member') {
                $preview_fields = explode(',', $this->get_option('preview_members'));
            }
            else if ($this->data['type'] == 'contact') {
                $preview_fields = explode(',', $this->get_option('preview_contacts'));
            }
            else if ($this->data['type'] == 'rsvp') {
                $preview_fields = explode(',', $this->get_option('preview_rsvps'));
            }
            else {
                $preview_fields = explode(',', $this->get_option('preview_accounts'));
            }
            // Begin table
            $list = '<div id="crit_preview" class="popupbody">';
            $list .= '<div class="codeBlock">' . $this->query . '</div>';
            $list .= '<table cellspacing=0 cellpadding=0 border=0 class="tablesorter listings">';
            $list .= '<thead><tr>';
            foreach ($preview_fields as $item) {
                $list .= '<th>' . strtoupper($item) . '</th>';
            }
            $list .= '</tr></thead>';
            $list .= '<tbody>';
            if ($this->count > 0) {
                $STH = $this->run_query($this->query);
                while ($row = $STH->fetch()) {
                    $list .= '<tr>';
                    foreach ($preview_fields as $item) {
                        $list .= '<td>' . $row[$item] . '</td>';
                    }
                    $list .= '</tr>';
                }
            } else {
                $list .= '<td class="weak" colspan="6">No results.</td>';
            }
            $list .= '</tbody>';
            $list .= '</table></div>';
        }
        return $list;
    }


    function create($filters, $name, $save, $inclusive, $type, $act, $public = '1', $act_id = '', $sort = '', $sortOrder = '', $display = '')
    {
        global $employee;
        $id = $this->insert("
            INSERT INTO `ppSD_criteria_cache` (
                `criteria`,
                `save`,
                `name`,
                `type`,
                `act`,
                `date`,
                `inclusive`,
                `public`,
                `owner`,
                `act_id`,
                `sort`,
                `sort_order`,
                `display_per_page`
            )
            VALUES (
                '" . $this->mysql_clean(serialize($filters)) . "',
                '" . $this->mysql_clean($save) . "',
                '" . $this->mysql_clean($name) . "',
                '" . $this->mysql_clean($type) . "',
                '" . $this->mysql_clean($act) . "',
                '" . current_date() . "',
                '" . $this->mysql_clean($inclusive) . "',
                '" . $this->mysql_clean($public) . "',
                '" . $this->mysql_clean($employee['id']) . "',
                '" . $this->mysql_clean($act_id) . "',
                '" . $this->mysql_clean($sort) . "',
                '" . $this->mysql_clean($sortOrder) . "',
                '" . $this->mysql_clean($display) . "'
            )
        ");
        $this->load($id);
        return $id;
    }


    function delete_criteria($id)
    {
        $q1 = $this->delete("
            DELETE FROM `ppSD_criteria_cache`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
    }

    /*
     * THIS NO LONGER WORKS! DO NOT USE THIS TOOL!
     * New function: $filters = $admin->build_criteria_filters($_POST);
     *
     * Takes slider filters
     * and converts them to
     * standard filter arrays.
     * Example: SYC336382||event_id||eq||ppSD_event_rsvps
     *
     * @param array $data
     * @param enum $type 'member', 'contact', 'rsvp', 'account'
     * @param string $name Optional name.
     * @param enum $act 'email', 'search', 'campaign', 'other'
     */
    function build_filters($Gdata, $type, $act, $name = '')
    {
        $filters       = array();
        $filter_types  = array();
        $filter_tables = array();
        if (empty($Gdata)) {
            $all = '1';
        } else {
            $all = '0';
            foreach ($Gdata as $Gname => $Gvalue) {
                if ($Gname == '-') {
                    $Gname = '0';
                }
                if ($Gname == 'use_advanced') {
                    continue;
                } else {
                    $exp = explode('||', $Gvalue);
                    if ($exp['0'] == '-') {
                        $exp['0'] = '0';
                    }
                    $filters[$exp['1']]       = $exp['0'];
                    $filter_types[$exp['1']]  = $exp['2'];
                    $filter_tables[$exp['1']] = $exp['3'];
                }
            }

        }
        // Filters array
        $final_filters = array(
            'all'           => $all,
            'filter'        => $filters,
            'filter_type'   => $filter_types,
            'filter_tables' => $filter_tables,
        );
        $admin = new admin;
        $final_filters = $admin->build_criteria_filters($final_filters);

        $crit_id       = $this->create($final_filters, $name, '0', 'AND', $type, $act);
        return $crit_id;
    }


}



