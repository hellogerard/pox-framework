<?php

if (! defined('PHPUnit_MAIN_METHOD'))
{
    define('PHPUnit_MAIN_METHOD', 'Validate_AllTests::main');
}

require_once(dirname(dirname(dirname(__FILE__))) . '/TestHelper.php');


class Validate_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();

        $suite->addTestSuite('Validate_AgeTest');
        $suite->addTestSuite('Validate_EqualsTest');
        $suite->addTestSuite('Validate_ZipcodeTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Validate_AllTests::main')
{
    Validate_AllTests::main();
}
