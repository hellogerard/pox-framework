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
        self::NOT_EQUAL => "'%aValue%' and '%value%' are not equal"
    );

    // these map the error message variables to class variables
    protected $_messageVariables = array(
        'aValue' => '_aValue',
    );

    protected $_aValue;

    public function __construct($aValue)
    {
        $this->_aValue = $aValue;
    }

    public function isValid($value)
    {
        // this line populates the "%value%" variables in the error messages
        $this->_setValue($value);

        // test type too
        if ($this->_aValue === $value)
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

