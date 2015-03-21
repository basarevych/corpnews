<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Service;

use ReflectionClass;
use Exception;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Entity\Template as TemplateEntity;
use Application\Entity\Client as ClientEntity;
use Application\Entity\Secret as SecretEntity;

/**
 * Parser service
 * 
 * @category    Application
 * @package     Service
 */
class Parser implements ServiceLocatorAwareInterface
{
    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Functions
     *
     * @var mixed
     */
    protected $functions = null;

    /**
     * Current template
     *
     * @var TemplateEntity
     */
    protected $template = null;

    /**
     * Current client
     *
     * @var ClientEntity
     */
    protected $client = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Parser
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        if (!$this->functions) {
            $options = $serviceLocator->get('Config');
            if (!isset($options['corpnews']['parser']['functions']))
                throw new \Exception("No 'parser/functions' section in the config");

            $this->functions = $options['corpnews']['parser']['functions'];

            foreach ($this->functions as $name => $props) {
                $class = $this->getFunctionClass($name);
                $reflection = new ReflectionClass($class);

                if (!$reflection->implementsInterface('DataForm\ParserFunction\ParserFunctionInterface'))
                    throw new Exception('All the functions must implement ParserFunctionInterface');

                if (!$reflection->implementsInterface('Zend\ServiceManager\ServiceLocatorAwareInterface'))
                    throw new Exception('All the functions must implement ServiceLocatorAwareInterface');
            }
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
     * Set current template
     *
     * @param TemplateEntity $template
     * @return Parser
     */
    public function setTemplate(TemplateEntity $template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Get current template
     *
     * @return TemplateEntity
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set current client
     *
     * @param ClientEntity $client
     * @return Parser
     */
    public function setClient(ClientEntity $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Get current client
     *
     * @return ClientEntity
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get parser function names
     *
     * @return array
     */
    public function getFunctions()
    {
        return array_keys($this->functions);
    }

    /**
     * Get function description
     *
     * @param string $name
     * @return string
     */
    public function getFunctionDescr($name)
    {
        if (!isset($this->functions[$name]) || !isset($this->functions[$name]['descr']))
            return null;

        return $this->functions[$name]['descr'];
    }

    /**
     * Get function class
     *
     * @param string $name
     * @return string
     */
    public function getFunctionClass($name)
    {
        if (!isset($this->functions[$name]) || !isset($this->functions[$name]['class']))
            return null;

        return $this->functions[$name]['class'];
    }

    /**
     * Is function output is HTML
     *
     * @param string $name
     * @return boolean
     */
    public function isHtmlFunction($name)
    {
        if (!isset($this->functions[$name]) || !isset($this->functions[$name]['html']))
            return null;

        return $this->functions[$name]['html'];
    }

    /**
     * Check syntax (parsed $output is HTML)
     *
     * @param string $msg
     * @param string &$output
     * @param boolean $htmlInput
     * @return boolean
     */
    public function checkSyntax($msg, &$output, $htmlInput)
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
        $output = "";
        if (count($scripts) > 0) {
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
                    if ($htmlInput)
                        $code = html_entity_decode($code);

                    if (!$this->testCode($code))
                        $error = true;
                }

                if ($error)
                    $success = false;

                $pos = strpos($msg, $script, $prevPos);
                if ($pos === false) {
                    $success = false;
                } else {
                    $originalChunk = substr($msg, $prevPos, $pos - $prevPos);
                    if ($htmlInput)
                        $output .= $originalChunk;
                    else
                        $output .= htmlentities($originalChunk, ENT_COMPAT | ENT_HTML401, 'UTF-8');

                    $highlight = '<span style="background: '
                        . ($error ? '#a90000' : '#00a900')
                        . '; color: #ffffff;">'
                        . htmlentities($script, ENT_COMPAT | ENT_HTML401, 'UTF-8')
                        . '</span>';
                    $output .= $highlight;
                    $prevPos = $pos + $length;
                }
            }

            $originalChunk = substr($msg, $prevPos, strlen($msg) - $prevPos);
            if ($htmlInput)
                $output .= $originalChunk;
            else
                $output .= htmlentities($originalChunk, ENT_COMPAT | ENT_HTML401, 'UTF-8');
        } else {
            $output = $htmlInput ? $msg : htmlentities($msg, ENT_COMPAT | ENT_HTML401, 'UTF-8');
        }

        return $success;
    }

