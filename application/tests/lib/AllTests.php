<?php

if (! defined('PHPUnit_MAIN_METHOD'))
{
    define('PHPUnit_MAIN_METHOD', 'Lib_AllTests::main');
}

require_once(dirname(dirname(__FILE__)) . '/TestHelper.php');


class Lib_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();

        $suite->addTest(Validate_AllTests::suite());

        $suite->addTestSuite('BusinessObjectTest');
        $suite->addTestSuite('ObjectFactoryTest');
        $suite->addTestSuite('ErrorsTest');
        $suite->addTestSuite('FormTest');
        $suite->addTestSuite('UtilitiesTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Lib_AllTests::main')
{
    Lib_AllTests::main();
}
