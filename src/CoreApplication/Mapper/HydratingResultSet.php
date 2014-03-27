<?php

namespace CoreApplication\Mapper;

class HydratingResultSet extends \Zend\Db\ResultSet\HydratingResultSet
{
    public function asArray()
    {
        $return = array();

        foreach ($this as $row) {
            if (method_exists($row, 'getId')) {
                $key = is_array($row->getId())
                     ? implode('-', $row->getId())
                     : $row->getId();

                $return[$key] = $row;

            } else {
                $return[] = $row;
            }
        }

        return $return;
    }
}
