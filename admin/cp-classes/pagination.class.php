<?php

/**
 * Pagination
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
class pagination extends db
{

    var $page;

    var $order;

    var $dir;

    var $display;

    var $table;

    var $filters;

    var $filter_query;

    var $query;

    var $query_count;

    var $rendered_pages;

    var $use_page;

    var $total;

    var $get_add;

    var $url;

    var $join;

    var $join_on;

    var $join_on_id;

    var $select;

    /**
     *

     */
    function __construct($table, $page, $add_get = '', $data = '', $filters = '', $join = '', $select = 'id')
    {
        if (empty($data['page']) || !is_numeric($data['page'])) {
            $this->page = '1';

        } else {
            $this->page = $data['page'];

        }
        if (empty($data['dir'])) {
            $this->dir = 'ASC';

        } else {
            $this->dir = $data['dir'];

        }
        if (empty($select)) {
            $this->select = '*';

        } else {
            $this->select = $select;

        }
        if (empty($data['display']) || !is_numeric($_GET['display'])) {
            $this->display = '24';

        } else {
            $this->display = $data['display'];

        }
        if (empty($data['order'])) {
            $this->order = '';

        } else {
            $this->order = $data['order'];

        }
        if (empty($join)) {
            $this->join       = '';
            $this->join_on    = '';
            $this->join_on_id = '';

        } else {
            $this->join       = $join['table'];
            $this->join_on    = $join['on'];
            $this->join_on_id = $join['table_id'];

        }
        $this->use_page = $page;
        $this->table    = $table;
        $this->filters  = $filters;
        $this->get_add  = $add_get;
        // Proceed
        $this->build_link();
        $this->build_filters();
        $this->build_query();
        $this->render_pages();

    }

    /**
     * Build query

     */
    function build_query()
    {
        $this->query_count = "SELECT COUNT(*) FROM `" . $this->table . "`";
        $this->query       = "SELECT " . $this->table . "." . $this->select . " FROM `" . $this->table . "`";
        if (!empty($this->join)) {
            $this->query .= "JOIN `" . $this->join . "`";
            $this->query .= "ON " . $this->join . '.' . $this->join_on . '=' . $this->table . '.' . $this->join_on_id;

            $this->query_count .= "JOIN `" . $this->join . "`";
            $this->query_count .= "ON " . $this->join . '.' . $this->join_on . '=' . $this->table . '.' . $this->join_on_id;
        }
        $rest_query = '';
        if (!empty($this->filter_query)) {
            $rest_query .= " WHERE " . $this->filter_query;

        }
        if (!empty($this->order)) {
            $rest_query .= " ORDER BY " . $this->order . " " . $this->dir;

        }
        $this->query_count .= $rest_query;
        if (!empty($this->display)) {
            $low = $this->page * $this->display - $this->display;
            $rest_query .= " LIMIT " . $low . "," . $this->display;

        }
        $this->query .= $rest_query;

    }

    /**
     * This is the newer version of the filter system.
     * $this->build_filters() is still required, but ideally
     * we will transition away from it in the future.
     */
    function filters_v20()
    {
        $admin = new admin;
        $this->filter_query = $admin->build_filter_query($this->filters, '');
        $this->filter_query = trim($this->filter_query);
        if (substr($this->filter_query, 0, 3) == 'AND') {
            $this->filter_query = substr($this->filter_query, 3);
        } else {
            $this->filter_query = substr($this->filter_query, 2);
        }
    }


    /**
     * Deprecated in favor of $admin->build_filter_query
     * However this should not be removed, as certain
     * parts of the program still use it extensively. We
     * will transition away from it in the future. For the
     * time being, the first four lines, while not ideal,
     * are required to determine if the request is made
     * using the newer filter system (example: upload widget)
     * of the older one (example: content access).
     */
    function build_filters()
    {
        if (! empty($this->filters['0'])) {
            $exp = explode('||',$this->filters['0']);
        } else {
            $exp = array();
        }
        if (sizeof($exp) == 4) {
            $this->filters_v20();
        } else {
            foreach ($this->filters as $namein => $value) {
                $exp  = explode('-', $namein);
                $name = $exp['0'];
                $this->filter_query .= ' ' . $value['scope'];
                $this->filter_query .= ' ' . $name . '';
                if ($value['eq'] == 'neq') {
                    $this->filter_query .= "!='";
                } else if ($value['eq'] == 'lt') {
                    $this->filter_query .= "<='";
                } else if ($value['eq'] == 'less') {
                    $this->filter_query .= "<'";
                } else if ($value['eq'] == 'greater') {
                    $this->filter_query .= "<'";
                } else if ($value['eq'] == 'gt') {
                    $this->filter_query .= ">'";
                } else if ($value['eq'] == 'like') {
                    $this->filter_query .= " LIKE '%";
                } else {
                    $this->filter_query .= "='";
                }
                $this->filter_query .= $value['value'];
                if ($value['eq'] == 'like') {
                    $this->filter_query .= "%";
                }
                $this->filter_query .= "'";
            }
            if (substr($this->filter_query, 0, 5) == ' AND ') {
                $this->filter_query = substr($this->filter_query, 5);
            } else {
                $this->filter_query = substr($this->filter_query, 4);
            }
        }
    }

    /**
     * Render the pages

     */
    function render_pages()
    {
        $tot         = $this->get_array($this->query_count);
        $total       = $tot['0'];
        $this->total = $tot['0'];
        $add_one     = $this->page + 1;
        $sub_one     = $this->page - 1;
        // Total pages
        $pages = ceil($this->total / $this->display);

        // Previous Page
        if ($add_one > $pages) {
            $next = '';
        } else {
            $next = '<span class="next"><a href="' . $this->url . '&page=' . $add_one . '">Next &raquo;</a></span>';
        }
        if ($sub_one <= 0) {
            $prev = '';
        } else {
            $prev = '<span class="prev"><a href="' . $this->url . '&page=' . $sub_one . '">&laquo; Prev</a></span>';
        }
        // Rest of the pages
        $this->rendered_pages .= $prev;
        $cur_page = 0;
        while ($pages > 0) {
            $cur_page++;
            if ($this->page == $cur_page) {
                $this->rendered_pages .= '<span class="page on"><a href="' . $this->url . '&page=' . $cur_page . '">' . $cur_page . '</a></span>';

            } else {
                $this->rendered_pages .= '<span class="page"><a href="' . $this->url . '&page=' . $cur_page . '">' . $cur_page . '</a></span>';

            }
            $pages--;

        }
        $this->rendered_pages .= $next;

    }

    /**
     * Build Link

     */
    function build_link()
    {
        $url    = PP_URL . '/' . $this->use_page;
        $addget = '';
        foreach ($this->get_add as $name => $value) {
            $addget .= '&' . $name . '=' . urlencode($value);

        }
        $addget .= '&dir=' . $this->dir;
        $addget .= '&display=' . $this->display;
        $addget .= '&order=' . $this->order;
        $addget    = substr($addget, 1);
        $this->url = $url . '?' . $addget;

    }

}



