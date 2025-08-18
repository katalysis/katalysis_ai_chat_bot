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

namespace KatalysisAiChatBot\Routing;

use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;

class ChatsRouteList implements RouteListInterface
{
    public function loadRoutes(Router $router)
    {
        $router->buildGroup()->setNamespace('Concrete\Package\KatalysisAiChatBot\Controller\Dialog\Chats')
            ->setPrefix('/ccm/system/dialogs/chats')
            ->routes('dialogs/chats.php', 'katalysis_ai_chat_bot');
    
        $router->buildGroup()->setNamespace('Concrete\Package\KatalysisAiChatBot\Controller\Search')
            ->setPrefix('/ccm/system/search/chats')
            ->routes('search/chats.php', 'katalysis_ai_chat_bot');
    }
}
