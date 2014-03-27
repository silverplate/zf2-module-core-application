<?php

namespace CoreApplication\Entity;

use Ext\String;

trait ExchangeArrayTrait
{
    /**
     * @throws \Exception
     * @return array
     */
    public static function getExchangeAttrs()
    {
        throw new \Exception(__METHOD__ . ' must be implemented');
    }

    /**
     * Exchange internal values from provided array
     *
     * @param  array $_data
     * @return void
     */
    public function exchangeArray(array $_data)
    {
        foreach (static::getExchangeAttrs() as $name) {
            if (strpos($name, 'is_') === 0) {
                $method = String::upperCase($name, true);
            } else {
                $method = 'set' . String::upperCase($name);
            }

            if (
                array_key_exists($name, $_data) &&
                method_exists($this, $method)
            ) {
                $this->$method($_data[$name] === '' ? null : $_data[$name]);
            }
        }
    }

    /**
     * Return an array representation of the object
     *
     * @return array
     */
    public function getArrayCopy()
    {
        $data = array();

        foreach (static::getExchangeAttrs() as $name) {
            if (strpos($name, 'is_') === 0) {
                $method = String::upperCase($name, true);
            } else {
                $method = 'get' . String::upperCase($name);
            }

            if (method_exists($this, $method)) {
                $data[$name] = $this->$method();
            }
        }

        return $data;
    }
}