    /**
     * Parse and substitute the code
     *
     * @param string $msg
     * @param string &$output
     * @param boolean $htmlInput
     * @return boolean
     */
    public function parse($msg, &$output, $htmlInput, $htmlOutput)
    {
        $__sl = $this->getServiceLocator();
        $__html = $htmlOutput;
        $callback = function ($code) use ($__sl, $__html) {
            foreach ($this->getFunctions() as $__func) {
                $$__func = function ($param1 = '', $param2 = '', $param3 = '') use ($__func, $__sl, $__html) {
                    $__class = $this->getFunctionClass($__func);
                    $__obj = new $__class();
                    $__obj->setServiceLocator($__sl);
                    $__obj->setTemplate($this->getTemplate());
                    $__obj->setClient($this->getClient());

                    ob_start();
                    $__obj->execute($param1, $param2, $param3);
                    $__output = ob_get_contents();
                    ob_end_clean();

                    if ($__html && !$this->isHtmlFunction($__func))
                        $__output = htmlentities($__output, ENT_COMPAT | ENT_HTML401, 'UTF-8');

                    echo $__output;
                };
            }

            @eval($code);
        };

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
        $output = "";
        if (count($scripts) > 0) {
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
                    if ($htmlInput)
                        $code = html_entity_decode($code);

                    if (!$this->testCode($code))
                        $error = true;
                }

                if ($error) {
                    $success = false;
                    continue;
                }

                $pos = strpos($msg, $script, $prevPos);
                if ($pos === false) {
                    $success = false;
                } else {
                    $originalChunk = substr($msg, $prevPos, $pos - $prevPos);
                    if ($htmlInput == $htmlOutput)
                        $output .= $originalChunk;
                    else if ($htmlInput && !$htmlOutput)
                        $output .= html_entity_decode($originalChunk);
                    else
                        $output .= htmlentities($originalChunk, ENT_COMPAT | ENT_HTML401, 'UTF-8');

                    ob_start();

                    try {
                        $callback($code);
                    } catch (\Exception $e) {
                        $success = false;
                    }

                    $result = ob_get_contents();
                    ob_end_clean();

                    $output .= $result;

                    $prevPos = $pos + $length;
                }
            }

            $originalChunk = substr($msg, $prevPos, strlen($msg) - $prevPos);
            if ($htmlInput == $htmlOutput)
                $output .= $originalChunk;
            else if ($htmlInput && !$htmlOutput)
                $output .= html_entity_decode($originalChunk);
            else
                $output .= htmlentities($originalChunk, ENT_COMPAT | ENT_HTML401, 'UTF-8');
        } else {
            if ($htmlInput == $htmlOutput)
                $output .= $msg;
            else if ($htmlInput && !$htmlOutput)
                $output .= html_entity_decode($msg);
            else
                $output .= htmlentities($msg, ENT_COMPAT | ENT_HTML401, 'UTF-8');
        }

        if (strpos($output, '{{') !== false)
            $success = false;

        return $success;
    }

    /**
     * Test PHP syntax is OK
     *
     * @param string $code
     * @return boolean
     */
    protected function testCode($code)
    {
        $filename = '/tmp/corpnews.' . SecretEntity::generateSecretKey();
        $file = fopen($filename, "w");
        if (!$file)
            return false;

        fwrite($file, "<?php\n");
        foreach ($this->getFunctions() as $func)
            fwrite($file, "$" . $func . " = function () { return null; };\n");
        fwrite($file, "\n" . $code . ";\n");
        fclose($file);

        exec('php ' . $filename, $output, $return);
        unlink($filename);

        return $return == 0;
    }
}
