<?php

/**
 *
 * This file was build with the Entity Designer add-on.
 *
 * https://www.concrete5.org/marketplace/addons/entity-designer
 *
 */

/** @noinspection DuplicatedCode */

namespace KatalysisAiChatBot\Search\Chats\ColumnSet;


use KatalysisAiChatBot\Search\Chats\ColumnSet\Column\IdColumn;
use KatalysisAiChatBot\Search\Chats\ColumnSet\Column\LocationColumn;
use KatalysisAiChatBot\Search\Chats\ColumnSet\Column\LlmColumn;


class DefaultSet extends ColumnSet
{
    protected $attributeClass = 'CollectionAttributeKey';
    
    public function __construct()
    {
        $this->addColumn(new IdColumn());
        $this->addColumn(new LocationColumn());
        $this->addColumn(new LlmColumn());
        $id = $this->getColumnByKey('c.id');
        $this->setDefaultSortColumn($id, 'desc');
    }
}
