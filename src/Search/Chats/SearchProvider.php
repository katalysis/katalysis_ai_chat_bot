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

namespace KatalysisAiChatBot\Search\Chats;

use KatalysisAiChatBot\Entity\Search\SavedChatsSearch;
use KatalysisAiChatBot\ChatList;
use KatalysisAiChatBot\Search\Chats\ColumnSet\DefaultSet;
use KatalysisAiChatBot\Search\Chats\ColumnSet\Available;
use KatalysisAiChatBot\Search\Chats\ColumnSet\ColumnSet;
use KatalysisAiChatBot\Search\Chats\Result\Result;
use Concrete\Core\Search\AbstractSearchProvider;
use Concrete\Core\Search\Field\ManagerFactory;

class SearchProvider extends AbstractSearchProvider
{
    public function getFieldManager()
    {
        return ManagerFactory::get('chats');
    }
    
    public function getSessionNamespace()
    {
        return 'chats';
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
        return new ChatList();
    }
    
    public function getDefaultColumnSet()
    {
        return new DefaultSet();
    }
    
    public function getSavedSearch()
    {
        return new SavedChatsSearch();
    }
}
