<?php

/**
 * This class is a custom Zend Validator and validates a (US) zipcode.
 * It simpley delegates to Inspekt's testZip() function.
 *
 * see: http://framework.zend.com/manual/en/zend.validate.writing_validators.html
 */

class Validate_Zipcode extends Zend_Validate_Abstract
{
    // error codes
    const NOT_ZIPCODE = 'notZipcode';

    // error messages
    protected $_messageTemplates = array(
        self::NOT_ZIPCODE => "'%value%' not a valid zipcode"
    );

    public function isValid($value)
    {
        // this line populates the "%value%" variables in the error messages
        $this->_setValue($value);

        // check format
        if (! Inspekt::isZip($value))
        {
            // this line will insert the error message in the list of errors to 
            // be returned to the caller
            $this->_error(self::NOT_ZIPCODE);
            return false;
        }

        return true;
    }
}

