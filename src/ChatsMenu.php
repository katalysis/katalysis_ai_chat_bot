<?php

/** @noinspection DuplicatedCode */
/** @noinspection PhpUnused */

namespace KatalysisAiChatBot;

use Concrete\Core\Application\UserInterface\ContextMenu\DropdownMenu;
use Concrete\Core\Application\UserInterface\ContextMenu\Item\LinkItem;
use Concrete\Core\Support\Facade\Url;
use KatalysisAiChatBot\Entity\Chat;

class ChatsMenu extends DropdownMenu
{
    protected $menuAttributes = ['class' => 'ccm-popover-page-menu'];
    
    public function __construct(Chat $chat)
    {
        parent::__construct();
        
        $this->addItem(
            new LinkItem(
                (string)Url::to("/dashboard/katalysis_ai_chat_bot/chats/edit", $chat->getId()),
                t('Edit')
            )
        );
        
        $this->addItem(
            new LinkItem(
                (string)Url::to("/dashboard/katalysis_ai_chat_bot/chats/remove", $chat->getId()),
                t('Remove')
            )
        );
    }
}
