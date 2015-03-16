<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Variable;

use Application\Entity\Client as ClientEntity;

/**
 * Variable interface
 *
 * @category    Application
 * @package     Variable
 */
interface VariableInterface
{
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
     * Get variable value
     *
     * @return string
     */
    public function getValue();
}
