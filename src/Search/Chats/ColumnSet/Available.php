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

use KatalysisAiChatBot\Search\Chats\ColumnSet\Column\PhoneColumn;
use KatalysisAiChatBot\Search\Chats\ColumnSet\Column\LaunchPageTitleColumn;
use KatalysisAiChatBot\Search\Chats\ColumnSet\Column\CreatedDateColumn;
use KatalysisAiChatBot\Search\Chats\ColumnSet\Column\UtmSourceColumn;
use KatalysisAiChatBot\Search\Chats\ColumnSet\Column\LaunchPageUrlColumn;
use KatalysisAiChatBot\Search\Chats\ColumnSet\Column\LaunchPageTypeColumn;
use KatalysisAiChatBot\Search\Chats\ColumnSet\Column\FirstMessageColumn;
use KatalysisAiChatBot\Search\Chats\ColumnSet\Column\LastMessageColumn;
use KatalysisAiChatBot\Search\Chats\ColumnSet\Column\UtmIdColumn;
use KatalysisAiChatBot\Search\Chats\ColumnSet\Column\UtmMediumColumn;
use KatalysisAiChatBot\Search\Chats\ColumnSet\Column\UtmCampaignColumn;
use KatalysisAiChatBot\Search\Chats\ColumnSet\Column\UtmTermColumn;
use KatalysisAiChatBot\Search\Chats\ColumnSet\Column\UtmContentColumn;

class Available extends DefaultSet
{
    public function __construct()
    {
        parent::__construct();
        
        // Add additional columns that are available but not in default set
        $this->addColumn(new PhoneColumn());
        $this->addColumn(new LaunchPageTitleColumn());
        $this->addColumn(new CreatedDateColumn());
        $this->addColumn(new UtmSourceColumn());
        $this->addColumn(new LaunchPageUrlColumn());
        $this->addColumn(new LaunchPageTypeColumn());
        $this->addColumn(new FirstMessageColumn());
        $this->addColumn(new LastMessageColumn());
        $this->addColumn(new UtmIdColumn());
        $this->addColumn(new UtmMediumColumn());
        $this->addColumn(new UtmCampaignColumn());
        $this->addColumn(new UtmTermColumn());
        $this->addColumn(new UtmContentColumn());
    }
}
