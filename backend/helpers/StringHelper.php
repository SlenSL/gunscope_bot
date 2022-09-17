<?php

namespace common\helpers;

use Yii;
class StringHelper
{
    /** 
     * Удаляет $count слов из конца строки
     * 
     * @param int $string - входная строка
     * @param int $count - количество обрезаемых слов
     * @return string 
     */
    public static function deleteLastWords($string, $count){
        $wordsArray = explode(' ', $string);

        $result = array_slice($wordsArray, 0, -$count);
        
        return implode(' ', $result);
    }

    public static function DateToString($timestamp)
    {
        if (empty($timestamp)) return '(не задано)';
        $currentDate = date('d m Y года в H:i', $timestamp);

        $_monthsList = array(
            " 01 " => "января",
            " 02 " => "февраля",
            " 03 " => "марта",
            " 04 " => "апреля",
            " 05 " => "мая",
            " 06 " => "июня",
            " 07 " => "июля",
            " 08 " => "августа",
            " 09 " => "сентября",
            " 10 " => "октября",
            " 11 " => "ноября",
            " 12 " => "декабря"
        );


        $_mD = date(" m ", $timestamp);
        $currentDate = str_replace($_mD, " " . $_monthsList[$_mD] . " ", $currentDate);
        return $currentDate;
    }

    public static function DateToStringBack($timestamp)
    {
        if (empty($timestamp)) return '(не задано)';
        $currentDate = date('d/m/Y H:i:s', $timestamp);
        return $currentDate;
    }

    public static function DateToStringBackWithNoSeconds($timestamp)
    {
        if (empty($timestamp)) return '(не задано)';
        $currentDate = date('d/m/Y H:i', $timestamp);
        return $currentDate;
    }

    public static function unmaskPhone($phone = null)
    {
        $phone = preg_replace('/[^0-9]/', '', (string)$phone);
        return (string)$phone;
    }

    public static function maskPhone($phone = null)
    {
        $phone = (string)$phone;
        $maskedPhone = sprintf(
            "+%s (%s) %s-%s-%s",
            substr($phone, 0, 1),
            substr($phone, 1, 3),
            substr($phone, 4, 3),
            substr($phone, 7, 2),
            substr($phone, 9)
        );

        return $maskedPhone;
    }

    /** 
     * Чистит строку от ссылок и <a> тегов
     * 
     * @param string $string
     * @return string - строка без ссылок
     */
    public static function deleteLinksFromString($string, $echoWord = false, $file = null, $url = null)
    {
        $wordsArrayOld = preg_split('/[\s,"]+/', $string);

        $patternTag = '/<a([\s\S]+)?>([\s\S]+)?<\/a>/i';
        $resultString = preg_replace($patternTag, "", $string);

        $wordsArray = preg_split('/[\s,"]+/', $resultString);

        $newWordsArray = [];
        foreach ($wordsArray as $word) {
            if ((iconv_strlen(trim($word)) < 6) || !self::containLink($word, $echoWord)) {
                $newWordsArray[] = $word;
            }
        }

        if (!empty($newWordsArray)) {
            $newString =  implode(" ", $newWordsArray);
        }

        if (!empty($newString) && (!empty(array_diff($wordsArrayOld, $newWordsArray)))) {
            return $newString;
        }

        // return false;
        return $string;
    }

    public static function transliterateString($string) 
    {
        $gost = array(
            'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
            'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
            'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
            'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
            'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
            'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
            'э' => 'e',    'ю' => 'yu',   'я' => 'ya',
    
            'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
            'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
            'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
            'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
            'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
            'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
            'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya',
        );
        $string = mb_strtolower($string);
        $final = strtr($string, $gost);
        // preg_replace( "/[^a-zA-ZА-Яа-я0-9\s]/", '', $final);
        $result = str_replace(' - ', '', $final);
        $result = preg_replace('![\s]+!', "-" , $result);
        $result = mb_strtolower($result);
        $result =  preg_replace("/[^a-zа-я0-9-]/i", "", iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $result)); 

        return $result;
    }

    /** 
     * Принимает строку, возвращает ссылку, хранящуюся в строке
     * 
     * @param string $string
     * @return bool
     */
    private static function containLink($string, $echoWord = false)
    {
        $patternEmail = '/[a-zA-z0-9]+@[a-zA-z0-9]+\.[a-zA-z0-9]/';
        $pattern = '/(http:\/\/|https:\/\/)|(www.)([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.-\?\%\&]*)*\/?/i';
        if (!str_contains($string, 'ytimg') && !str_contains($string, 'youtube') && !preg_match($patternEmail, $string) && preg_match($pattern, $string)) {
            if ($echoWord) {
                echo "<p>Найденное слово с ссылкой: {$string}</p>";
            }
            return true;
        }
        return false;
    }

}
