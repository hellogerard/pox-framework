<?php


require_once(dirname(dirname(__FILE__)) . '/TestHelper.php');


class ErrorsTest extends PHPUnit_Framework_TestCase
{
    protected $_object;

    public function setUp()
    {
        $config = new Zend_Config_Ini(APP_ROOT . '/config/config.ini', 'development');
        Zend_Registry::set('config', $config);
        new Errors();
    }

    public function tearDown()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    public function testPHPError()
    {
        try {
            trigger_error("testing PHP error", E_USER_ERROR);
            $this->fail("An expected exception was not raised.");
        } catch (PHPException $e) {
            $this->assertEquals("testing PHP error", $e->getMessage());
            $this->assertTrue($e instanceof PHPException);
        }
    }

    public function testPEARError()
    {
        try {
            PEAR::raiseError("testing PEAR error");
            $this->fail("An expected exception was not raised.");
        } catch (PEAR_Exception $e) {
            $this->assertEquals("testing PEAR error", $e->getMessage());
            $this->assertTrue($e instanceof PEAR_Exception);
        }
    }
}

?>
