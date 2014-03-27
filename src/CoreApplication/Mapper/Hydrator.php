<?php

namespace CoreApplication\Mapper;

use Zend\Stdlib\Hydrator\Reflection;
use Ext\String;

class Hydrator extends Reflection
{
    protected static $_map = array();
    protected $_table;

    public function __construct(AbstractMapper $_mapper)
    {
        parent::__construct();

        $this->_table = $_mapper->getTable();
        $this->initMap($_mapper->getAttrs(), $_mapper->getPri());
    }

    protected function _mapField($_keyFrom, $_keyTo, array $_map)
    {
        $map = $_map;

        if (array_key_exists($_keyFrom, $map)) {
            $map[$_keyTo] = $map[$_keyFrom];
            unset($map[$_keyFrom]);
        }

        return $map;
    }

    public function hydrate(array $_data, $_object)
    {
        $data = $_data;

        foreach ($this->getMap() as $from => $to) {
            $data = $this->_mapField($from, $to, $data);
        }

        return parent::hydrate($data, $_object);
    }

    public function extract($_object)
    {
        $data = parent::extract($_object);

        foreach ($this->getMap() as $to => $from) {
            $data = $this->_mapField($from, $to, $data);
        }

        $data = array_intersect_key($data, $this->getMap());


        // Подстановка значений по умолчанию

        foreach (array_keys($data) as $key) {
            if ($data[$key] === null) {
                if ($key == 'creation_time') {
                    $data[$key] = time();
                }

            } else if (strpos($key, 'is_') === 0) {
                $data[$key] = $data[$key] ? 1 : 0;

            }

            if ($key == 'modification_time') {
                $data[$key] = time();
            }
        }

        return $data;
    }

    public function getMap()
    {
        return static::$_map[$this->_table];
    }

    public function initMap(array $_attrs, array $_keys)
    {
        // Ключ из нескольких атрибутов, упрощать его до _id не нужно.
        if (count($_keys) > 1) {
            $attrs = $_attrs;

        // Первичный ключ БД table_id в модели упрощается до свойства _id;
        } else {
            $attrs = array();

            foreach ($_attrs as $attr) {
                $attrs[] = in_array($attr, $_keys)
                         ? array($attr => '_id')
                         : $attr;
            }
        }

        static::$_map[$this->_table] = array();

        foreach ($attrs as $attr) {
            if (is_array($attr)) {
                static::$_map[$this->_table][key($attr)] = current($attr);

            } else {
                static::$_map[$this->_table][$attr] =
                    '_' . String::upperCase($attr, true);
            }
        }
    }
}
