<?php

/** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */

namespace KatalysisAiChatBot;

use Concrete\Core\Application\UserInterface\ContextMenu\DropdownMenu;
use Concrete\Core\Application\UserInterface\ContextMenu\Item\LinkItem;
use Concrete\Core\Support\Facade\Url;
use KatalysisAiChatBot\Entity\Action;

class ActionsMenu extends DropdownMenu
{
    protected $menuAttributes = ['class' => 'ccm-popover-page-menu'];
    
    public function __construct(Action $action)
    {
        parent::__construct();
        
        $this->addItem(
            new LinkItem(
                (string)Url::to("/dashboard/katalysis_ai_chat_bot/actions/edit", $action->getId()),
                t('Edit')
            )
        );
        
        $this->addItem(
            new LinkItem(
                (string)Url::to("/dashboard/katalysis_ai_chat_bot/actions/remove", $action->getId()),
                t('Remove')
            )
        );
    }
} 