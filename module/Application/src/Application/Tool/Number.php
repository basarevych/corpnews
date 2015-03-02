<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Tool;

/**
 * Text helper class
 * 
 * @category    Application
 * @package     Tool
 */
class Number
{
    /**
     * Convert number to locale string
     *
     * @param   integer $number
     * @return  string
     */
    public static function localeFormat($number)
    {
        if ($number === null)
            return '';

        $fmt = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::DECIMAL);
        $fmt->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 6);
        return $fmt->format($number);
    }
} 
