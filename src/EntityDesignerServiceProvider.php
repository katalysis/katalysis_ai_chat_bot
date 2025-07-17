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

namespace KatalysisAiChatBot;

use Concrete\Core\Foundation\Service\Provider as ServiceProvider;

class EntityDesignerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $serviceProviderClassNames = [
            ChatsServiceProvider::class
        ];
        
        foreach ($serviceProviderClassNames as $serviceProviderClassName) {
            /** @var ServiceProvider $serviceProvider */
            $serviceProvider = $this->app->make($serviceProviderClassName);
            $serviceProvider->register();
        }
    }
    
}
