<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Tool;

use DateTime;

/**
 * Date helper class
 * 
 * @category    Application
 * @package     Tool
 */
class Date
{
    /**
     * Convert DateTime to imap_search string
     *
     * @param DateTime $date
     * @return string
     */
    public static function toImapString($date)
    {
        return $date->format('d-M-Y H:i:s O');
    }
} 
