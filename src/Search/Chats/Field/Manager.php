<?php

/**
 *
 * This file was build with the Entity Designer add-on.
 *
 * https://www.concrete5.org/marketplace/addons/entity-designer
 *
 */

/** @noinspection DuplicatedCode */

namespace KatalysisAiChatBot\Search\Chats\Field;

use Concrete\Core\Search\Field\Manager as FieldManager;
use KatalysisAiChatBot\Entity\Chat;
use KatalysisAiChatBot\Search\Chats\Field\Field\LocationField;
use KatalysisAiChatBot\Search\Chats\Field\Field\LlmField;

class Manager extends FieldManager
{
    
    public function __construct()
    {
        $properties = [
            new LocationField(),
            new LlmField(),
        ];
        $this->addGroup(t('Core Properties'), $properties);
    }
}
