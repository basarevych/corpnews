<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Exception;

use Exception;

/**
 * HTTP 405 Not Implemented
 *
 * @category    Application
 * @package     Exception
 */
class NotImplementedException extends HttpException
{
    /**
     * Constructor
     *
     * @param string    $message
     * @param integer   $code
     * @param Exception $prev
     */
    public function __construct($message = 'Method not implemented', $code = 405, $prev = null)
    {
        parent::__construct($message, $code, $prev);
    }
}
