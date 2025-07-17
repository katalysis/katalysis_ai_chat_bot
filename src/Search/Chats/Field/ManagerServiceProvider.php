<?php

/** @noinspection DuplicatedCode */

namespace KatalysisAiChatBot\Search\Chats\Field;

use Concrete\Core\Foundation\Service\Provider as ServiceProvider;

class ManagerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app['manager/search_field/chats'] = function ($app) {
            return $app->make('use KatalysisAiChatBot\Search\Chats\Field\Manager');
        };
    }
}
