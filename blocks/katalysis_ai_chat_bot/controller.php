<?php

namespace Concrete\Package\KatalysisAiChatBot\Block\KatalysisAiChatbot;

use Concrete\Core\Block\BlockController;
use Concrete\Core\Page\Page;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Support\Facade\Application;

class Controller extends BlockController
{
    protected $btTable = 'btKatalysisAiChatBot';
    protected $btInterfaceWidth = 400;
    protected $btInterfaceHeight = 500;
    protected $btCacheBlockRecord = false;
    protected $btCacheBlockOutput = false;
    protected $btCacheBlockOutputOnPost = false;
    protected $btCacheBlockOutputForRegisteredUsers = false;
    protected $btWrapperClass = 'ccm-ui';
    protected $btDefaultSet = 'katalysis';

    public function getBlockTypeName()
    {
        return t('Katalysis AI Chatbot');
    }

    public function getBlockTypeDescription()
    {
        return t('Adds an AI-powered chatbot to your page');
    }

    public function add()
    {
        $this->set('title', '');
        $this->set('showTitle', true);
        $this->set('chatbotPosition', 'bottom-right');
        $this->set('theme', 'light');
    }

    public function edit()
    {
        // Load existing values from the block record
        $this->set('title', $this->title ?? '');
        $this->set('showTitle', $this->showTitle ?? true);
        $this->set('chatbotPosition', $this->chatbotPosition ?? 'bottom-right');
        $this->set('theme', $this->theme ?? 'light');
    }

    public function save($args)
    {
        $args['title'] = trim($args['title'] ?? '');
        $args['showTitle'] = !empty($args['showTitle']);
        $args['chatbotPosition'] = $args['chatbotPosition'] ?? 'bottom-right';
        $args['theme'] = $args['theme'] ?? 'light';
        
        parent::save($args);
    }

    public function view()
    {
        $app = Application::getFacadeApplication();
        $config = $app->make(Repository::class);
        
        // Get AI configuration
        $this->set('openaiKey', $config->get('katalysis.ai.open_ai_key'));
        $this->set('openaiModel', $config->get('katalysis.ai.open_ai_model'));
        
        // Get current page context
        $page = Page::getCurrentPage();
        if ($page) {
            $this->set('pageTitle', $page->getCollectionName());
            $this->set('pageUrl', $page->getCollectionLink());
            $this->set('pageType', $this->getPageType($page));
        }
        
        // Get welcome message prompt from settings
        $this->set('welcomePrompt', $config->get('katalysis.aichatbot.welcome_message_prompt', ''));
    }

    private function getPageType($page)
    {
        // Determine page type based on page template or attributes
        $template = $page->getPageTemplateHandle();
        
        if (strpos($template, 'service') !== false) {
            return 'service';
        } elseif (strpos($template, 'article') !== false) {
            return 'article';
        } elseif (strpos($template, 'contact') !== false) {
            return 'contact';
        } elseif (strpos($template, 'about') !== false) {
            return 'about';
        } else {
            return 'page';
        }
    }

    public function getSearchableContent()
    {
        $content = '';
        if ($this->title) {
            $content .= $this->title . ' ';
        }
        return $content;
    }
} 