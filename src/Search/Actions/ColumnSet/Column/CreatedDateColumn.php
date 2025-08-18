<?php

/**
 *
 * This file was build with the Entity Designer add-on.
 *
 * https://www.concrete5.org/marketplace/addons/entity-designer
 *
 */

/** @noinspection DuplicatedCode */

namespace KatalysisAiChatBot\Search\Actions\ColumnSet\Column;

use Concrete\Core\Search\Column\Column;
use Concrete\Core\Search\Column\PagerColumnInterface;
use Concrete\Core\Search\ItemList\Pager\PagerProviderInterface;
use KatalysisAiChatBot\Entity\Action;
use KatalysisAiChatBot\ActionList;

class CreatedDateColumn extends Column implements PagerColumnInterface
{
    public function getColumnKey()
    {
        return 'a.createdDate';
    }
    
    public function getColumnName()
    {
        return t('Created Date');
    }
    
    public function getColumnCallback()
    {
        return 'getCreatedDate';
    }
    
    /**
    * @param ActionList $itemList
    * @param $mixed Action
    * @noinspection PhpDocSignatureInspection
    */
    public function filterListAtOffset(PagerProviderInterface $itemList, $mixed)
    {
        $query = $itemList->getQueryObject();
        $sort = $this->getColumnSortDirection() == 'desc' ? '<' : '>';
        $where = sprintf('a.createdDate %s :createdDate', $sort);
        $query->setParameter('createdDate', $mixed->getCreatedDate());
        $query->andWhere($where);
    }
} 