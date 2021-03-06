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
 * HTTP 403 Forbidden
 *
 * @category    Application
 * @package     Exception
 */
class AccessDeniedException extends HttpException
{
    /**
     * Constructor
     *
     * @param string    $message
     * @param integer   $code
     * @param Exception $prev
     */
    public function __construct($message = 'Access to requested resource is denied', $code = 403, $prev = null)
    {
        parent::__construct($message, $code, $prev);
    }
}
