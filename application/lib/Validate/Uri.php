<?php

/**
 * This class is a custom Zend Validator and validates a website URI.
 * It simpley delegates to Inspekt's testUri() function.
 *
 * see: http://framework.zend.com/manual/en/zend.validate.writing_validators.html
 */

class Validate_Uri extends Zend_Validate_Abstract
{
    // error codes
    const NOT_URI = 'notUri';

    // error messages
    protected $_messageTemplates = array(
        self::NOT_URI => "%value% not a valid URI"
    );

    public function isValid($value)
    {
        // this line populates the "%value%" variables in the error messages
        $this->_setValue($value);

        // check format
        if (! Inspekt::isUri($value))
        {
            // this line will insert the error message in the list of errors to 
            // be returned to the caller
            $this->_error(self::NOT_URI);
            return false;
        }

        return true;
    }
}

