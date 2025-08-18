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
use KatalysisAiChatBot\Search\Chats\Field\Field\StartedField;
use KatalysisAiChatBot\Search\Chats\Field\Field\NameField;
use KatalysisAiChatBot\Search\Chats\Field\Field\EmailField;
use KatalysisAiChatBot\Search\Chats\Field\Field\PhoneField;
use KatalysisAiChatBot\Search\Chats\Field\Field\LaunchPageTitleField;
use KatalysisAiChatBot\Search\Chats\Field\Field\UtmSourceField;
use KatalysisAiChatBot\Search\Chats\Field\Field\LaunchPageUrlField;
use KatalysisAiChatBot\Search\Chats\Field\Field\LaunchPageTypeField;
use KatalysisAiChatBot\Search\Chats\Field\Field\FirstMessageField;
use KatalysisAiChatBot\Search\Chats\Field\Field\LastMessageField;
use KatalysisAiChatBot\Search\Chats\Field\Field\UtmIdField;
use KatalysisAiChatBot\Search\Chats\Field\Field\UtmMediumField;
use KatalysisAiChatBot\Search\Chats\Field\Field\UtmCampaignField;
use KatalysisAiChatBot\Search\Chats\Field\Field\UtmTermField;
use KatalysisAiChatBot\Search\Chats\Field\Field\UtmContentField;

class Manager extends FieldManager
{
    
    public function __construct()
    {
        $coreProperties = [
            new LocationField(),
            new LlmField(),
            new StartedField(),
        ];
        
        $contactProperties = [
            new NameField(),
            new EmailField(),
            new PhoneField(),
        ];
        
        $pageProperties = [
            new LaunchPageTitleField(),
            new LaunchPageUrlField(),
            new LaunchPageTypeField(),
        ];
        
        $messageProperties = [
            new FirstMessageField(),
            new LastMessageField(),
        ];
        
        $utmProperties = [
            new UtmSourceField(),
            new UtmIdField(),
            new UtmMediumField(),
            new UtmCampaignField(),
            new UtmTermField(),
            new UtmContentField(),
        ];
        
        $this->addGroup(t('Core Properties'), $coreProperties);
        $this->addGroup(t('Contact Information'), $contactProperties);
        $this->addGroup(t('Page Information'), $pageProperties);
        $this->addGroup(t('Message Content'), $messageProperties);
        $this->addGroup(t('UTM Parameters'), $utmProperties);
    }
}
