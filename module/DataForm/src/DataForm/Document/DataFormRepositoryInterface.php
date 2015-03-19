<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DataForm\Document;

/**
 * Document repository interface
 * 
 * @category    DataForm
 * @package     Document
 */
interface DataFormRepositoryInterface
{
    /**
     * Remove all documents
     */
    public function removeAll();
 }
