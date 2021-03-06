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
class Text
{
    /**
     * Convert size in bytes (string) to integer
     *
     * @param   string $str
     * @return  integer
     */
    public static function strToSize($str)
    {
        if (preg_match('/([.0-9]+)\s*GB/i', $str, $matches))
            return $matches[1] * 1024 * 1024 * 1024;
        else if (preg_match('/([.0-9]+)\s*MB/i', $str, $matches))
            return $matches[1] * 1024 * 1024;
        else if (preg_match('/([.0-9]+)\s*KB/i', $str, $matches))
            return $matches[1] * 1024;

        return (int)$str;
    }

    /**
     * Convert integer (size) to string
     *
     * @param   integer $size
     * @return  string
     */
    public static function sizeToStr($size)
    {
        if ($size >= 1024 * 1024 * 1024 * 1024)
            return sprintf("%.02f TB", $size / 1024 / 1024 / 1024 / 1024);
        else if ($size >= 1024 * 1024 * 1024)
            return sprintf("%.02f GB", $size / 1024 / 1024 / 1024);
        else if ($size >= 1024 * 1024)
            return sprintf("%.02f MB", $size / 1024 / 1024);
        else if ($size >= 1024)
            return sprintf("%.02f KB", $size / 1024);

        return $size;
    }

    /**
     * Convert variable name to camel case
     *
     * @param string $name
     * @return string
     */
    public static function toCamelCase($name)
    {
        $name = str_replace("_", " ", $name);
        $name = ucwords($name);
        $name = str_replace(" ", "", $name);
        return $name;
    }
}
