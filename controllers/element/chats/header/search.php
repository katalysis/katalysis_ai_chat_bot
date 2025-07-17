<?php

/**
 *
 * This file was build with the Entity Designer add-on.
 *
 * https://www.concrete5.org/marketplace/addons/entity-designer
 *
 */

/** @noinspection DuplicatedCode */

namespace Concrete\Package\KatalysisAiChatBot\Controller\Element\Chats\Header;

use Concrete\Core\Controller\ElementController;
use Concrete\Core\Entity\Search\Query;
use Concrete\Core\Foundation\Serializer\JsonSerializer;

class Search extends ElementController
{
    protected $headerSearchAction;
    protected $query;
    protected $pkgHandle = "katalysis_ai_chat_bot";
    
    public function getElement()
    {   
        return "chats/header/search";
    }
    
    public function setQuery(Query $query = null): void
    {
        $this->query = $query;
    }
    
    public function setHeaderSearchAction(string $headerSearchAction): void
    {
        $this->headerSearchAction = $headerSearchAction;
    }
    
    public function view()
    {
        $this->set('form', $this->app->make('helper/form'));
        $this->set('token', $this->app->make('token'));
        
        if (isset($this->headerSearchAction)) {
            $this->set('headerSearchAction', $this->headerSearchAction);
        } else {
            $this->set('headerSearchAction', $this->app->make('url')->to('/dashboard/katalysis_ai_chat_bot/chats'));
        }
        
        if (isset($this->query)) {
            $this->set('query', $this->app->make(JsonSerializer::class)->serialize($this->query, 'json'));
        }
    }
}
