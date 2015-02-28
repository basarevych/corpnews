<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Validator;

use Exception;
use Locale;
use NumberFormatter;
use Zend\Validator\AbstractValidator;

/**
 * Locale-aware float number validator
 *
 * @category    Application
 * @package     Validator
 */
class Float extends AbstractValidator
{
    /**
     * @const NOT_FLOAT
     */
    const NOT_FLOAT = 'notFloat';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_FLOAT => "Value is not a float number"
    );

    /**
     * Returns true if $value is valid
     *
     * @param  mixed $value
     * @return boolean
     * @throws Exception    When not configured
     */
    public function isValid($value)
    {
        $this->setValue($value);

        $fmt = new NumberFormatter(Locale::getDefault(), NumberFormatter::DECIMAL);
        $parse = $fmt->parse($value);
        if ($parse === false) {
            $this->error(self::NOT_FLOAT);
            return false;
        }

        return true;
    }
}
