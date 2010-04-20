<?php

/**
 * All functions in this class should be static, and not use any
 * business-specific logic.
 */

class TextUtilities
{
    public static function escape($string)
    {
        return htmlentities($string, ENT_QUOTES, 'UTF-8');
    }

    public static function stem($string)
    {
        // remove everything but letters, and numbers
        // the 'u' modifier enables UTF-8
        $string = preg_replace('/[^\p{L}\p{N}\p{Zs}]+/u', '', $string);
        // remove spaces
        $string = preg_replace('/\s+/u', '', $string);
        // use multi-byte version
        $string = mb_strtolower($string, 'UTF-8');
        $string = trim($string);
        return $string;
    }

    public static function underscoreToCamelCase($string)
    {
        $inflector = new Zend_Filter_Inflector(':string');
        $inflector->setRules(array(':string' => 'Word_UnderscoreToCamelCase'));
        return $inflector->filter(array('string' => $string));
    }
}

