<?php

/**
 * This class is a custom Zend Validator and validates a (US) phone #.
 * It simpley delegates to Inspekt's testPhone() function. It can receive the
 * form value via constructor or via isValid() method.
 *
 * see: http://framework.zend.com/manual/en/zend.validate.writing_validators.html
 */

class Validate_Phone extends Zend_Validate_Abstract
{
    // error codes
    const NOT_PHONE = 'notPhone';

    // error messages
    protected $_messageTemplates = array(
        self::NOT_PHONE => "'%value%' not a valid phone number"
    );

    protected $_phone;

    public function __construct($phone = null)
    {
        $this->_phone = trim($phone);
    }

    public function isValid($value)
    {
        if (! empty($this->_phone))
        {
            $value = $this->_phone;
        }

        // this line populates the "%value%" variables in the error messages
        $this->_setValue($value);

        // check format
        if (! Inspekt::isPhone($value))
        {
            // this line will insert the error message in the list of errors to 
            // be returned to the caller
            $this->_error(self::NOT_PHONE);
            return false;
        }

        return true;
    }
}

