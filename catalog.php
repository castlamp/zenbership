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
$cart = new cart();
/**
 * Permissions:
 * 0 : None
 * 1 : View Catalog
 * 2 : View Cart
 * 3 : Checkout
 */
$cart->check_permission('1');
/**
 * View Product
 */
if (!empty($_GET['id'])) {
    $product = $cart->get_product($_GET['id']);
    if ($product['error'] == '1' || $product['data']['hide'] == '2' || $product['data']['hide'] == '1' || ! empty($product['data']['associated_id']) || ! empty($product['data']['invoice_id'])) {
        $echanges = array(
            'title'   => $db->get_error('S017'),
            'details' => $db->get_error('S024')
        );
        $template = new template('error', $echanges, '1');
        echo $template;
        exit;
    } else {
        $skip = 0;
        if ($product['data']['members_only'] == '1') {
            $ses = new session();
            $session = $ses->check_session();
            if ($session['error'] == '1') {
                $skip = '1';
            }
        }
        if ($skip == '1') {
            $echanges = array(
                'title'   => $db->get_error('S017'),
                'details' => $db->get_error('S024')
            );
            $template = new template('error', $echanges, '1');
            echo $template;
            exit;
        } else {
            $up_views    = $cart->up_product_stats($product['data']['id']);
            $category    = $cart->get_category($product['data']['category']);
            $breadcrumbs = $cart->breadcrumbs($product['data']['category']);
            $catalog     = array(
                'data'        => $product['data'],
                'category'    => $category,
                'breadcrumbs' => $breadcrumbs,
            );
            $template    = new template('catalog_view_product', $catalog, '1');
            echo $template;
            exit;
        }
    }
} /**
 * Browse Catalog
 */
