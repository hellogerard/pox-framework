<?php


require_once(dirname(dirname(dirname(__FILE__))) . '/TestHelper.php');


class Validate_ZipcodeTest extends PHPUnit_Framework_TestCase
{
    public function testNotZipcode()
    {
        $validator = new Validate_Zipcode();

        $this->assertFalse($validator->isValid('abcde'));
        $error = $validator->getErrors();
        $this->assertEquals($error[0], Validate_Zipcode::NOT_ZIPCODE);
    }

    public function testValid()
    {
        $validator = new Validate_Zipcode();

        $this->assertTrue($validator->isValid('45202'));
    }
}

?>
