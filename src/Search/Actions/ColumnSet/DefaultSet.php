<?php

/**
 *
 * This file was build with the Entity Designer add-on.
 *
 * https://www.concrete5.org/marketplace/addons/entity-designer
 *
 */

/** @noinspection DuplicatedCode */

namespace KatalysisAiChatBot\Search\Actions\ColumnSet;


use KatalysisAiChatBot\Search\Actions\ColumnSet\Column\IdColumn;
use KatalysisAiChatBot\Search\Actions\ColumnSet\Column\NameColumn;
use KatalysisAiChatBot\Search\Actions\ColumnSet\Column\IconColumn;
use KatalysisAiChatBot\Search\Actions\ColumnSet\Column\TriggerInstructionColumn;


class DefaultSet extends ColumnSet
{
    protected $attributeClass = 'CollectionAttributeKey';
    
    public function __construct()
    {
        $this->addColumn(new IdColumn());
        $this->addColumn(new NameColumn());
        $this->addColumn(new IconColumn());
        $this->addColumn(new TriggerInstructionColumn());
        $id = $this->getColumnByKey('a.id');
        $this->setDefaultSortColumn($id, 'desc');
    }
} 