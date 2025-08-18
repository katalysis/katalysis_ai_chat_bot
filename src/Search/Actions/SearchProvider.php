<?php

/**
 *
 * This file was build with the Entity Designer add-on.
 *
 * https://www.concrete5.org/marketplace/addons/entity-designer
 *
 */

/** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */

namespace KatalysisAiChatBot\Search\Actions;

use KatalysisAiChatBot\Entity\Search\SavedActionsSearch;
use KatalysisAiChatBot\ActionList;
use KatalysisAiChatBot\Search\Actions\ColumnSet\DefaultSet;
use KatalysisAiChatBot\Search\Actions\ColumnSet\Available;
use KatalysisAiChatBot\Search\Actions\ColumnSet\ColumnSet;
use KatalysisAiChatBot\Search\Actions\Result\Result;
use Concrete\Core\Search\AbstractSearchProvider;
use Concrete\Core\Search\Field\ManagerFactory;

class SearchProvider extends AbstractSearchProvider
{
    public function getFieldManager()
    {
        return ManagerFactory::get('actions');
    }
    
    public function getSessionNamespace()
    {
        return 'actions';
    }
    
    public function getCustomAttributeKeys()
    {
        return [];
    }
    
    public function getBaseColumnSet()
    {
        return new ColumnSet();
    }
    
    public function getAvailableColumnSet()
    {
        return new Available();
    }
    
    public function getCurrentColumnSet()
    {
        return ColumnSet::getCurrent();
    }
    
    public function createSearchResultObject($columns, $list)
    {
        return new Result($columns, $list);
    }
    
    public function getItemList()
    {
        return new ActionList();
    }
    
    public function getDefaultColumnSet()
    {
        return new DefaultSet();
    }
    
    public function getSavedSearch()
    {
        return new SavedActionsSearch();
    }
} 