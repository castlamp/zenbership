<?php



//  Copyright Oliver Lillie 2011
//  $Id: Whitechars.php 1938 2011-06-09 13:25:22Z buggedcom $ $Rev: 1938 $
class Gibberish
{

    protected static $_accepted_characters = 'abcdefghijklmnopqrstuvwxyz -_+';

    public static function test($text, $trained_library, $raw = false)
    {
        $value = self::_averageTransitionProbability($text, $trained_library);
        if ($raw === true) {
            return $value;

        }
        if ($value <= $trained_library['threshold']) {
            return true;

        }

        return false;

    }

    protected static function _normalise($line)
    {
//          Return only the subset of chars from accepted_chars.
//          This helps keep the  model relatively small by ignoring punctuation,
//          infrequenty symbols, etc.
        return preg_replace('/[^a-z\ ]/', '', strtolower($line));

    }

    public static function train($big_text_file, $good_text_file, $bad_text_file, $lib_path)
    {
        if (is_file($big_text_file) === false || is_file($good_text_file) === false || is_file($bad_text_file) === false) {
            return false;

        }
        $k   = strlen(self::$_accepted_characters);
        $pos = array_flip(str_split(self::$_accepted_characters));
//          Assume we have seen 10 of each character pair.  This acts as a kind of
//          prior or smoothing factor.  This way, if we see a character transition
//          live that we've never observed in the past, we won't assume the entire
//          string has 0 probability.
        $log_prob_matrix = array();
        $range           = range(0, count($pos) - 1);
        foreach ($range as $index1) {
            $array = array();
            foreach ($range as $index2) {
                $array[$index2] = 10;

            }
            $log_prob_matrix[$index1] = $array;

        }
//          Count transitions from big text file, taken
//          from http://norvig.com/spell-correct.html
        $lines = file($big_text_file);
        foreach ($lines as $line) {
//              Return all n grams from l after normalizing
            $filtered_line = str_split(self::_normalise($line));
            $a             = false;
            foreach ($filtered_line as $b) {
                if ($a !== false) {
                    $log_prob_matrix[$pos[$a]][$pos[$b]] += 1;

                }
                $a = $b;

            }

        }
        unset($lines, $filtered_line);
//          Normalize the counts so that they become log probabilities.
//          We use log probabilities rather than straight probabilities to avoid
//          numeric underflow issues with long texts.
//          This contains a justification:
//          http://squarecog.wordpress.com/2009/01/10/dealing-with-underflow-in-joint-probability-calculations/
        foreach ($log_prob_matrix as $i => $row) {
            $s = (float)array_sum($row);
            foreach ($row as $k => $j) {
                $log_prob_matrix[$i][$k] = log($j / $s);

            }

        }
//          Find the probability of generating a few arbitrarily choosen good and
//          bad phrases.
        $good_lines = file($good_text_file);
        $good_probs = array();
        foreach ($good_lines as $line) {
            array_push($good_probs, self::_averageTransitionProbability($line, $log_prob_matrix));

        }
        $bad_lines = file($bad_text_file);
        $bad_probs = array();
        foreach ($bad_lines as $line) {
            array_push($bad_probs, self::_averageTransitionProbability($line, $log_prob_matrix));

        }
//          Assert that we actually are capable of detecting the junk.
        $min_good_probs = min($good_probs);
        $max_bad_probs  = max($bad_probs);
        if ($min_good_probs <= $max_bad_probs) {
            return false;

        }
//          And pick a threshold halfway between the worst good and best bad inputs.
        $threshold = ($min_good_probs + $max_bad_probs) / 2;

//          save matrix
        return file_put_contents($lib_path, serialize(array(
            'matrix'    => $log_prob_matrix,
            'threshold' => $threshold,
        ))) > 0;

    }

    public static function _averageTransitionProbability($line, $log_prob_matrix)
    {
//          Return the average transition prob from line through log_prob_mat.
        $log_prob      = 1.0;
        $transition_ct = 0;
        $pos           = array_flip(str_split(self::$_accepted_characters));
        $filtered_line = str_split(self::_normalise($line));
        $a             = false;
        foreach ($filtered_line as $b) {
            if ($a !== false) {
                $log_prob += $log_prob_matrix[$pos[$a]][$pos[$b]];
                $transition_ct += 1;

            }
            $a = $b;

        }

        # The exponentiation translates from log probs to probs.
        return exp($log_prob / max($transition_ct, 1));

    }

}
