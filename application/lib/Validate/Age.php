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
    const YEAR_EMPTY = 'yearEmpty';
    const MONTH_EMPTY = 'monthEmpty';
    const DAY_EMPTY = 'dayEmpty';

    // error messages
    protected $_messageTemplates = array(
        self::TOO_YOUNG => "Age is less than %min% years",
        self::YEAR_EMPTY => "Year is required",
        self::MONTH_EMPTY => "Month is required",
        self::DAY_EMPTY => "Day is required"
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
        list($year, $month, $day) = array_values($value);

        // there are four ways to fail
        // 1) the birth year is empty
        // 2) the birth month is empty
        // 3) the birth day is empty
        // 4) the age is less than the minimum

        if (empty($year))
        {
            $this->_error(self::YEAR_EMPTY);
            return false;
        }

        if (empty($month))
        {
            $this->_error(self::MONTH_EMPTY);
            return false;
        }

        if (empty($day))
        {
            $this->_error(self::DAY_EMPTY);
            return false;
        }

        // if not a real date, either the params were passed in the wrong order, 
        // or bogus data was entered.
        if (! checkdate($month, $day, $year))
        {
            throw new Zend_Validate_Exception("Fields must be passed in as 'year', 'month', 'day'");
        }

        // check if birthday is $min yrs. ago or greater

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

