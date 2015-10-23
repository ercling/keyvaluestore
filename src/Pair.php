<?php
namespace keyvaluestore;
use keyvaluestore\exceptions\WrongTypeException;

class Pair
{
    private $_key;
    private $_value;

    public function __construct($key, $value)
    {
        $this->setKey($key);
        $this->setValue($value);
    }

    public function getKey()
    {
        return $this->_key;
    }

    public function getValue()
    {
        return $this->_value;
    }

    private function setKey($key)
    {
        if(!(is_numeric($key) || is_string($key))){
            throw new WrongTypeException('$key must be number or string');
        }
        $this->_key = $key;
        return true;
    }

    private function setValue($value)
    {
        $this->_value = $value;
        return true;
    }
}