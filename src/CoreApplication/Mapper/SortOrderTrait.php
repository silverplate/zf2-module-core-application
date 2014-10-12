<?php

namespace CoreApplication\Mapper;

use CoreApplication\Entity;

trait SortOrderTrait
{
    /**
     * @param Entity\IdTrait|Entity\SortOrderTrait $entity
     * @return int
     */
    public function insertEntity($entity)
    {
        $res = parent::insertEntity($entity);

        if ($res && $this->isSortable() && !$entity->getSortOrder()) {
            $entity->setSortOrder($entity->getId());
            $this->updateEntity($entity);
        }

        return $res;
    }
}
