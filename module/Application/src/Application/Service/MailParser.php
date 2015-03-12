<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Service;

use Exception;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * MailParser service
 * 
 * @category    Application
 * @package     Service
 */
class MailParser implements ServiceLocatorAwareInterface
{
    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Variables
     *
     * @var mixed
     */
    protected $variables = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return MailParser
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        if (!$this->variables) {
            $options = $serviceLocator->get('Config');
            if (!isset($options['corpnews']['parser']['variables']))
                throw new \Exception("No 'parser/variables' section in the config");

            $this->variables = $options['corpnews']['parser']['variables'];
        }

        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        if (!$this->serviceLocator)
            throw new \Exception('No Service Locator configured');
        return $this->serviceLocator;
    }


    /**
     * Get parser variables
     *
     * @return array
     */
    public function getVariables()
    {
        return array_keys($this->variables);
    }

    /**
     * Get variable description
     *
     * @param string $variable
     * @return string
     */
    public function getVariableDescr($variable)
    {
        if (!isset($this->variables[$variable]) || !isset($this->variables[$variable]['descr']))
            return null;

        return $this->variables[$variable]['descr'];
    }

    /**
     * Check syntax
     *
     * @param string $msg
     * @param string &$output
     * @return boolean
     */
    public function checkSyntax($msg, &$output = null)
    {
        $scripts = [];
        $bracketCounter = 0;
        $buffer = "";
        for ($i = 0; $i < strlen($msg); $i++) {
            if ($msg[$i] == '{')
                $bracketCounter++;

            if ($bracketCounter > 0) {
                $buffer .= $msg[$i];
            } else if (strlen($buffer) > 0) {
                $scripts[] = $buffer;
                $buffer = "";
            }

            if ($msg[$i] == '}')
                $bracketCounter--;
        }
        if (strlen($buffer) > 0)
            $scripts[] = $buffer;

        $success = true;
        $output = $msg;
        $prevPos = 0;
        foreach ($scripts as $script) {
            $error = false;

            $length = strlen($script);
            if ($script[0] != '{' || $script[1] != '{')
                continue;
            if ($script[$length-1] != '}' || $script[$length-2] != '}')
                $error = true;

            if (!$error) {
                $code = substr($script, 2, $length-4) . ';';
                if (!$this->testCode($code)) {
                    $success = false;
                    $error = true;
                }
            }

            $pos = strpos($output, $script, $prevPos);
            if ($pos !== false) {
                $error = '<span style="background: '
                    . ($error ? '#a90000' : '#00a900')
                    . '; color: #ffffff;">'
                    . $script . '</span>';
                $output = substr_replace($output, $error, $pos, $length);
                $prevPos = $pos + strlen($error);
            }
        }

        return $success;
    }

    protected function testCode($code)
    {
        $oldReporting = error_reporting(0);
        $success = @eval('return true;' . $code . ';');
        error_reporting($oldReporting);
        return $success === true;
    }
}
