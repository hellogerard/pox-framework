<?php

/**
 * This class is a custom Zend Validator. It validates that a the given birthday 
 * year, month, day represents an age that is greater than or equal to the given 
 * age (default is 18 years old).
 *
 * see: http://framework.zend.com/manual/en/zend.validate.writing_validators.html
 */


class Validate_Age extends Zend_Validate_Abstract
{
    // error codes
    const TOO_YOUNG = 'tooYoung';
    const INVALID_DATE = 'invalidDate';

    // error messages
    protected $_messageTemplates = array(
        self::TOO_YOUNG => "Age is less than %min% years",
        self::INVALID_DATE => "'%value%' does not appear to be a valid date",
    );

    // these map the error message variables to class variables
    protected $_messageVariables = array(
        'min' => '_min'
    );

    // class variables to hold input data
    protected $_min;

    // constructor takes in the minimum age to validate. default is 18 years
    // old.
    public function __construct($min = 18)
    {
        $this->_min = max(0, (int) $min);
    }

    public function isValid($value)
    {
        // this line populates the "%value%" variables in the error messages
        $this->_setValue($value);

        // check for invalid date format
        $validator = new Validate_Date;
        if (! $validator->isValid($value))
        {
            // this line will insert the error message in the list of errors to 
            // be returned to the caller
            $this->_error(self::INVALID_DATE);
            return false;
        }

        // check if birthday is $min yrs. ago or greater

        // break up date
        list($year, $month, $day) = sscanf($value, '%d-%d-%d');

        // must compare days and years separately
        $birthday = $month . $day;
        $birthyear = $year;

        $today = date('md');
        $year = date('Y');

        if ($birthyear < $year - $this->_min)
        {
            return true;
        }
        else if ($birthyear == $year - $this->_min && $birthday <= $today)
        {
            return true;
        }
        else
        {
            $this->_error(self::TOO_YOUNG);
            return false;
        }
    }
}

