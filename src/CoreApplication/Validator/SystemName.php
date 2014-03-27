<?php

namespace CoreApplication\Validator;

use Zend\Validator\Regex;

class SystemName extends Regex
{
    public function __construct()
    {
        parent::__construct('/^[a-z0-9\-]*$/');

        $this->setMessage(
            'Expects only lowercase letters (a—z) and digits',
            static::NOT_MATCH
        );
    }
}
