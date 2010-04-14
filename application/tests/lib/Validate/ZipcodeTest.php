<?php


require_once(dirname(dirname(dirname(__FILE__))) . '/TestHelper.php');


class Validate_ZipcodeTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $sql = "CREATE TABLE zip_code (zip_code);
                INSERT INTO zip_code (zip_code) VALUES ('45202');";
        Zend_Registry::get('db')->query($sql);
    }

    public function tearDown()
    {
        $sql = "DROP TABLE zip_code";
        Zend_Registry::get('db')->query($sql);
    }

    public function testNotZipcode()
    {
        $validator = new Validate_Zipcode();

        $this->assertFalse($validator->isValid('abcde'));
        $error = $validator->getErrors();
        $this->assertEquals($error[0], Validate_Zipcode::NOT_ZIPCODE);
    }

    public function testNotFound()
    {
        $validator = new Validate_Zipcode();

        $this->assertFalse($validator->isValid('00999'));
        $error = $validator->getErrors();
        $this->assertEquals($error[0], Validate_Zipcode::NOT_FOUND);
    }

    public function testValid()
    {
        $validator = new Validate_Zipcode();

        $this->assertTrue($validator->isValid('45202'));
    }
}

?>
