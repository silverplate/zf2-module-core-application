<?php

namespace CoreApplication\Entity;

trait IdTrait
{
    /** @var int */
    protected $_id;

    /**
     * @param int $_id
     */
    public function setId($_id)
    {
        $this->_id = $_id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->_id;
    }
}
