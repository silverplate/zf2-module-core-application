<?php

namespace CoreApplication\Mapper;

use Zend\Db\TableGateway\Feature;
use Zend\Db\Sql;

class ObjectTypeFeature extends Feature\AbstractFeature
{
    private $_objectTypeId;

    public function __construct($_objectTypeId)
    {
        $this->_objectTypeId = $_objectTypeId;
    }

    public function preSelect(Sql\Select $_select)
    {
        $this->_where($_select);
    }

//    public function preInsert(Sql\Insert $_insert)
//    {
//        $_insert->values(
//            array('object_type_id' => $this->_objectTypeId),
//            Sql\Insert::VALUES_MERGE
//        );
//    }

    public function preUpdate(Sql\Update $_update)
    {
        $this->_where($_update);
    }

    public function preDelete(Sql\Delete $_delete)
    {
        $this->_where($_delete);
    }

    /**
     * @param $_instance \Zend\Db\Sql\Select
     */
    protected function _where($_instance)
    {
        $table = $this->tableGateway->getTable();

        $_instance->where([
            "$table.object_type_id" => $this->_objectTypeId
        ]);
    }
}
