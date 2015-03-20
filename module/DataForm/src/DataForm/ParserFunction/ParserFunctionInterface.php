<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DataForm\ParserFunction;

use Application\Entity\Template as TemplateEntity;
use Application\Entity\Client as ClientEntity;

/**
 * ParserFunction interface
 *
 * @category    DataForm
 * @package     ParserFunction
 */
interface ParserFunctionInterface
{
    /**
     * Set current template
     *
     * @param TemplateEntity $template
     * @return self
     */
    public function setTemplate(TemplateEntity $template);

    /**
     * Get current template
     *
     * @return TemplateEntity
     */
    public function getTemplate();

    /**
     * Set current client
     *
     * @param ClientEntity $client
     * @return self
     */
    public function setClient(ClientEntity $client);

    /**
     * Get current client
     *
     * @return ClientEntity
     */
    public function getClient();

    /**
     * Execute the function
     */
    public function execute();
}
