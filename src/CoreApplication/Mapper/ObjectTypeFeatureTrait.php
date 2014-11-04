<?php

namespace CoreApplication\Mapper;

use Zend\Db\TableGateway\Feature\FeatureSet;

trait ObjectTypeFeatureTrait
{
    /** @var int */
    private $_objectTypeId;

    /**
     * @param $_objectTypeId int
     */
    public function __construct($_objectTypeId = null)
    {
        if ($_objectTypeId !== false) {
            if ($_objectTypeId === null) {
                $prototype = $this->getEntityPrototype();
                $objectTypeId = $prototype->getObjectTypeId();

            } else {
                $objectTypeId = $_objectTypeId;
            }

            $this->setObjectTypeId($objectTypeId);

            $this->featureSet = new FeatureSet;
            $this->featureSet->addFeature(
                new ObjectTypeFeature($this->getObjectTypeId())
            );
        }

        parent::__construct();
    }

    /**
     * @param int $_objectTypeId
     */
    public function setObjectTypeId($_objectTypeId)
    {
        $this->_objectTypeId = $_objectTypeId;
    }

    /**
     * @return int
     */
    public function getObjectTypeId()
    {
        return $this->_objectTypeId;
    }
}
