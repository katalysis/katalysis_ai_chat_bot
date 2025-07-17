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

use KatalysisAiChatBot\Entity\Chat;
use KatalysisAiChatBot\Search\ItemList\Pager\Manager\ChatListPagerManager;
use Concrete\Core\Search\ItemList\Database\ItemList;
use Concrete\Core\Search\ItemList\Pager\PagerProviderInterface;
use Concrete\Core\Search\ItemList\Pager\QueryString\VariableFactory;
use Concrete\Core\Search\Pagination\PaginationProviderInterface;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Permission\Key\Key;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Closure;

class ChatList extends ItemList implements PagerProviderInterface, PaginationProviderInterface
{
    protected $isFulltextSearch = false;
    protected $autoSortColumns = ['c.id', 'c.started', 'c.location', 'c.llm'];
    protected $permissionsChecker = -1;
    
    public function createQuery()
    {
        $this->query->select('c.*')
            ->from("KatalysisChats", "c");
    }
    
    public function finalizeQuery(QueryBuilder $query)
    {
        return $query;
    }
    
    /**
     * @param string $keywords
     */
    public function filterByKeywords($keywords)
    {
        $this->query->andWhere('(c.`id` LIKE :keywords OR c.`started` LIKE :keywords OR c.`location` LIKE :keywords OR c.`llm` LIKE :keywords)');
        $this->query->setParameter('keywords', '%' . $keywords . '%');
    }
    
    
    /**
     * @param string $location
     */
    public function filterByLocation($location)
    {
        $this->query->andWhere('c.`location` LIKE :location');
        $this->query->setParameter('location', '%' . $location . '%');
    }
    
    /**
     * @param string $llm
     */
    public function filterByLlm($llm)
    {
        $this->query->andWhere('c.`llm` LIKE :llm');
        $this->query->setParameter('llm', '%' . $llm . '%');
    }
    
    
    /**
    * @param array $queryRow
    * @return Chat
    */
    public function getResult($queryRow)
    {
        $app = Application::getFacadeApplication();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $app->make(EntityManagerInterface::class);
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $entityManager->getRepository(Chat::class)->findOneBy(["id" => $queryRow["id"]]);
    }
    
    public function getTotalResults()
    {
        if ($this->permissionsChecker === -1) {
            return $this->deliverQueryObject()
                ->resetQueryParts(['groupBy', 'orderBy'])
                ->select('count(distinct c.id)')
                ->setMaxResults(1)
                ->execute()
                ->fetchColumn();
            }
        
        return -1; // unknown
    }
    
    public function getPagerManager()
    {
        return new ChatListPagerManager($this);
    }
    
    public function getPagerVariableFactory()
    {
        return new VariableFactory($this, $this->getSearchRequest());
    }
    
    public function getPaginationAdapter()
    {
        return new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $query->resetQueryParts(['groupBy', 'orderBy'])
                ->select('count(distinct c.id)')
                ->setMaxResults(1);
        });
    }
    
    public function checkPermissions($mixed)
    {
        if (isset($this->permissionsChecker)) {
            if ($this->permissionsChecker === -1) {
                return true;
            }
            
            /** @noinspection PhpParamsInspection */
            return call_user_func_array($this->permissionsChecker, [$mixed]);
        }
        
        $permissionKey = Key::getByHandle("read_katalysis_chats");
        return $permissionKey->validate();
    }
    
    public function setPermissionsChecker(Closure $checker = null)
    {
        $this->permissionsChecker = $checker;
    }
    
    public function ignorePermissions()
    {
        $this->permissionsChecker = -1;
    }
    
    public function getPermissionsChecker()
    {
        return $this->permissionsChecker;
    }
    
    public function enablePermissions()
    {
        unset($this->permissionsChecker);
    }
    
    public function isFulltextSearch()
    {
        return $this->isFulltextSearch;
    }
}
