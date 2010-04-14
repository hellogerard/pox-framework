<?php

/**
 * This class holds an HTML form, and is responsible for the following:
 *
 * - validation of input data
 * - generation of validation errors, if validation failed
 * - if validation succeeds
 *   - creates a secure "cage" around the input data
 *   - destroys the original data (e.g. $_POST)
 *   - ensures that all data accessed by application is filtered
 *
 * This class extends the Zend_Filter_Input class for validation.
 * see: http://framework.zend.com/manual/en/zend.filter.input.html
 *
 * This class delegates to the Inspekt library for filtering. Inspekt is an
 * immature product ATM, so it may be lacking in some filters. New filters can 
 * be added here simply be adding the method (see getHTMLEntities()).
 *
 * Inspekt home: http://inspekt.org
 * Inspekt filter methods:
 * http://funkatron.com/inspekt/user_docs/#List-of-test-and-filter-methods
 */

class Form extends Zend_Filter_Input
{
    // this will hold our input "cage"
    private $_clean = null;

    public function __construct($validators, $input)
    {
        // call the Zend_Filter_Input constructor
        parent::__construct($filters, $validators, $input);
    }

    /**
     * this function validates the form.  if simple calls 
     * Zend_Filter_Input::isValid(), but capture the result. if the result is 
     * success, it creates the Inspekt cage around the input before returning 
     * true.
     */

    public function isValid()
    {
        if (! parent::isValid())
        {
            return false;
        }
        else
        {
            $this->_clean = Inspekt_Cage::Factory($this->_validFields);
            return true;
        }
    }

    /**
     * There is a difference between input fields that are present, but have an 
     * empty value (e.g. an empty textbox), and input fields that are not 
     * present at all (e.g. an unchecked checkbox). For some reason, Zend_Filter_Input 
     * does not take the error message for a missing field from the rule 
     * itself, but from an options array. Let's override the function that 
     * returns this message to use a message from the rule, and not the options 
     * array.
     */

    protected function _getMissingMessage($rule, $field)
    {
        if ($this->_validatorRules[$rule][self::MESSAGES][0])
        {
            return $this->_validatorRules[$rule][self::MESSAGES][0];
        }
        else
        {
            return parent::_getMissingMessage($rule, $field);
        }
    }

    /**
     * This delegates any filter methods called on this form object to our 
     * Inspekt cage. If the cage is not created, throw an exception.
     */

    public function __call($name, $args)
    {
        if ($this->_clean !== null)
        {
            return call_user_func_array(array($this->_clean, $name), $args);
        }
        else
        {
            throw new Form_Exception(__CLASS__ . "::isValid() must be called first");
        }
    }

    /**
     * Inspekt does not have an htmlentities() filter yet. Let's create our own 
     * here.
     */

    public function getHTMLEntities($field)
    {
        if ($this->_clean !== null)
        {
            return htmlentities($this->_clean->getRaw($field), ENT_QUOTES, 'UTF-8');
        }
        else
        {
            throw new Form_Exception(__CLASS__ . "::isValid() must be called first");
        }
    }

    /**
     * Since we have destroyed the original input data, we must override these 
     * accessors to do nothing, or else Zend_Filter_Input will trigger an error.
     */

    public function getEscaped($var = null)
    {
    }

    public function getUnescaped($var = null)
    {
    }

    public function __get($var)
    {
    }

    /**
     * This overrides the Zend_Filter_Input::getMessages() function to return 
     * the messages in a format that can be instantly assigned to Smarty, 
     * following the convention that if the input field name is "$field", its 
     * error Smarty variable will be "$fieldErr".
     */

    public function getMessages()
    {
        $messages = parent::getMessages();

        if (empty($messages))
        {
            return array();
        }

        $return = array();

        foreach ($messages as $field => $messagesArray)
        {
            $return[$field . 'Err'] = reset($messagesArray);
        }

        return $return;
    }
}

