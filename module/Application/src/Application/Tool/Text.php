<?php
/**
 * ZF2 Skeleton
 *
 * @link        http://mycode.daemon-notes.com/projects/zf2-skeleton
 * @copyright   Copyright (c) 2012-2014 Ross at daemon-notes.com
 * @license     http://daemon-notes.com/license/software FreeBSD License
 * @version     $Id: Text.php 118 2014-04-03 07:07:29Z ross $
 */

namespace Application\Tool;

/**
 * Text helper class
 * 
 * @category    Skeleton
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
}
