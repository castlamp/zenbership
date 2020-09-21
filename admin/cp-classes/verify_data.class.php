<?php


/**
 * Data Validation Class
 *
 * This class is included in this project
 * but belongs to the "Castlamp Framework".
 * While the overall project is copyrighted to
 * "Penn Foster", the contents of this file are
 * distributed under the "GPL3" license:
 * http://www.gnu.org/licenses/gpl.html
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
 * @version     v1.0
 *
 * Zenbership:
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
class verify_data
{

    protected $data;

    protected $rules;

    protected $current_name;

    protected $current_value;

    protected $current_rule;

    protected $conditions;

    public $results;

    public $errors;

    public $ajax_errors;

    public $error_found;

    public $plain_english;

    public $final_data;

    /**
     * @param array $form_data  Data we are working with. Generally POST array from a form.
     * @param array $rules      Array of supplemental rules for fields
     *                          Options: email, date, phone, url, zip, postal_code,
     *                          alpha, numeric, alphanumeric, minlength,
     *                          maxlength, sanitize, nosymbols, zippostal,
     *                          sanitize, badwords, censor, date, datetime
     *                          Example Array:
     *                  $rules = array(
     *                      'email' => array('email','required'),
     *                      'phone' => array('phone'),
     *                      'about_me' => array('only_if:field=value','sanitize','minlength:25,maxlength:100'),
     *                  );
     * @param array $conditions Array of conditions that would remove a
     *                          a field from the rules array.
     *                          Example Array:
     *                  $conditions = array(
     *                      'email' => array(

    'contact_method' => 'phone',
     *                          'contact_method' => 'mail',
     *                      );
     *                  );
     *                          In the above example, email would be removed from the $rules array, meaning it
     *                          would not be validated, if "contact_method"
     *                          So when building $rules, you need to put ALL possible rules, and have conditions
     *                          remove them if they don't need to be validated.

     */
    function __construct($form_data, $rules = array(), $conditions = array())
    {
        $this->form_data  = $form_data;
        $this->rules      = $rules;
        $this->conditions = $conditions;
        $this->check_conditions();
        $this->process();
        $this->clean_errors();

    }

    function check_conditions()
    {
        foreach ($this->conditions as $field_name => $conds) {
            $remove_rule = '0';
            foreach ($conds as $check) {
                $check_exp   = explode('=', $check);
                $check_field = $check_exp['0'];
                $check_value = $check_exp['1'];
                if ($this->form_data[$check_field] == $check_value) {
                    $remove_rule = '1';
                }
            }
            if ($remove_rule == 1) {
                unset($this->rules[$field_name]);
            }
        }

    }

    /**
     * Processes the form and runs
     * all relevant rules.

     */
    function process()
    {
        // Loop submitted items.
        foreach ($this->form_data as $name => $value) {
            $this->current_name  = $name;
            $this->current_value = $value;
            $this->check_rules();
        }

    }

    /**
     * Checks if a field is required,
     * and if it is, adds the error.

     */
    function check_rules()
    {
        // Loop rules array
        if (array_key_exists($this->current_name, $this->rules)) {
            $rules = $this->rules[$this->current_name];
            if (! in_array('required', $rules) && empty($this->form_data[$this->current_name])) {
                // no need to validate an empty field
                // if it isn't required and wasn't submitted.
            } else {
                foreach ($rules as $aRule) {
                    $this->current_rule = $aRule;
                    $this->check_individual_rule();
                }
            }
        }

        // Complete the process.
        $this->final_data[$this->current_name] = $this->current_value;
    }

    /**
     * Route the rule check.

     */
    function check_individual_rule()
    {
        switch ($this->current_rule) {
            case 'sanitize':
                $this->current_value = filter_var($this->current_value, FILTER_SANITIZE_STRING);
                break;
            case 'badwords':
                $this->curse_words();
                break;
            case 'censor':
                $this->censor_curse_words();
                break;
            case 'date':
                $this->check_date();
                break;
            case 'datetime':
                $this->check_datetime();
                break;
            case 'email':
                $this->check_email();
                break;
            case 'phone':
                $this->phone();
                break;
            case 'url':
                $this->check_url();
                break;
            case 'zip':
                $this->zip();
                break;
            case 'postal_code':
                $this->postal_code();
                break;
            case 'alpha':
                $this->alpha();
                break;
            case 'numeric':
                $this->numeric();
                break;
            case 'alphanumeric':
                $this->alphanumeric();
                break;
            case 'nosymbols':
                $this->nosymbols();
                break;
            case 'basicsymbols':
                $this->basicsymbols();
                break;
            case 'minlength':
                $this->minlength();
                break;
            case 'maxlength':
                $this->maxlength();
                break;
            case 'zippostal':
                $this->zippostal();
                break;
            case 'required':
                $this->check_required();
                break;

        }

    }

    /**
     * Validates a phone number.

     */
    function phone()
    {
        if (!preg_match('/^[0-9]{10,11}+$/', $this->current_value)) {
            $this->apply_error();

        }

    }

    /**
     * Throws an error if a curse
     * words is detected.

     */
    function curse_words()
    {
        $curse_words = $this->curse_array();
        foreach ($curse_words as $word) {
            $pattern = '/^' . $word . '/';
            if (preg_match($pattern, $this->current_value)) {
                $this->apply_error();

            }

        }

    }

    /**
     * Checks if date was submitted in the
     * proper YYYY-MM-DD format.

     */
    function check_date()
    {
        if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $this->current_value, $matches)) {
            if (!checkdate($matches[2], $matches[3], $matches[1])) {
                $this->apply_error();

            }

        }

    }

    /**
     * Checks if date was submitted in the
     * proper YYYY-MM-DD HH:MM:SS format

     */
    function check_datetime()
    {
        $good = '0';
        if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $this->current_value, $matches)) {
            if (!checkdate($matches[2], $matches[3], $matches[1])) {
                $this->apply_error();

            }

        }

    }

    /**
     * Detects and censors curse words.

     */
    function censor()
    {
        $curse_words = $this->curse_array();
        preg_replace($curse_words, '****', $this->current_value);

    }

    /**
     * Validates a zip code.

     */
    function zip()
    {
        if (
            !preg_match('/^[0-9]{5}+$/', $this->current_value) &&
            !preg_match('/^[0-9]{5}-[0-9]{4}+$/', $this->current_value)
        ) {
            $this->apply_error();

        }

    }

    /**
     * Validates a zip or postal code.

     */
    function zippostal()
    {
        if (
            !preg_match('/^[0-9]{5}+$/', $this->current_value) &&
            !preg_match('/^[0-9]{5}-[0-9]{4}+$/', $this->current_value) &&
            !preg_match('/^[A-Z]{1}[0-9]{1}){3}+$/', $this->current_value)
        ) {
            $this->apply_error();

        }

    }

    /**
     * Validates a postal code.

     */
    function postal_code()
    {
        if (!preg_match('/^[A-Z]{1}[0-9]{1}){3}+$/', $this->current_value)) {
            $this->apply_error();

        }

    }

    /**
     * Only allows alpha-numeric.

     */
    function alphanumeric()
    {
        if (!preg_match('/^[A-Za-z0-9]+$/', $this->current_value)) {
            $this->apply_error();

        }

    }

    /**
     * Only allows alpha-numeric and spaces.

     */
    function nosymbols()
    {
        if (!preg_match('/^[A-Za-z0-9 ]+$/', $this->current_value)) {
            $this->apply_error();

        }

    }

    /**
     * Only allows alpha-numeric and spaces.

     */
    function basicsymbols()
    {
        if (!preg_match('/^[A-Za-z0-9\_\-]+$/', $this->current_value)) {
            $this->apply_error();
        }
    }

    /**
     * Only allows numeric.

     */
    function numeric()
    {
        if (!preg_match('/^[0-9]+$/', $this->current_value)) {
            $this->apply_error();

        }

    }

    /**
     * Only allows alpha.

     */
    function alpha()
    {
        if (!preg_match('/^[A-Za-z]+$/', $this->current_value)) {
            $this->apply_error();

        }

    }

    /**
     * Checks minimum length.

     */
    function minlength()
    {
        $options = explode('=', $this->current_rule);
        if (strlen($this->current_value) < $options['1']) {
            $this->apply_error();

        }

    }

    /**
     * Checks maximum length.

     */
    function maxlength()
    {
        $options = explode('=', $this->current_rule);
        if (strlen($this->current_value) > $options['1']) {
            $this->apply_error();

        }

    }

    /**
     * Checks if a field is required,
     * and if it is, adds the error.

     */
    function check_required()
    {
        $check = trim($this->current_value);
        if (empty($check)) {
            $this->apply_error();

        }

    }

    /**
     * Validates a URL.

     */
    function check_url()
    {
        if (!filter_var($this->current_value, FILTER_VALIDATE_URL)) {
            $this->apply_error();

        }

    }

    /**
     * Validates an email.

     */
    function check_email()
    {
        if (!filter_var($this->current_value, FILTER_VALIDATE_EMAIL)) {
            $this->apply_error();

        }

    }

    /**
     * Add an error to the array/list of errors.

     */
    function apply_error($option = '')
    {
        // Possible errors
        $use_error      = $this->get_error();
        $use_ajax_error = $this->get_ajax_error();
        // Prepare replacements for
        // this error.
        $find    = array(
            '%name%',
            '%option%',
        );
        $replace = array(
            $this->pretty_names($this->current_name),
            $option,
        );
        // Apply error
        $this->error_found                                           = '1';
        $this->errors[$this->current_name][$this->current_rule]      = '1';
        $this->ajax_errors[$this->current_name][$this->current_rule] = str_replace($find, $replace, $use_ajax_error);
        $this->plain_english .= '<li>' . str_replace($find, $replace, $use_error) . '</li>';

    }

    /**
     * Gets a readable error.

     */
    function get_error()
    {
        $error_array = array(
            'required'     => '%name% is required.',
            'email'        => '%name% is not a valid email.',
            //'date'         => '%name% is not a valid date.',
            'phone'        => '%name% is not a valid phone number. Please input only numbers.',
            'url'          => '%name% is not a valid URL.',
            'zip'          => '%name% is not a properly formatted zip code.',
            'postal_code'  => '%name% is not a properly formatted postal code.',
            'zippostal'    => '%name% is not a properly formatted zip code or postal code.',
            'alpha'        => '%name% must consist of letters only.',
            'numeric'      => '%name% must be numeric.',
            'alphanumeric' => '%name% must be alpha-numeric.',
            'minlength'    => '%name% must be longer than %option% characters.',
            'maxlength'    => '%name% cannot be greater than %option% characters.',
            'nosymbols'    => '%name% can only contain letters, numbers, and spaces.',
            'basicsymbols' => '%name% can only contain letter, numbers, and basic symbols.',
            'badwords'     => 'Inappropriate language is not permitted.',
            'date'         => 'Date must be a valid YYYY-MM-DD format',
            'datetime'     => 'Date and time must be in a valid YYYY-MM-DD HH:MM:SS format.'
        );

        return $error_array[$this->current_rule];

    }

    /**
     * Gets a readable error for ajax
     * context. Often don't need or
     * want the field name in there.

     */
    function get_ajax_error()
    {
        $error_ajax_array = array(
            'required'     => 'Required.',
            'email'        => 'This is not a valid email.',
            //'date'         => 'This is not a valid date.',
            'phone'        => 'This is not a valid phone number. Please input only numbers.',
            'url'          => 'This is not a valid URL.',
            'zip'          => 'This is not a properly formatted zip code.',
            'postal_code'  => 'This is not a properly formatted postal code.',
            'zippostal'    => 'This is not a properly formatted zip code or postal code.',
            'alpha'        => 'Must consist of letters only.',
            'numeric'      => 'Must be numeric.',
            'alphanumeric' => 'Must be alpha-numeric.',
            'minlength'    => 'Must be longer than %option% characters.',
            'maxlength'    => 'Cannot be greater than %option% characters.',
            'nosymbols'    => 'Can only contain letters, numbers, and spaces.',
            'basicsymbols' => 'Can only contain letter, numbers, and basic symbols.',
            'badwords'     => 'Inappropriate language is not permitted.',
            'date'         => 'Date must be a valid YYYY-MM-DD format',
            'datetime'     => 'Date and time must be in a valid YYYY-MM-DD HH:MM:SS format.'
        );

        return $error_ajax_array[$this->current_rule];

    }

    /**
     * Returns an array of curse words.

     */
    function curse_array()
    {
        return array(
            'ahole', 'anus', 'ash0le', 'ash0les', 'asholes', 'ass', 'Ass Monkey',
            'Assface', 'assh0le', 'assh0lez', 'asshole', 'assholes', 'assholz',
            'asswipe', 'azzhole', 'bassterds', 'bastard', 'bastards', 'bastardz',
            'basterds', 'basterdz', 'Biatch', 'bitch', 'bitches', 'Blow Job', 'boffing',
            'butthole', 'buttwipe', 'c0ck', 'c0cks', 'c0k', 'Carpet Muncher', 'cawk',
            'cawks', 'Clit', 'cnts', 'cntz', 'cock', 'cockhead', 'cock-head', 'cocks',
            'CockSucker', 'cock-sucker', 'crap', 'cum', 'cunt', 'cunts', 'cuntz', 'dick',
            'dild0', 'dild0s', 'dildo', 'dildos', 'dilld0', 'dilld0s', 'dominatricks',
            'dominatrics', 'dominatrix', 'dyke', 'enema', 'f u c k', 'f u c k e r', 'fag',
            'fag1t', 'faget', 'fagg1t', 'faggit', 'faggot', 'fagit', 'fags', 'fagz', 'faig',
            'faigs', 'fart', 'flipping the bird', 'fuck', 'fucker', 'fuckin', 'fucking',
            'fucks', 'Fudge Packer', 'fuk', 'Fukah', 'Fuken', 'fuker', 'Fukin', 'Fukk',
            'Fukkah', 'Fukken', 'Fukker', 'Fukkin', 'g00k', 'gay', 'gayboy', 'gaygirl',
            'gays', 'gayz', 'God-damned', 'h00r', 'h0ar', 'h0re', 'hells', 'hoar', 'hoor',
            'hoore', 'jackoff', 'jap', 'japs', 'jerk-off', 'jisim', 'jiss', 'jizm', 'jizz',
            'knob', 'knobs', 'knobz', 'kunt', 'kunts', 'kuntz', 'Lesbian', 'Lezzian', 'Lipshits',
            'Lipshitz', 'masochist', 'masokist', 'massterbait', 'masstrbait', 'masstrbate',
            'masterbaiter', 'masterbate', 'masterbates', 'Motha Fucker', 'Motha Fuker',
            'Motha Fukkah', 'Motha Fukker', 'Mother Fucker', 'Mother Fukah', 'Mother Fuker',
            'Mother Fukkah', 'Mother Fukker', 'mother-fucker', 'Mutha Fucker', 'Mutha Fukah',
            'Mutha Fuker', 'Mutha Fukkah', 'Mutha Fukker', 'n1gr', 'nastt', 'nigger;', 'nigur;',
            'niiger;', 'niigr;', 'orafis', 'orgasim;', 'orgasm', 'orgasum', 'oriface', 'orifice',
            'orifiss', 'packi', 'packie', 'packy', 'paki', 'pakie', 'paky', 'pecker', 'peeenus',
            'peeenusss', 'peenus', 'peinus', 'pen1s', 'penas', 'penis', 'penis-breath', 'penus',
            'penuus', 'Phuc', 'Phuck', 'Phuk', 'Phuker', 'Phukker', 'polac', 'polack', 'polak',
            'Poonani', 'pr1c', 'pr1ck', 'pr1k', 'pusse', 'pussee', 'pussy', 'puuke', 'puuker',
            'queer', 'queers', 'queerz', 'qweers', 'qweerz', 'qweir', 'recktum', 'rectum',
            'retard', 'sadist', 'scank', 'schlong', 'screwing', 'semen', 'sex', 'sexy',
            'Sh!t', 'sh1t', 'sh1ter', 'sh1ts', 'sh1tter', 'sh1tz', 'shit', 'shits', 'shitter',
            'Shitty', 'Shity', 'shitz', 'Shyt', 'Shyte', 'Shytty', 'Shyty', 'skanck', 'skank',
            'skankee', 'skankey', 'skanks', 'Skanky', 'slut', 'sluts', 'Slutty', 'slutz',
            'son-of-a-bitch', 'tit', 'turd', 'va1jina', 'vag1na', 'vagiina', 'vagina', 'vaj1na',
            'vajina', 'vullva', 'vulva', 'w0p', 'wh00r', 'wh0re', 'whore', 'xrated', 'xxx', 'b!\+ch',
            'bitch', 'blowjob', 'clit', 'arschloch', 'fuck', 'shit', 'ass', 'asshole', 'b!tch',
            'b17ch', 'b1tch', 'bastard', 'bi\+ch', 'boiolas', 'buceta', 'c0ck', 'cawk', 'chink',
            'cipa', 'clits', 'cock', 'cum', 'cunt', 'dildo', 'dirsa', 'ejakulate', 'fatass', 'fcuk',
            'fuk', 'fux0r', 'hoer', 'hore', 'jism', 'kawk', 'l3itch', 'l3i\+ch', 'lesbian', 'masturbate',
            'masterbat\*', 'masterbat3', 'motherfucker', 's.o.b.', 'mofo', 'nazi', 'nigga', 'nigger',
            'nutsack', 'phuck', 'pimpis', 'pusse', 'pussy', 'scrotum', 'sh!t', 'shemale', 'shi\+', 'sh!\+',
            'slut', 'smut', 'teets', 'tits', 'boobs', 'b00bs', 'teez', 'testical', 'testicle', 'titt',
            'w00se', 'jackoff', 'wank', 'whoar', 'whore', '\*damn', '\*dyke', '\*fuck\*', '\*shit\*', '@$$',
            'amcik', 'andskota', 'arse\*', 'assrammer', 'ayir', 'bi7ch', 'bitch\*', 'bollock\*', 'breasts',
            'butt-pirate', 'cabron', 'cazzo', 'chraa', 'chuj', 'Cock\*', 'cunt\*', 'd4mn', 'daygo',
            'dego', 'dick\*', 'dike\*', 'dupa', 'dziwka', 'ejackulate', 'Ekrem\*', 'Ekto', 'enculer',
            'faen', 'fag\*', 'fanculo', 'fanny', 'feces', 'feg', 'Felcher', 'ficken', 'fitt\*', 'Flikker',
            'foreskin', 'Fotze', 'Fu\(\*', 'fuk\*', 'futkretzn', 'gay', 'gook', 'guiena', 'h0r', 'h4x0r',
            'hell', 'helvete', 'hoer\*', 'honkey', 'Huevon', 'hui', 'injun', 'jizz', 'kanker\*', 'kike',
            'klootzak', 'kraut', 'knulle', 'kuk', 'kuksuger', 'Kurac', 'kurwa', 'kusi\*', 'kyrpa\*', 'lesbo',
            'mamhoon', 'masturbat\*', 'merd\*', 'mibun', 'monkleigh', 'mouliewop', 'muie', 'mulkku', 'muschi',
            'nazis', 'nepesaurio', 'nigger\*', 'orospu', 'paska\*', 'perse', 'picka', 'pierdol\*', 'pillu\*',
            'pimmel', 'piss\*', 'pizda', 'poontsee', 'poop', 'porn', 'p0rn', 'pr0n', 'preteen', 'pula',
            'pule', 'puta', 'puto', 'qahbeh', 'queef\*', 'rautenberg', 'schaffer', 'scheiss\*', 'schlampe',
            'schmuck', 'screw', 'sh!t\*', 'sharmuta', 'sharmute', 'shipal', 'shiz', 'skribz', 'skurwysyn',
            'sphencter', 'spic', 'spierdalaj', 'splooge', 'suka', 'b00b\*', 'testicle\*', 'titt\*', 'twat',
            'vittu', 'wank\*', 'wetback\*', 'wichser', 'wop\*', 'yed', 'zabourah'
        );

    }

    /**
     * Takes a field name and makes it
     * more readable.

     */
    function pretty_names($name)
    {
        return ucwords(str_replace('_', ' ', $name));

    }

    function clean_errors()
    {
        if (!empty($this->plain_english)) {
            $this->plain_english = '<ul>';
            $this->plain_english .= $this->plain_english;
            $this->plain_english .= '</ul>';

        }

    }

}

