<?php 
namespace Concrete\Package\KatalysisAiChatBot;

use Config;
use Page;
use Concrete\Core\Package\Package;
use SinglePage;
use View;
use AssetList;
use Asset;
use Concrete\Core\Command\Task\Manager as TaskManager;
use KatalysisAiChatBot\Command\Task\Controller\BuildRagIndexController;

class Controller extends Package
{
    protected $pkgHandle = 'katalysis_ai_chat_bot';
    protected $appVersionRequired = '9.3';
    protected $pkgVersion = '0.1.3';
    protected $pkgAutoloaderRegistries = [
        'src' => 'KatalysisAiChatBot'
    ];

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::$packageDependencies
     */
    protected $packageDependencies = [
        'katalysis_neuron_ai' => true,
    ];

    protected $single_pages = array(

        '/dashboard/katalysis_ai_chat_bot/chats' => array(
            'cName' => 'Chats'
        ),
        '/dashboard/katalysis_ai_chat_bot/chat_bot_settings' => array(
            'cName' => 'Chat Bot Settings'
        ),

    );

    public function getPackageName()
    {
        return t("Katalysis AI Chat Bot");
    }

    public function getPackageDescription()
    {
        return t("Adds AI chat bot capabilities");
    }

    public function on_start()
    {
        $this->setupAutoloader();

        $entityDesignerServiceProvider = $this->app->make(\KatalysisAiChatBot\EntityDesignerServiceProvider::class);
        $entityDesignerServiceProvider->register();

        // Register the chats search service provider
        $chatsServiceProvider = $this->app->make(\KatalysisAiChatBot\ChatsServiceProvider::class);
        $chatsServiceProvider->register();

        $version = $this->getPackageVersion();

        $al = AssetList::getInstance();
        $al->register('css', 'katalysis-ai', 'css/katalysis-ai.css', ['version' => $version, 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false], $this);
        
        $manager = $this->app->make(TaskManager::class);
		$manager->extend('build_rag_index', function () {
			return new BuildRagIndexController();
		});
    }

    private function setupAutoloader()
    {
        if (file_exists($this->getPackagePath() . '/vendor')) {
            require_once $this->getPackagePath() . '/vendor/autoload.php';
        }
    }

    public function install()
    {
        $this->setupAutoloader();

        $pkg = parent::install();

        $this->installPages($pkg);
        $this->installContentFile('build_rag_index.xml');
        
    }

    public function upgrade()
    {
		parent::upgrade();

        $this->installContentFile('build_rag_index.xml');


    }


    /**
     * @param Package $pkg
     * @return void
     */
    protected function installPages($pkg)
    {
        foreach ($this->single_pages as $path => $value) {
            if (!is_array($value)) {
                $path = $value;
                $value = array();
            }
            $page = Page::getByPath($path);
            if (!$page || $page->isError()) {
                $single_page = SinglePage::add($path, $pkg);

                if ($value) {
                    $single_page->update($value);
                }
            }
        }
    }
}
