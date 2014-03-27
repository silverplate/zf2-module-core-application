<?php

namespace CoreApplication\Entity;

trait SortOrderTrait
{
    /** @var int */
    protected $_sortOrder;

    /**
     * @param int $_sortOrder
     */
    public function setSortOrder($_sortOrder)
    {
        $this->_sortOrder = $_sortOrder;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->_sortOrder;
    }

    /**
     * @param SortOrderTrait $_a
     * @param SortOrderTrait $_b
     * @return int
     */
    public static function sort($_a, $_b)
    {
        $a = $_a->getSortOrder();
        $b = $_b->getSortOrder();

        if ($a == $b)     return 0;
        else if ($a > $b) return 1;
        else              return -1;
    }
}