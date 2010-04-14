<?php

/**
 * This class is a custom Zend Validator and validates a zipcode.
 *
 * see: http://framework.zend.com/manual/en/zend.validate.writing_validators.html
 */

class Validate_Zipcode extends Zend_Validate_Abstract
{
    // error codes
    const NOT_FOUND = 'notFound';
    const NOT_ZIPCODE = 'notZipcode';

    // error messages
    protected $_messageTemplates = array(
        self::NOT_FOUND => "%value% not found",
        self::NOT_ZIPCODE => "%value% not in valid format"
    );

    public function isValid($value)
    {
        // this line populates the "%value%" variables in the error messages
        $this->_setValue($value);

        // there are two ways a zipcode can fail
        // 1) if it is not found in the database of zipcodes
        // 2) if it is not the right format (e.g. 5 digits)

        // check format
        if (! preg_match('/^\d{5}$/', $value))
        {
            // this line will insert the error message in the list of errors to 
            // be returned to the caller
            $this->_error(self::NOT_ZIPCODE);
            return false;
        }

        // check in DB
        $sql = "select zip_code from zip_code where lpad(zip_code, 5, '0') = ? limit 1";
        $rows = Zend_Registry::get('db')->getRows($sql, array($value));

        if (count($rows))
        {
            return true;
        }
        else
        {
            // this line will insert the error message in the list of errors to 
            // be returned to the caller
            $this->_error(self::NOT_FOUND);
            return false;
        }
    }
}

