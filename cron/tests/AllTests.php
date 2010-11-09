<?php

if (! defined('PHPUnit_MAIN_METHOD'))
{
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require_once(dirname(dirname(dirname(__FILE__))) . '/application/tests/TestHelper.php');


class AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();

        $suite->addTestSuite('CronParserTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main')
{
    AllTests::main();
}
