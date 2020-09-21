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
class captcha extends db
{

    /**
     * Generate a CAPTCHA

     */
    function generate_captcha()
    {
        $type = $this->get_option('captcha_type');
        if ($type == "random") {
            $theCaptcha   = substr(md5(md5(time() . rand(100000, 999999))), 0, 9);
            $send_captcha = $theCaptcha;

        } else {
            $word1        = $this->possible_word('short');
            $word2        = $this->possible_word('long');
            $theCaptcha   = $word1 . $word2;
            $send_captcha = $word1 . "|" . $word2;
        }
        return $send_captcha;
        // $this->generate_captcha_image($send_captcha,$type);
    }

    function get_captcha($id)
    {
        $q1 = $this->get_array("
            SELECT *
            FROM `ppSD_captcha`
            WHERE `id`='" . $this->mysql_clean($id) . "'
            LIMIT 1
        ");
        return $q1;
    }

    /**
     * Generate a CAPTCHA block
     * Only used for inline-ajax requests.
     * Other form-based blocks are rendered
     * VIA a template.

     */
    function captcha_block($image_url)
    {
        return "<div class=\"zen_field_entry zen_medium zen_gray_box\" id=\"captcha_block\">

    			<div class=\"zen_pad_less\">

	            		<div class=\"zen_col50l\">

	      	      			<img width=\"200\" height=\"50\" id=\"captchaput\" class=\"imageout\" src=\"$image_url\" />

	      	      			<div class=\"zen_topmargin_less\">

	      	      				<input type=\"text\" name=\"captcha\" value=\"\" class=\"home\" style=\"width:200px;\" />

					</div>

				</div>

				<div class=\"zen_col50\">

					<div class=\"zen_pad_less zen_base_form\">

						<h2 class=\"zen_notopmargin\">Are you human?</h2>

						Please confirm that your are human by typing the words to the left.

					</div>

				</div>

				<div class=\"zen_clear\"></div>

			</div>

			</div>";

    }

    /**
     * Generate a CAPTCHA image

     */
    function generate_captcha_image($text, $type = "random", $width = "200", $height = "50")
    {
        /*

            echo "<li>$text // $type";

            $testGD = get_extension_funcs("gd"); // Grab function list

            if (!$testGD){ echo "GD not even installed."; exit; }

            echo"<pre>".print_r($testGD,true)."</pre>";

        */
        header('Content-Type: image/png');
        $im = imagecreatetruecolor($width, $height);
        // Create some colors
        $white = imagecolorallocate($im, 255, 255, 255);
        $grey  = imagecolorallocate($im, 225, 225, 225);
        $black = imagecolorallocate($im, 0, 0, 0);
        $blue  = imagecolorallocate($im, 111, 205, 243);
        imagefilledrectangle($im, 0, 0, $width, $height, $white);
        // Get the font file.
        $font = PP_PATH . "/custom/fonts/handsean.ttf";
        if (strpos($text, '|') === false) {
            $set_one   = substr($text, 0, 4);
            $set_two   = substr($text, 4);
            $push_left = "75";

        } else {
            $exp_words = explode('|', $text);
            $set_one   = $exp_words['0'];
            $word_len  = strlen($set_one);
            $push_left = ($word_len * 12) + 10;
            $set_two   = $exp_words['1'];

        }
        // Background distraction font
        $font_sizeA = "50";
        $fake_text  = substr(md5(time()), 0, 12);
        imagettftext($im, $font_sizeA, -6, 0, 40, $grey, $font, $fake_text);
        // Left side black font
        $font_sizeB = "20";
        imagettftext($im, $font_sizeB, -3, 2, 30, $black, $font, $set_one);
        // Right side blue font
        $font_sizeC = "25";
        imagettftext($im, $font_sizeC, 4, $push_left, 40, $blue, $font, $set_two);
        imagepng($im);
        imagedestroy($im);

    }

    /**
     * Array of possible word options

     */
    function possible_word($type = "short")
    {
        $short_words = array(
            'red', 'orange', 'yellow', 'green', 'blue', 'purple', 'gray', 'short', 'cold', 'hot',
            'angry', 'itchy', 'lazy', 'scary', 'brave', 'calm', 'eager', 'happy', 'jolly', 'kind',
            'nice', 'proud', 'silly', 'witty', 'loud', 'noisy', 'raspy', 'tall', 'tiny', 'small',
            'big', 'large', 'short', 'fast', 'hot', 'good', 'old', 'pretty', 'fat', 'happy', 'full',
            'dark', 'funny', 'interesting', 'cheap', 'high', 'deep', 'healthy', 'rich', 'soft', 'easy',
            'slow', 'small', 'soft', 'special', 'strange', 'strong', 'sure', 'surprised', 'tall', 'tiny',
            'tired', 'together', 'true', 'ugly', 'warm', 'weak', 'wet', 'wrong', 'young', 'finished',
            'first', 'flat', 'free', 'full', 'good', 'great', 'happy', 'hard', 'heavy', 'high', 'hot',
            'huge', 'interesting', 'large', 'dangerous', 'dark', 'dead', 'different', 'done', 'dry',
            'early', 'easy', 'empty', 'excited', 'exciting', 'cheap', 'clear', 'close', 'closed'
        );
        $long_words  = array(
            'alarm', 'animal', 'aunt', 'bait', 'balloon', 'bath', 'bead', 'beam', 'bean',
            'bedroom', 'boot', 'bread', 'brick', 'brother', 'camp', 'chicken', 'children',
            'crook', 'deer', 'dock', 'doctor', 'downtown', 'drum', 'dust', 'eye', 'family',
            'father', 'fight', 'flesh', 'food', 'frog', 'goose', 'grade',
            'grape', 'grass', 'hook', 'horse', 'jail', 'jam', 'kiss', 'kitten', 'light', 'loaf', 'lock',
            'lunch', 'lunchroom', 'meal', 'mother', 'notebook', 'owl', 'pail', 'parent', 'park', 'plot',
            'rabbit', 'rake', 'robin', 'actor', 'airplane', 'airport', 'army', 'baseball', 'beef',
            'birthday', 'boy', 'brush', 'bushes', 'butter', 'cast', 'cave', 'cent',
            'cherries', 'cherry', 'cobweb', 'coil', 'cracker', 'dinner', 'eggnog', 'elbow',
            'face', 'fireman', 'flavor', 'gate', 'glove', 'glue', 'goldfish', 'goose',
            'grain', 'hair', 'haircut', 'hobbies', 'holiday', 'hot', 'jellyfish', 'ladybug',
            'mailbox', 'number', 'oatmeal', 'pail', 'pancake', 'pear', 'sack', 'sail', 'scale',
            'apple', 'beauty', 'board', 'capital', 'captain', 'brother', 'bell', 'base', 'atom'
        );
        if ($type == "short") {
            $ary_len   = sizeof($short_words) - 1;
            $random    = rand(0, $ary_len);
            $pick_word = $short_words[$random];

        } else {
            $ary_len   = sizeof($long_words) - 1;
            $random    = rand(0, $ary_len);
            $pick_word = $long_words[$random];

        }

        return $pick_word;

    }

}