else {
    /**
     * Pagination, sorting options,
     * and other
     */
    if (!empty($_GET['category'])) {
        $final_category = $_GET['category'];
    } else {
        $final_category = '1';
    }
    $category = $cart->get_category($final_category);
    // Category does not exist.
    if ($category['error'] == '1' || $category['hide'] == '1') {
        $echanges = array(
            'title'   => $db->get_error('S017'),
            'details' => $db->get_error('S025')
        );
        $template = new template('error', $echanges, '1');
        echo $template;
        exit;
    } // Found category
    else {
        $put = 'category_views-' . $final_category;
        $db->put_stats($put);
        if (empty($category['cols'])) {
            $category['cols'] = '1';
        }
        $filters = array(
            'hide' => array('scope' => 'AND', 'value' => '0', 'eq' => 'eq'),
        );
        if (! empty($_GET['query'])) {
            $query           = $_GET['query'];
            $filters['tagline'] = array('scope' => 'AND', 'value' => $_GET['query'], 'eq' => 'like');
            $filters['description'] = array('scope' => 'OR', 'value' => $_GET['query'], 'eq' => 'like');
            $filters['name'] = array('scope' => 'OR', 'value' => $_GET['query'], 'eq' => 'like');
            $filters['id'] = array('scope' => 'OR', 'value' => $_GET['query'], 'eq' => 'like');
        } else {
            $query               = '';
            $filters['category'] = array('scope' => 'AND', 'value' => $final_category, 'eq' => 'eq');
        }
        $add_get = array(
            'category' => $final_category
        );
        if (!empty($_GET['organize'])) {
            if ($_GET['organize'] == 'alpha_az') {
                $_GET['order'] = 'name';
                $_GET['dir']   = 'ASC';
            } else if ($_GET['organize'] == 'alpha_za') {
                $_GET['order'] = 'name';
                $_GET['dir']   = 'DESC';
            } else if ($_GET['organize'] == 'price_low') {
                $_GET['order'] = 'price';
                $_GET['dir']   = 'ASC';
            } else if ($_GET['organize'] == 'price_high') {
                $_GET['order'] = 'price';
                $_GET['dir']   = 'DESC';
            } else if ($_GET['organize'] == 'popularity') {
                $_GET['order'] = 'popularity';
                $_GET['dir']   = 'DESC';
            } else {
                $_GET['order'] = 'cart_ordering';
                $_GET['dir']   = 'ASC';
            }
            $add_get['organize'] = $_GET['organize'];
        }
        if (!empty($_GET['price_low'])) {
            $_GET['price_low']    = str_replace('$', '', $_GET['price_low']);
            $add_get['price_low'] = $_GET['price_low'];
            $filters['price-0']   = array('scope' => 'AND', 'value' => $_GET['price_low'], 'eq' => 'gt');
        } else {
            $_GET['price_low'] = '';
        }
        if (!empty($_GET['price_high'])) {
            $_GET['price_high']    = str_replace('$', '', $_GET['price_high']);
            $add_get['price_high'] = $_GET['price_high'];
            $filters['price-1']    = array('scope' => 'AND', 'value' => $_GET['price_high'], 'eq' => 'lt');
        } else {
            $_GET['price_high'] = '';
        }
        if (empty($_GET['organize'])) {
            $_GET['organize'] = '';
        }
        if (empty($_GET['order'])) {
            $_GET['order'] = 'cart_ordering';
        }
        if (empty($_GET['dir'])) {
            $_GET['dir'] = 'ASC';
        }
        if (empty($_GET['display'])) {
            $_GET['display'] = '24';
        }
        $paginate = new pagination('ppSD_products', 'catalog.php', $add_get, $_GET, $filters);
        if (empty($_GET['page'])) {
            $page = '1';
        } else {
            if (is_numeric($_GET['page']) && $_GET['page'] > 0) {
                $page = $_GET['page'];
            } else {
                $page = '1';
            }
        }
        /**
         * Breadcrumbs and sub-categories
         */
        $breadcrumbs   = $cart->breadcrumbs($final_category);
        $category_list = $cart->render_subcategories($final_category);
        /**
         * Product blocks
         */
        if ($category['cols'] == '0') {
            $category['cols'] = '1';
        }
        $blocks  = '';
        $ablocks = 0;
        $colup   = 0;
        $allcols = array();

        $STH     = $db->run_query($paginate->query);
        while ($row = $STH->fetch()) {
            // $col_width = floor(100 / $category['cols']);
            //if ($category['cols'] == '1' || $category['cols'] == '0') {
            //	$style = '';
            //   $allcols[$colup][] = '<li class="zen_clear zen_empty"></li>';
            // $blocks .= '<li class="zen_clear zen_empty"></li>';
            //    $ablocks = 0;
            //} else {
            if ($colup == $category['cols']) {
                //$style = 'float:left;width:' . $col_width . '%;';
                //$allcols[$colup][] = '<li style="' . $style . '">' . $cart->catalog_block($row['id'],$category['template_id']) . '</li>' . "\n";
                $colup = 0;
            } else {
                //$style = 'float:left;width:' . $col_width . '%;margin-right:3%;';
                //$allcols[$colup][] = '<li style="' . $style . '">' . $cart->catalog_block($row['id'],$category['template_id']) . '</li>' . "\n";
            }
            $allcols[$colup][] = '<li>' . $cart->catalog_block($row['id'], $category['template_id']) . '</li>' . "\n";
            //}
            $ablocks = 1;
            $colup++;
            // $blocks .= '<li style="' . $style . '">' . $cart->catalog_block($row['id'],$category['template_id']) . '</li>' . "\n";
        }
        if (empty($ablocks)) {
            $changesA = array();
            $blocks   = '<ul class="zen_catalog_product_list">';
            $blocks .= new template('catalog_entry_none', $changesA, '0');
            $blocks .= '</ul>';
        } else {
            $up        = 0;
            $upp       = 0;
            $blocks    = '';
            $col_width = floor(100 / $category['cols']);
            if ($category['cols'] > 1) {
                $col_width -= 3;
            }
            while ($category['cols'] > 0) {
                $up++;
                if (empty($allcols[$upp])) {
                    break;
                }
                if ($up == $category['cols']) {
                    $blocks .= '<div style="float:left;width:' . $col_width . '%;"><ul class="zen_catalog_product_list">' . implode('', $allcols[$upp]) . '</ul></div>';
                } else {
                    $blocks .= '<div style="float:left;width:' . $col_width . '%;margin-right:3%;"><ul class="zen_catalog_product_list">' . implode('', $allcols[$upp]) . '</ul></div>';
                }
                $upp++;
                $category['cols']--;
            }
        }
        /**
         * Render the page
         */
        $catalog  = array(
            'category'            => $category,
            'category_list'       => $category_list['0'],
            'total_subcategories' => $category_list['1'],
            'breadcrumbs'         => $breadcrumbs,
            'blocks'              => $blocks,
            'query'               => $query,
            'pagination'          => $paginate->rendered_pages
        );
        $template = new template('catalog', $catalog, '1');
        echo $template;
        exit;

    }

}