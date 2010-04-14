<?php

/**
 * This class is a custom Zend Validator. It validates that two given values are 
 * equal (in value and in type).
 *
 * see: http://framework.zend.com/manual/en/zend.validate.writing_validators.html
 */

class Validate_Equals extends Zend_Validate_Abstract
{
    // error codes
    const NOT_EQUAL = 'notEqual';

    // error messages
    protected $_messageTemplates = array(
        self::NOT_EQUAL => "'%value1%' and '%value2%' are not equal"
    );

    // these map the error message variables to class variables
    protected $_messageVariables = array(
        'value1' => '_value1',
        'value2' => '_value2'
    );

    // class variables to hold input data
    protected $_value1;
    protected $_value2;

    public function isValid($value)
    {
        list($this->_value1, $this->_value2) = array_values($value);

        // test type too
        if ($this->_value1 === $this->_value2)
        {
            return true;
        }
        else
        {
            $this->_error(self::NOT_EQUAL);
            return false;
        }
    }
}

