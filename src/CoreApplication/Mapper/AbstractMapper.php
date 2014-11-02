<?php

namespace CoreApplication\Mapper;

use Zend\Db\Metadata\Metadata;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\Feature;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractMapper
extends TableGateway
implements ServiceLocatorAwareInterface
{
    protected static $_pri = array();
    protected static $_attrs = array();
    protected $_entityPrototype;
    protected $_hydrator;

    /** @var \Zend\ServiceManager\ServiceLocatorInterface */
    protected $_serviceManager;

    /** @var string */
    protected $_moduleName;

    /**
     * If value is true prefix will be calculated from module name
     * If value is string specified prefix will be added
     *
     * @var bool|string
     */
    protected $_tablePrefix;

    public function __construct()
    {
        if (!$this->table) {
            $this->table = $this->computeTable();
        }

        if (is_null($this->featureSet)) {
            $this->featureSet = new Feature\FeatureSet;
        }

        $this->featureSet->addFeature(new Feature\GlobalAdapterFeature);
        $this->initialize();
    }

    public function computeTable()
    {
        $path = explode(
            '\\',
            preg_replace('/^\\\?[a-zA-Z]+\\\Mapper\\\/', '', get_called_class())
        );


        // Обработка случаев, когда класс находится в одноименной папке.

        $cnt = count($path);

        if ($cnt > 1 && $path[$cnt - 2] == $path[$cnt - 1]) {
            unset($path[$cnt - 1]);
        }

        $table = \Ext\String::underline(implode('_', $path));


        // Add module prefix if module is not base

        if (is_string($this->_tablePrefix)) {
            $table = $this->_tablePrefix . '_' . $table;

        } else if ($this->_tablePrefix) {
            $autoPrefix = strtolower($this->_getModuleName());
            $table = $autoPrefix . '_' . $table;
        }

        return $table;
    }

    /**
     * Gets module name
     *
     * @return string
     */
    protected function _getModuleName()
    {
        if (empty($this->_moduleName)) {
            $this->_moduleName = explode('\\', get_called_class())[0];
        }

        return $this->_moduleName;
    }

    public function setServiceLocator(ServiceLocatorInterface $_serviceLocator)
    {
        $this->_serviceManager = $_serviceLocator;
    }

    public function srv($_name)
    {
        return $this->getServiceLocator()->get($_name);
    }

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->_serviceManager;
    }

    public function saveEntity($_entity)
    {
        return $_entity->getId() && $this->findById($_entity->getId())
             ? $this->updateEntity($_entity)
             : $this->insertEntity($_entity);
    }

    public function findById($_id)
    {
        return $this->hydrate($this->select($this->getWhere($_id)))->current();
    }

    /**
     * @param $_results
     * @return HydratingResultSet
     */
    public function hydrate($_results)
    {
        $items = new HydratingResultSet(
            $this->getHydrator(),
            $this->getEntityPrototype()
        );

        $results = is_array($_results) ? $_results : $_results->toArray();
        return $items->initialize($results);
    }

    public function getHydrator()
    {
        if (!$this->_hydrator) {
            $this->setHydrator($this->computeHydrator());
        }

        return $this->_hydrator;
    }

    public function setHydrator($_hydrator)
    {
        $this->_hydrator = $_hydrator;
    }

    public function computeHydrator()
    {
        return new Hydrator($this);
    }

    public function getEntityPrototype()
    {
        if (!$this->_entityPrototype) {
            $this->setEntityPrototype($this->computeEntityPrototype());
        }

        return $this->_entityPrototype;
    }

    public function setEntityPrototype($_entityPrototype)
    {
        $this->_entityPrototype = $_entityPrototype;
    }

    public function computeEntityPrototype()
    {
        $matches = array();
        preg_match(
            '/^\\\?([a-zA-Z]+)\\\Mapper\\\(.+)$/',
            get_called_class(),
            $matches
        );

        if (!$matches) {
            throw new \Exception('Unable to determine entity class name');
        }

        $className = "\\$matches[1]\\Entity\\$matches[2]";
        return new $className;
    }

    public function getWhere($_where)
    {
        if (is_array($_where) && count($_where) == 0) {
            return $_where;
        }

        $where = is_object($_where) ? $_where->getId() : $_where;

        if (!is_array($where)) {
            $where = array($where);
        }

        return array_combine($this->getPri(), $where);
    }

    public function getPri()
    {
        if (!isset(static::$_pri[$this->getTable()])) {
            static::$_pri[$this->getTable()] = $this->computePri();
        }

        return static::$_pri[$this->getTable()];
    }

    public function computePri()
    {
        $metadata = new Metadata($this->getAdapter());

        foreach ($metadata->getConstraints($this->getTable()) as $item) {
            if ($item->isPrimaryKey()) {
                return $item->getColumns();
            }
        }

        return false;
    }

    public function updateEntity($_entity)
    {
        return $this->update(
            $this->getHydrator()->extract($_entity),
            $this->getWhere($_entity)
        );
    }

    public function insertEntity($_entity)
    {
        $res = $this->insert($this->getHydrator()->extract($_entity));

        if ($res && count($this->getPri()) == 1 && !$_entity->getId()) {
            $_entity->setId($this->getLastInsertValue());
        }

        return $res;
    }

    public function deleteEntity($_entity)
    {
        return $this->delete($this->getWhere($_entity));
    }

    public function findBy($_attr, $_value)
    {
        return $this->hydrate($this->select(array($_attr => $_value)));
    }

    /**
     * @param null $_where
     * @return \CoreApplication\Mapper\HydratingResultSet
     */
    public function fetchAll($_where = null)
    {
        return $this->hydrate($this->select($_where));
    }

    public function isSortable()
    {
        return method_exists($this->getEntityPrototype(), 'sort');
    }

    public function getList($_where = null)
    {
        $list = $this->fetchAll($_where)->asArray();

        if ($this->isSortable()) {
            uasort($list, array($this->getEntityPrototype(), 'sort'));
        }

        return $list;
    }

    public function deleteAll()
    {
        $this->delete(array());

        /**
         * @todo Установка значения AUTO_INCREMENT не работает?
         */
        $this->getAdapter()
             ->createStatement("ALTER TABLE `$this->table` AUTO_INCREMENT = 1")
             ->execute();
    }

    public function getIdList($_where = null, $_onlyKey = null)
    {
        $sql = $this->getSql();
        $select = $sql->select();
        $columns = $_onlyKey
                 ? (is_array($_onlyKey) ? $_onlyKey : array($_onlyKey))
                 : $this->getPri();

        $select->columns($columns);

        if ($_where) {
            $select->where($_where);
        }

        if ($_onlyKey && count($columns) == 1) {
            $select->group(current($columns));
        }

        $result = $this->selectWith($select)->toArray();

        if (count($columns) > 1) {
            return $result;
        } else {
            $tmp = array();
            foreach ($result as $item) $tmp[] = current($item);
            return $tmp;
        }
    }

    /**
     * Gets entities array by related entity id
     *
     * @param \CoreApplication\Mapper\AbstractMapper $_mapper
     * @param int $_entityId
     * @param string $_relationTable
     * @param string $_idColumn
     * @param string $_relationIdColumn
     * @return array[]mixed
     */
    protected function _getRelationEntities(
        $_mapper, $_entityId, $_relationTable, $_idColumn, $_relationIdColumn
    )
    {
        $result = array();
        $sql = new Sql($this->getAdapter());

        $select = $sql->select(array('r' => $_relationTable));
        $select->columns(array($_relationIdColumn));
        $select->where(array($_idColumn => $_entityId));

        $select->join(
            array('t' => $_mapper->getTable()),
            "r.{$_relationIdColumn} = t.{$_relationIdColumn}",
            $_mapper->getAttrs('re_'),
            Select::JOIN_LEFT
        );

        $stmt = $sql->prepareStatementForSqlObject($select);
        foreach ($stmt->execute() as $row) {
            $data = $this->_getJoinedRowData($row);
            $entity = $_mapper->hydrate($data['re'])->current();
            $result[$entity->getId()] = $entity;
        }
        return $result;
    }

