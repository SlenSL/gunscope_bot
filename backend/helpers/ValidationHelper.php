<?php

namespace  backend\helpers;
use backend\models\upmarket\UpmarketEmail as Email;

class ValidationHelper
{
    public static function validateEmail($string)
    {
        if (preg_match('/^[\w+._-]+@[\w.-]+\.[\w]{2,}$/', $string)) {
            return trim($string);
        } 

        return false;
    }

    public static function validateNumbers($string)
    {
        $strWithoutChars = (int) preg_replace('/[^0-9]/', '', $string);

        $numbersArray = array_map('intval', str_split($strWithoutChars));
        $numbersString = implode(',', $numbersArray);
        return !empty($numbersString) ? $numbersString : false;
    }
}