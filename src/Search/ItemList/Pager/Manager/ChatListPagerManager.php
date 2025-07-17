<?php

/**
 *
 * This file was build with the Entity Designer add-on.
 *
 * https://www.concrete5.org/marketplace/addons/entity-designer
 *
 */

/** @noinspection DuplicatedCode */

namespace KatalysisAiChatBot\Search\ItemList\Pager\Manager;

use KatalysisAiChatBot\Entity\Chat;
use KatalysisAiChatBot\Search\Chats\ColumnSet\Available;
use Concrete\Core\Search\ItemList\Pager\PagerProviderInterface;
use Concrete\Core\Search\ItemList\Pager\Manager\AbstractPagerManager;
use Doctrine\ORM\EntityManagerInterface;
use Concrete\Core\Support\Facade\Application;

class ChatListPagerManager extends AbstractPagerManager
{
    /** 
     * @param Chat $chat
     * @return int 
     */
    public function getCursorStartValue($chat)
    {
        return $chat->getId();
    }
    
    public function getCursorObject($cursor)
    {
        $app = Application::getFacadeApplication();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $app->make(EntityManagerInterface::class);
        return $entityManager->getRepository(Chat::class)->findOneBy(["id" => $cursor]);
    }
    
    public function getAvailableColumnSet()
    {
        return new Available();
    }
    
    public function sortListByCursor(PagerProviderInterface $itemList, $direction)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $itemList->getQueryObject()->addOrderBy('c.id', $direction);
    }
}