// Truncate приводит к ошибке в таблицах, которые участвуют
// во внешних зависимостях.
//    public function truncate()
//    {
//        return $this->getAdapter()
//                    ->getDriver()
//                    ->createStatement("TRUNCATE `$this->table`")
//                    ->execute();
//    }

    public function getAttrs($_prefix = null)
    {
        if (!isset(static::$_attrs[$this->getTable()])) {
            static::$_attrs[$this->getTable()] = $this->computeAttrs();
        }

        if ($_prefix) {
            $attrs = array();

            foreach (static::$_attrs[$this->getTable()] as $value) {
                $attrs[$_prefix . $value] = $value;
            }

            return $attrs;
        }

        return static::$_attrs[$this->getTable()];
    }

    public function computeAttrs()
    {
        $metadata = new Metadata($this->getAdapter());
        return $metadata->getColumnNames($this->getTable());
    }

    /**
     * Gets row data array for joined rows
     * columns expected to have prefix ([a-z]+)_
     *
     * @param array $row
     * @return array
     */
    protected function _getJoinedRowData($row)
    {
        $data = array();

        foreach ($row as $key => $value) {
            $attr = array();
            preg_match('/([a-z]+)_([a-z_]+)$/', $key, $attr);

            if ($attr) {
                if (!array_key_exists($attr[1], $data)) {
                    $data[$attr[1]] = array(array());
                }

                $data[$attr[1]][0][$attr[2]] = $value;
            }
        }

        return $data;
    }
}
