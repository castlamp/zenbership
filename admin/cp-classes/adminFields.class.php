<?php
/**
 * This class is used to standardize the generation of fields throughout
 * the administrative control panel.
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

class adminFields {


    private $richtexts = 0;

    private $label = '';
    private $leftText = '';
    private $filterType = '';
    private $rightText = '';
    private $description = '';
    private $typeOverride = '';
    private $selectOptions = array();
    private $id;
    private $min, $max, $maxlength;
    private $placeholder;
    private $value;
    private $autocomplete = false;
    private $fieldBox = false;


    public function setLeftText($text)
    {
        $this->leftText = $text;

        return $this;
    }

    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }

    public function setSelectOptions(array $options)
    {
        $this->selectOptions = $options;

        return $this;
    }

    public function setAutocomplete($auto)
    {
        $this->autocomplete = $auto;

        return $this;
    }

    public function setMin($min)
    {
        $this->min = $min;

        return $this;
    }

    public function setMaxlength($max)
    {
        $this->maxlength = $max;

        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * http://www.w3schools.com/html/html_form_input_types.asp
     * Examples:
     * color
     * date
     * datetime
     * datetime-local
     * email
     * month
     * number
     * range
     * search
     * tel
     * time
     * url
     * week
     *
     * @param $type
     *
     * @return $this
     */
    public function setSpecialType($type)
    {
        if ($type == 'datetime') {
            $type = 'datetime-local';
        }

        $this->typeOverride = $type;

        return $this;
    }

    public function setFilter($filter)
    {
        $this->filterType = $filter;

        return $this;
    }


    public function setLabel($name)
    {
        $this->label = $name;

        return $this;
    }


    public function setRightText($text)
    {
        $this->rightText = $text;

        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }


    public function setDescription($text)
    {
        $this->description = $text;

        return $this;
    }

    public function getId()
    {
        return (! empty($this->id)) ? $this->id : uniqid();
    }


    public function sendBack($string)
    {
        $yep = false;

        $send = '';

        if (! empty($this->label)) {
            $send .= '<label>' . $this->label . '</label>';
        }

        if ($this->fieldBox && ! empty($this->description)) {
            $send .= '<div class="fieldBox">';
        }

        if (! empty($this->leftText) || ! empty($this->rightText)) {
            $yep = true;

            $send .= '<div class="fieldHolder">';
        }

        if (! empty($this->leftText)) {
            $send .= '<div class="fieldLeft">' . $this->leftText . '</div>';

            $this->leftText = '';
        }

        $send .= $string;

        if (! empty($this->rightText)) {
            $send .= '<div class="fieldRight">' . $this->rightText . '</div>';

            $this->rightText = '';
        }

        if (! empty($this->description)) {
            if ($this->fieldBox) {
                $send .= '</div>';
            }

            $send .= '<div class="field_desc">' . $this->description . '</div>';

            $this->description = '';
        }

        if ($yep) {
            $send .= '</div>';
        }

        $this->placeholder = '';
        $this->min = '';
        $this->max = '';
        $this->fieldBox = false;
        $this->maxlength = '';
        $this->id = '';
        $this->autocomplete = '';
        $this->value = '';
        $this->typeOverride = '';

        return $send;
    }


    /**
     * @deprecated But still used by some plugins. So leave it be.
     *
     * @param $field
     * @param string $displayName
     * @param string $description
     *
     * @return string
     */
    public function wrap($field, $displayName = '', $description = '')
    {
        if (! empty($this->label)) {
            $displayName = $this->label;
        }

        $return = '<div class="field">
            <label class="top">' . $displayName . '</label>
            <div class="field_entry_top">' . $field . '</div>';

        if (! empty($description)) {
            $return .= '<p class="field_desc">' . $description . '</p>';
        }

        $return .= '</div>';

        return $return;
    }


    /**
     * @param $fieldName
     * @param string $val
     * @param array $options
     *
     * @return string
     */
    public function radio($fieldName, $val = '', $options = array())
    {
        $return = '<div class="radioInputs">';

        foreach ($options as $value => $displayName) {
            $rid = uniqid();

            $return .= '<input type="radio" id="' . $rid . '" name="' . $fieldName . '" value="' . $value . '"';
            if ($value == $val) {
                $return .= ' checked="checked"';
            }
            $return .= '/><label class="checking" for="' . $rid . '">' . $displayName . '</label>';
        }

        $return .= '</div>';

        return $this->sendBack($return);
    }


    /**
     * @param $fieldName
     * @param string $val
     * @param array $options
     *
     * @return string
     */
    public function select($fieldName, $val = '', $options = array(), $style = '')
    {
        $id = $this->getId();

        if (! empty($this->selectOptions)) {
            $options = $this->selectOptions;
        }

        $return = '<select id="' . $id . '" name="' . $fieldName . '"';
        if (! empty($style)) {
            $return .= ' style="' . $style . '"';
        }
        $return .= '>';
        $return .= '<option value=""';
        if (empty($val)) {
            $return .= ' selected="selected"';
        }
        $return .= '></option>';

        $foundGroup = false;

        foreach ($options as $value => $displayName) {
            if (strpos($value, 'GROUP') !== false) {
                if ($foundGroup) {
                    $return .= '</optgroup>';
                }
                $return .= '<optgroup label="' . $displayName . '">';

                $foundGroup = true;
            } else {
                $return .= '<option value="' . $value . '"';
                if ($val == $value) {
                    $return .= ' selected="selected"';
                }
                $return .= '>' . $displayName . '</option>';
            }
        }

        if ($foundGroup) {
            $return .= '</optgroup>';
        }

        $return .= '</select>';

        return $this->sendBack($return);
    }


    /**
     * @param $name
     * @param string $val
     * @param string $class
     *
     * @return string
     */
    public function timeframe($name, $value = '000100000000', $class = 'req', $advanced_options = '0')
    {
        $admin = new admin;

        if ($class == 'req') {
            $req = 1;
        } else {
            $req = 0;
        }

        $this->fieldBox = true;

        return $this->sendBack($admin->timeframe_field($name, $value, $req, $advanced_options));
    }


    /**
     * @param $fieldName
     * @param $displayName
     * @param string $val
     *
     * @return string
     */
    public function checkbox($fieldName, $options, $val = '')
    {
        $return = '<div class="fieldCheckboxOption">';

        foreach ((array)$options as $displayName) {
            $id = uniqid();

            $return .= '<input type="checkbox" id="' . $id . '" name="' . $fieldName . '" value="1"';

            if ($val == 1 || $val === true) {
                $return .= ' checked="checked"';
            }

            $return .= ' /><label for="' . $id . '" class="checking1">' . $displayName . '</label>';
        }

        $return .= '</div>';

        return $this->sendBack($return);
    }


    /**
     * @param $name
     */
    public function upload($name)
    {
        $string = '<input type="file" name="' . $name . '" />';

        return $this->sendBack($string);
    }


    /**
     * @param array $options
     * @param string $val
     *
     * @return string
     */
    public function checkGroup($fieldname, array $options, array $selectedFeeds)
    {
        $return = '<div class="fieldCheckboxOption">';

        foreach ((array)$options as $value => $name) {
            $id = uniqid();

            $return .= '<input type="checkbox" id="' . $id . '" name="' . $fieldname . '[]" value="' . $value . '"';

            if (in_array($value, $selectedFeeds)) {
                $return .= ' checked="checked"';
            }

            $return .= ' /><label for="' . $id . '" class="checking1">' . $value . '</label>';
        }

        $return .= '</div>';

        return $this->sendBack($return);
    }


    /**
     * @param $fieldName
     * @param string $val
     * @param string $class
     *
     * @return string
     */
    public function textarea($fieldName, $val = '', $class = '', $placeholder = '', $id = '')
    {
        $id = (! empty($id)) ? $id : $this->getId();

        return $this->sendBack('<textarea id="' . $id . '" placeholder="' . $placeholder . '" name="' . $fieldName . '" style="width:100%;height:200px;" class="' . $class . '">' . $val . '</textarea>');
    }


    /**
     * @param string $fieldName
     * @param string $val
     * @param string $height
     * @param string $simple        0 = full editor, 1 = simplified editor
     *
     * @return string
     */
    public function richtext($fieldName, $val = '', $height = '250', $simple = '0')
    {
        $id = $this->getId();

        $get = $this->textarea($fieldName, $val, 'richtext', '', $id);

        $admin = new admin;
        $get .= $admin->richtext('100%', $height . 'px', $id, $this->richtexts++, $simple);

        return $this->sendBack($get);
    }


    /**
     * @param $fieldName
     * @param string $val
     * @param string $class
     */
    public function date($fieldName, $val = '', $class = '', $placeholder = '')
    {
        if (empty($val)) {
            $val = $this->value;
        }

        if ($class == 'req') {
            $req = 1;
        } else {
            $req = 0;
        }

        $admin = new admin;

        return $this->sendBack($admin->datepicker($fieldName, $val, '0', '250', '', '', $req));
    }


    public function datetime($fieldName, $val = '', $class = '', $placeholder = '')
    {
        if (empty($val)) {
            $val = $this->value;
        }

        if ($class == 'req') {
            $req = 1;
        } else {
            $req = 0;
        }

        $admin = new admin;

        //echo $admin->datepicker('event[starts]', '', '1', '250', '1', '10', '1', 'event_starts');
        return $this->sendBack($admin->datepicker($fieldName, $val, '1', '250', '1', '10', $req, $this->id));
    }


    /**
     * @param $fieldName
     * @param string $val
     * @param string $class
     *
     * @return string
     */
    public function string($fieldName, $val = '', $class = '', $placeholder = '', $style = '')
    {
        if ($this->typeOverride == 'date') {
            return $this->date($fieldName, $val, $class, $placeholder);
        } else if ($this->typeOverride == 'datetime-local') {
            return $this->datetime($fieldName, $val, $class, $placeholder);
        }

        $id = $this->getId();

        $type = (! empty($this->typeOverride)) ? $this->typeOverride : 'text';

        if ($this->typeOverride == 'date') {
            if (empty($val)) {
                $val = ''; // get_date();
            } else {
                $val = date('Y-m-d', strtotime($val));
            }
        }

        if (! empty($this->placeholder)) {
            $placeholder = $this->placeholder;
        }

        if (empty($val)) {
            if (! empty($this->value)) {
                $val = $this->value;
            }
        }

        if (! empty($val) && $this->typeOverride == 'datetime-local') {
            $val = date("Y-m-d\TH:i:s", strtotime($val));
        }

        $string = '<input';
        if (! empty($style)) {
            $string .= ' style="' . $style . '"';
        }
        $string .= ' placeholder="' . $placeholder . '" type="' . $type . '" name="' . $fieldName . '" id="' . $id . '" value="' . $val . '" style="width:100%;" class="' . $class . '"';

        if (! $this->autocomplete) {
            $string .= ' autocomplete="off"';
        }

        if (! empty($this->min)) {
            $string .= ' min="' . $this->min . '"';
        }

        if (! empty($this->max)) {
            $string .= ' max="' . $this->max . '"';
        }

        if (! empty($this->maxlength)) {
            $string .= ' maxlength="' . $this->maxlength . '"';
        }

        $string .= '';

        $string .= ' />';

        $this->typeOverride = '';

        return $this->sendBack($string);
    }


    /**
     * Auto-populate member list.
     *
     * @param $fieldName
     * @param string $val
     *
     * @return string
     */
    public function memberList($fieldName, $val = '', $class = '')
    {
        $id = $this->getId();

        if (! empty($val)) {
            $user = new user;
            $username = $user->get_username($val);
        } else {
            $username = '';
        }

        $this->rightText = "<a href=\"null.php\" onclick=\"return get_list('member','" . $id . "_id','" . $id . "');\"><img
        src=\"imgs/icon-list.png\" class=\"icon\" alt=\"Select from list\" title=\"Select from list\" /></a>";

        return $this->sendBack("<input placeholder=\"Begin typing the member's username or click the list icon to the right...\" type=\"text\" value=\"" . $username . "\" name=\"username_dud\" id=\"" . $id . "\"
autocomplete=\"off\" onkeyup=\"return autocom(this.id,'id','username','ppSD_members','username','members');\"
style=\"\" class=\"" . $class . "\" /><input type=\"hidden\" name=\"$fieldName\" id=\"" . $id . "_id\"
        value=\"" . $val . "\" />");
    }


    /**
     * Auto-populate contact list.
     *
     * @param $fieldName
     * @param string $val
     *
     * @return string
     */
    public function contactList($fieldName, $val = '', $class = '')
    {
        $id = $this->getId();

        if (! empty($val)) {
            $user = new contact;
            $name = $user->get_name($val);
        } else {
            $name = '';
        }

        $this->rightText = "<a href=\"null.php\" onclick=\"return get_list('contact','" . $id . "_id','" . $id . "');\"><img
        src=\"imgs/icon-list.png\" alt=\"Select from list\" title=\"Select from list\" /></a>";

        return $this->sendBack("<input placeholder=\"Begin typing the contact's last name or click the list icon to the right...\" type=\"text\" value=\"" . $name . "\" name=\"username_dud\" id=\"" . $id . "\"
autocomplete=\"off\" onkeyup=\"return autocom(this.id,'contact_id','first_name,last_name','ppSD_contact_data','last_name','contacts');\"
style=\"\" class=\"" . $class . "\" /><input type=\"hidden\" name=\"$fieldName\" id=\"" . $id . "_id\"
        value=\"" . $val . "\" />");
    }


    public function formList($fieldName, $val = '', $class = '')
    {
        $id = $this->getId();

        if (! empty($val)) {
            $form = new form;
            $name = $form->get_form_name($val);
        } else {
            $name = '';
        }

        $this->rightText = "<a href=\"null.php\" onclick=\"return get_list('forms','" . $id . "_id','" . $id . "');\"><img
        src=\"imgs/icon-list.png\" alt=\"Select from list\" title=\"Select from list\" /></a>";

        return $this->sendBack("<input placeholder=\"Begin typing the form's name or click the list icon to the right...\" type=\"text\" value=\"" . $name . "\" name=\"form_dud\" id=\"" . $id . "\"
autocomplete=\"off\" onkeyup=\"return autocom(this.id,'id','name','ppSD_forms','name','forms');\"
style=\"\" class=\"" . $class . "\" /><input type=\"hidden\" name=\"$fieldName\" id=\"" . $id . "_id\"
        value=\"" . $val . "\" />");
    }


    /**
     * @param $fieldName
     * @param string $val
     * @param string $class
     *
     * @return string
     */
    public function contentList($fieldName, $val = '', $class = '')
    {
        $id = $this->getId();

        if (! empty($val)) {
            $content = new content;
            $name = $content->get_name($val);
        } else {
            $name = '';
        }

        $this->rightText = "<a href=\"null.php\" onclick=\"return get_list('content','" . $id . "_id','" . $id . "');\"><img
        src=\"imgs/icon-list.png\" alt=\"Select from list\" title=\"Select from list\" /></a>";

        return $this->sendBack("<input placeholder=\"Begin typing the content's name or click the list icon to the right...\" type=\"text\" value=\"" . $name . "\" name=\"content_dud\" id=\"" . $id . "\"
autocomplete=\"off\" onkeyup=\"return autocom(this.id,'id','name','ppSD_content','name','content');\"
style=\"\" class=\"" . $class . "\" /><input type=\"hidden\" name=\"$fieldName\" id=\"" . $id . "_id\"
        value=\"" . $val . "\" />");
    }


    /**
     * @param $fieldName
     * @param string $val
     * @param string $class
     *
     * @return string
     */
    public function memberTypeList($fieldName, $val = '', $class = '')
    {
        $id = $this->getId();

        if (! empty($val)) {
            $user = new user;
            $get = $user->get_member_type($val);
            $name = $get['name'];
        } else {
            $name = '';
        }

        $this->rightText = "<a href=\"null.php\" onclick=\"return get_list('member_types','" . $id . "_id','" . $id . "');\"><img
        src=\"imgs/icon-list.png\" class=\"icon\" width=16 height=16 alt=\"Select from list\" title=\"Select from list\"  /></a>";

        return $this->sendBack("<input placeholder=\"Begin typing a member type or click the list icon to the right...\" type=\"text\" value=\"" . $name . "\" name=\"member_type_dud\" id=\"" . $id . "\"
autocomplete=\"off\" onkeyup=\"return autocom(this.id,'id','name','ppSD_member_types','name','member_types');\"
style=\"\" class=\"" . $class . "\" /><input type=\"hidden\" name=\"$fieldName\" id=\"" . $id . "_id\"
        value=\"" . $val . "\" />");
    }


    /**
     * @param $fieldName
     * @param string $val
     * @param string $class
     *
     * @return string
     */
    public function staffList($fieldName, $val = '', $class = '')
    {
        $id = $this->getId();

        if (! empty($val)) {
            $admin = new admin;
            $get = $admin->get_employee('', $val);
            $name = $get['username'];
        } else {
            $name = '';
        }

        $this->rightText = "<a href=\"null.php\" onclick=\"return get_list('staff','" . $id . "_id','" . $id . "');\"><img
        src=\"imgs/icon-list.png\" alt=\"Select from list\" title=\"Select from list\"  /></a>";

        return $this->sendBack("<input placeholder=\"Begin typing an employee's name or click the list icon to the right...\" type=\"text\" value=\"" . $name . "\" name=\"staff_dud\" id=\"" . $id . "\"
autocomplete=\"off\" onkeyup=\"return autocom(this.id,'id','username','ppSD_staff','username,first_name,last_name','staff');\"
style=\"\" class=\"" . $class . "\" /><input type=\"hidden\" name=\"$fieldName\" id=\"" . $id . "_id\"
        value=\"" . $val . "\" />");
    }

    /**
     * @param $fieldName
     * @param string $val
     * @param string $class
     *
     * @return string
     */
    public function fieldList($fieldName, $val = '', $class = '')
    {
        $id = $this->getId();

        if (! empty($val)) {
            $field = new field;
            $get = $field->get_field_name($val);
        } else {
            $name = '';
        }

        $this->rightText = "<a href=\"null.php\" onclick=\"return get_list('fields','" . $id . "_id','" . $id . "');\"><img
        src=\"imgs/icon-list.png\" alt=\"Select from list\" title=\"Select from list\"  /></a>";

        return $this->sendBack("<input placeholder=\"Begin typing a field name or click the list icon to the right...\" type=\"text\" value=\"" . $name . "\" name=\"staff_dud\" id=\"" . $id . "\"
autocomplete=\"off\" onkeyup=\"return autocom(this.id,'id','display_name','ppSD_fields','display_name','fields');\"
style=\"\" class=\"" . $class . "\" /><input type=\"hidden\" name=\"$fieldName\" id=\"" . $id . "_id\"
        value=\"" . $val . "\" />");
    }



    public function sourceList($fieldName, $val = '', $class = '', $addButton = true)
    {
        $id = $this->getId();

        if (! empty($val)) {
            $source = new source;
            $get = $source->get_source($val);
            $name = $get['source'];
        } else {
            $name = '';
        }

        $this->rightText = "<a href=\"null.php\" onclick=\"return get_list('source','" . $id . "_id','" . $id . "');\"><img
        src=\"imgs/icon-list.png\" alt=\"Select from list\" title=\"Select from list\" /></a>";

        if ($addButton) {
            $this->rightText .= "<a href=\"null.php\" onclick=\"return popup('source-edit','');\">
        <img src=\"imgs/icon-quickadd.png\" alt=\"Add\" title=\"Add\" /></a>";
        }

        return $this->sendBack("<input placeholder=\"Begin typing a source or click the list icon to the right...\" type=\"text\" value=\"" . $name . "\" name=\"source_dud\" id=\"" . $id . "\"
autocomplete=\"off\" onkeyup=\"return autocom(this.id,'id','source','ppSD_sources','source','sources');\"
style=\"\" class=\"" . $class . "\" /><input type=\"hidden\" name=\"$fieldName\" id=\"" . $id . "_id\"
        value=\"" . $val . "\" />");
    }


    /**
     * @param $fieldName
     * @param string $val
     * @param string $class
     * @param string $filter
     *
     * @return string
     */
    public function productList($fieldName, $val = '', $class = '', $filter = '')
    {
        $id = $this->getId();

        if (! empty($this->filterType)) {
            $filter = $this->filterType;
        }

        if (! empty($val)) {
            $product = new product;
            $name = $product->get_name($val);
        } else {
            $name = '';
        }

        if (! empty($filter)) {
            if ($filter == 'subscriptions') {
                $type = 'subscription_products';
            } else {
                $type = 'products';
            }
        } else {
            $type = 'products';
        }

        $this->rightText = "<a href=\"null.php\" onclick=\"return get_list('" . $type . "','" . $id . "_id','" . $id . "');\"><img
        src=\"imgs/icon-list.png\" alt=\"Select from list\" title=\"Select from list\" /></a>";

        return $this->sendBack("<input placeholder=\"Begin typing the product's name or click the list icon to the right...\" type=\"text\" value=\"" . $name . "\" name=\"product_dud\" id=\"" . $id . "\"
autocomplete=\"off\" onkeyup=\"return autocom(this.id,'id','name','ppSD_products','name','products');\"
style=\"\" class=\"" . $class . "\" /><input type=\"hidden\" name=\"$fieldName\" id=\"" . $id . "_id\"
        value=\"" . $val . "\" />");
    }




    public function couponList($fieldName, $val = '', $class = '')
    {
        $id = $this->getId();

        if (! empty($val)) {
            $cart = new cart;
            $name = $cart->get_savings_code($val);
        } else {
            $name = '';
        }

        return $this->sendBack("<input placeholder=\"Begin typing the coupon code\" type=\"text\" value=\"" . $name . "\" name=\"promo_dud\" id=\"" . $id . "\"
autocomplete=\"off\" onkeyup=\"return autocom(this.id,'id','id','ppSD_cart_coupon_codes','id','promo_code');\"
style=\"\" class=\"" . $class . "\" /><input type=\"hidden\" name=\"$fieldName\" id=\"" . $id . "_id\"
        value=\"" . $val . "\" />");
    }


    public function accountList($fieldName, $val = '', $class = '')
    {
        $id = $this->getId();

        if (! empty($val)) {
            $account = new account;
            $name = $account->get_name($val);
        } else {
            $name = '';
        }

        $this->rightText = "<a href=\"null.php\" onclick=\"return get_list('account','" . $id . "_id','" . $id . "');\"><img
        src=\"imgs/icon-list.png\" alt=\"Select from list\" title=\"Select from list\" /></a><a href=\"null.php\" onclick=\"return popup('account-add','');\">
        <img src=\"imgs/icon-quickadd.png\" alt=\"Add\" title=\"Add\" /></a>";

        return $this->sendBack("<input placeholder=\"Begin typing the account's name or click the list icon to the right...\" type=\"text\" value=\"" . $name . "\" name=\"account_dud\" id=\"" . $id . "\"
autocomplete=\"off\" onkeyup=\"return autocom(this.id,'id','name','ppSD_accounts','name','accounts');\"
style=\"\" class=\"" . $class . "\" /><input type=\"hidden\" name=\"$fieldName\" id=\"" . $id . "_id\"
        value=\"" . $val . "\" />");
    }


    /**
     * @param $fieldName
     * @param string $val
     * @param string $class
     *
     * @return string
     */
    public function labelList($fieldName, $val = '', $class = '')
    {
        $id = $this->getId();

        if (! empty($val)) {
            $name = $val;
        } else {
            $name = '';
        }

        $this->rightText = "<a href=\"null.php\" onclick=\"return get_list('labels','" . $id . "_id','" . $id . "');\"><img
        src=\"imgs/icon-list.png\" alt=\"Select from list\" title=\"Select from list\" /></a>";

        return $this->sendBack("<input placeholder=\"Begin typing the label or click the list icon to the right...\" type=\"text\" value=\"" . $name . "\" name=\"upload_dud\" id=\"" . $id . "\"
autocomplete=\"off\" onkeyup=\"return autocom(this.id,'label','label','ppSD_uploads','label','uploads');\"
style=\"\" class=\"" . $class . "\" /><input type=\"hidden\" name=\"$fieldName\" id=\"" . $id . "_id\"
        value=\"" . $val . "\" />");
    }


    /**
     * @param $fieldName
     * @param string $val
     * @param string $class
     *
     * @return string
     */
    public function transactionList($fieldName, $val = '', $class = '')
    {
        $id = $this->getId();

        if (! empty($val)) {
            $name = $val;
        } else {
            $name = '';
        }

        // $this->rightText = "<a href=\"null.php\" onclick=\"return get_list('labels','" . $id . "_id','" . $id . "');\"><img src=\"imgs/icon-list.png\" alt=\"Select from list\" title=\"Select from list\" /></a>";

        return $this->sendBack("<input placeholder=\"Begin typing a transaction ID to select...\" type=\"text\" value=\"" . $name . "\" name=\"order_dud\" id=\"" . $id . "\"
autocomplete=\"off\" onkeyup=\"return autocom(this.id,'id','id','ppSD_cart_sessions','id','transactions');\"
style=\"\" class=\"" . $class . "\" /><input type=\"hidden\" name=\"$fieldName\" id=\"" . $id . "_id\"
        value=\"" . $val . "\" />");
    }



    /**
     * Generates a preset address form
     *
     * Needs to be a separate class...
     *
     * @param string $prefix
     * @param array $values
     * @param array $exclude
     *
     * @return string
     */
    public function addressForm($prefix = '', $values = array(), $exclude = array())
    {
        $form = '<div class="innerForm">';

        $fields = array(
            'first_name' => '',
            'last_name' => '',
            'address_line_1' => '',
            'address_line_2' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'country' => '',
        );

        // Exclusions
        // Example: invoice edit client form
        foreach ($fields as $name => $val) {
            if (in_array($name, $exclude)) {
                unset($fields[$name]);

                continue;
            }
        }

        // Populate values?
        if (! empty($values)) {
            foreach ($fields as $name => $val) {

                if (! empty($values[$name])) {
                    if (! empty($prefix)) {
                        $fields[$prefix . '[' . $name . ']'] = $values[$name];

                        unset($fields[$name]);
                    } else {
                        $fields[$name] = $values[$name];
                    }
                } else {
                    if (! empty($prefix)) {
                        $fields[$prefix . '[' . $name . ']'] = '';

                        unset($fields[$name]);
                    } else {
                        $fields[$name] = '';
                    }
                }
            }
        }

        // Build it...
        foreach ($fields as $key => $value) {
            if (strpos($key, 'first_name') !== false) {
                $form .= $this->string($key, $value, '', 'First Name', 'width:50%;');
            }
            else if (strpos($key, 'last_name') !== false) {
                $form .= $this->string($key, $value, '', 'Last Name', 'width:50%;');
            }
            else if (strpos($key, 'address_line_1') !== false) {
                $form .= $this->string($key, $value, '', 'Address Line 1');
            }
            else if (strpos($key, 'address_line_2') !== false) {
                $form .= $this->string($key, $value, '', 'Line 2 (example: Apt 2)');
            }
            else if (strpos($key, 'city') !== false) {
                $form .= $this->string($key, $value, '', 'City', 'width:33.3333%;');
            }
            else if (strpos($key, 'state') !== false) {
                $form .= $this->select($key, $value, state_array(), 'width:33.3333%;');
            }
            else if (strpos($key, 'zip') !== false) {
                $form .= $this->string($key, $value, '', 'Zip/Postal', 'width:33.3333%;');
            }
            else if (strpos($key, 'country') !== false) {
                $form .= $this->select($key, $value, country_array());
            }
        }

        $form .= '</div>';

        return $form;
    }


    public function creditCardForm($prefix = '', $values = array())
    {
        $form = '<div class="credit_card_container"><div class="col50l"><div class="credit_card_front">';

        $fields = array(
            'cc_number' => '',
            'card_exp_mm' => '',
            'card_exp_yy' => '',
            'cvv' => '',
        );

        if (! empty($values)) {
            foreach ($fields as $name => $val) {
                if (! empty($values[$name])) {
                    $fields[$name] = $values[$name];
                }
            }
        }

        if (! empty($prefix)) {
            $fields[$prefix . '[cc_number]'] = $fields['cc_number'];
            $fields[$prefix . '[card_exp_mm]'] = $fields['card_exp_mm'];
            $fields[$prefix . '[card_exp_yy]'] = $fields['card_exp_yy'];
            $fields[$prefix . '[cvv]'] = $fields['cvv'];

            unset($fields['cc_number']);
            unset($fields['card_exp_mm']);
            unset($fields['card_exp_yy']);
            unset($fields['cvv']);
        }

        $up = 0;
        foreach ($fields as $key => $value) {
            $up++;
            if ($up == 1) {
                $form .= '<div class="credit_card_number">' .
                    $this->string($key, $value, '', '5111222233334444', 'width:100%;');
            }
            else if ($up == 2) {
                $form .= $this
                    ->setMin('0')
                    ->setMax('12')
                    ->setSpecialType('number')
                    ->string($key, $value, '', '03', 'width:100px;');
            }
            else if ($up == 3) {
                $form .= '<span style="text-align:center;width:20px;display:inline-block;">/</span>' . $this
                    ->setMin(date('y'))
                    ->setSpecialType('number')
                    ->string($key, $value, '', '22', 'width:100px;') . '</div>';
            }
            else if ($up == 4) {
                $form .= '</div></div><div class="col50r"><div class="credit_card_back"><div class="credit_card_cvv">' . $this
                        ->setSpecialType('number')
                        ->setMaxlength('4')
                        ->string($key, $value, '', 'CVV', 'margin-left:20px;width:100px;') . '</div>';
            }
        }

        $form .= '</div></div></div>';

        return $form;
    }


    public function memberForm($prefix = '', $values = array())
    {
        $form = '<div class="innerForm">';

        $fields = array(
            'username' => '',
            'email' => '',
            'first_name' => '',
            'last_name' => '',
            'source' => '',
            'account' => '',
            'type' => '',
            'notify' => '',
        );

        if (! empty($values)) {
            foreach ($fields as $name => $val) {
                if (! empty($values[$name])) {
                    $fields[$name] = $values[$name];
                }
            }
        }

        if (! empty($prefix)) {
            $fields[$prefix . '[username]'] = $fields['username'];
            $fields[$prefix . '[email]'] = $fields['email'];
            $fields[$prefix . '[source]'] = $fields['source'];
            $fields[$prefix . '[account]'] = $fields['account'];
            $fields[$prefix . '[first_name]'] = $fields['first_name'];
            $fields[$prefix . '[last_name]'] = $fields['last_name'];
            $fields[$prefix . '[type]'] = $fields['type'];
            $fields[$prefix . '[notify]'] = $fields['notify'];

            unset($fields['username']);
            unset($fields['email']);
            unset($fields['source']);
            unset($fields['account']);
            unset($fields['first_name']);
            unset($fields['last_name']);
            unset($fields['type']);
            unset($fields['notify']);
        }


        $up = 0;
        foreach ($fields as $key => $value) {
            $up++;
            if ($up == 1) {
                $form .= $this->string($key, $value, '', 'username', 'width:50%;');
            }
            else if ($up == 2) {
                $form .= $this->string($key, $value, '', 'someone@email.com', 'width:50%;');
            }
            else if ($up == 3) {
                $form .= $this->string($key, $value, '', 'First Name', 'width:50%;');
            }
            else if ($up == 4) {
                $form .= $this->string($key, $value, '', 'Last Name', 'width:50%;');
            }
            else if ($up == 5) {
                $form .= $this->sourceList($key, $value, '', false);
            }
            else if ($up == 6) {
                $form .= $this->accountList($key, $value, '', false);
            }
            else if ($up == 7) {
                $admin = new admin;
                $memTypes = $admin->member_types('', 'array');

                $form .= $this
                    ->setDescription('Select a member type for this member.')
                    ->select($key, $value, $memTypes);
            }
            else if ($up == 8) {
                $form .= '<label>Would you like to notify the member of his/her new account?</label>';
                $form .= $this->radio($key, $value, array(
                    '1' => 'E-mail the member about his/her new membership',
                    '0' => 'Do NOT e-mail the new member.',
                ));
            }
        }

        $form .= '</div>';

        return $form;
    }


    public function contactForm($prefix = '', $values = array())
    {
        $form = '<div class="innerForm">';

        $fields = array(
            'email' => '',
            'company_name' => '',
            'first_name' => '',
            'last_name' => '',
            'source' => '',
            'account' => '',
            'owner' => '',
            'expected_value' => '',
        );

        if (! empty($values)) {
            foreach ($fields as $name => $val) {
                if (! empty($values[$name])) {
                    $fields[$name] = $values[$name];
                }
            }
        }

        if (! empty($prefix)) {
            $fields[$prefix . '[email]'] = $fields['email'];
            $fields[$prefix . '[company_name]'] = $fields['company_name'];
            $fields[$prefix . '[first_name]'] = $fields['first_name'];
            $fields[$prefix . '[last_name]'] = $fields['last_name'];
            $fields[$prefix . '[source]'] = $fields['source'];
            $fields[$prefix . '[account]'] = $fields['account'];
            $fields[$prefix . '[owner]'] = $fields['owner'];
            $fields[$prefix . '[expected_value]'] = $fields['expected_value'];

            unset($fields['email']);
            unset($fields['company_name']);
            unset($fields['first_name']);
            unset($fields['last_name']);
            unset($fields['source']);
            unset($fields['account']);
            unset($fields['owner']);
            unset($fields['expected_value']);
        }

        $up = 0;
        foreach ($fields as $key => $value) {
            $up++;
            if ($up == 1) {
                $form .= '<div class="col50l"><label>E-Mail</label>';
                $form .= $this
                    ->string($key, $value, '', 'someone@email.com', '');
                $form .= '</div>';
            }
            else if ($up == 2) {
                $form .= '<div class="col50r"><label>Company Name</label>';
                $form .= $this
                    ->string($key, $value, '', 'Company Name', '');
                $form .= '</div>';
            }
            else if ($up == 3) {
                $form .= '<div class="col50l"><label>First Name</label>';
                $form .= $this
                    ->string($key, $value, '', 'First Name', '');
                $form .= '</div>';
            }
            else if ($up == 4) {
                $form .= '<div class="col50r"><label>Last Name</label>';
                $form .= $this
                    ->string($key, $value, '', 'Last Name', '');
                $form .= '</div>';
            }
            else if ($up == 5) {
                $form .= '<div class="col50l"><label>Generated from Source</label>';
                $form .= $this
                    ->sourceList($key, $value, '', false);
                $form .= '</div>';
            }
            else if ($up == 6) {
                $form .= '<div class="col50r"><label>Add to Account</label>';
                $form .= $this
                    ->accountList($key, $value, '', false);
                $form .= '</div>';
            }
            else if ($up == 7) {
                $form .= '<div class="col50l"><label>Assign to employee</label>';
                $form .= $this
                    ->staffList($key, $value, '', false);
                $form .= '</div>';
            }
            else if ($up == 8) {
                $form .= '<div class="col50r"><label>Expected Value</label>';
                $form .= $this
                    ->setPlaceholder('1500.00')
                    ->setRightText(CURRENCY_SYMBOL)
                    ->string($key, $value, '');
                $form .= '</div>';
            }
        }

        $form .= '</div>';

        return $form;
    }

}