<?php

/**
 * This class is a custom Zend Validator and validates a date.
 * It simpley delegates to Inspekt's testDate() function.
 *
 * see: http://framework.zend.com/manual/en/zend.validate.writing_validators.html
 */

class Validate_Date extends Zend_Validate_Abstract
{
    // error codes
    const NOT_DATE = 'notDate';

    // error messages
    protected $_messageTemplates = array(
        self::NOT_DATE => "'%value%' does not appear to be a valid date"
    );

    public function isValid($value)
    {
        // this line populates the "%value%" variables in the error messages
        $this->_setValue($value);

        if (($timestamp = strtotime($value)) === false)
        {
            $this->_error(self::NOT_DATE);
            return false;
        }

        // check format - convert into ISO-8601 format
        if (! Inspekt::isDate(date('o-m-N', $timestamp)))
        {
            // this line will insert the error message in the list of errors to 
            // be returned to the caller
            $this->_error(self::NOT_DATE);
            return false;
        }

        return true;
    }
}

