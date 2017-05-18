<?php

require_once dirname(dirname(dirname(__FILE__))) . '/cp-classes/OptionsContract.class.php';

/**
 * An example of the pre-processing we can do on options when they are updated.
 *
 * "example" matches the option ID in the database.
 */

class additional_search_contacts implements OptionsContract {

    private $always = array(
        'email',
        'last_name',
        'id',
    );

    public function processValue($value)
    {
        $final = array();

        $exp = explode(',', $value);
        foreach ($exp as $anItem) {
            if (! in_array($anItem, $this->always)) {
                $final[] = $anItem;
            }
        }

        return implode(',', $final);
    }


    public function processGet($defaultGetValue)
    {
        return $defaultGetValue . ',email,last_name,id';
    }

}