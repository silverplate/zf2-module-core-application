<?php

namespace CoreApplication\Entity;

trait IsPublishedTrait
{
    /** @var bool */
    protected $_isPublished;

    /**
     * @param bool $_isPublished
     * @return bool
     */
    public function isPublished($_isPublished = null)
    {
        if (!is_null($_isPublished)) {
            $this->_isPublished = (bool) $_isPublished;
        }

        return (bool) $this->_isPublished;
    }

    /**
     * Especially for form hydration.
     *
     * @param $_isPublished
     * @return bool
     */
    public function setIsPublished($_isPublished)
    {
        return $this->isPublished((bool) $_isPublished);
    }
}
