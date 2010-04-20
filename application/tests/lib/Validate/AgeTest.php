<?php


require_once(dirname(dirname(dirname(__FILE__))) . '/TestHelper.php');


class Validate_AgeTest extends PHPUnit_Framework_TestCase
{
    public function testInvalidDate()
    {
        $validator = new Validate_Age();

        $this->assertFalse($validator->isValid('cow'));
        $error = $validator->getErrors();
        $this->assertEquals($error[0], Validate_Age::INVALID_DATE);
    }

    public function testTooYoung()
    {
        $validator = new Validate_Age(18);

        $input = date('Y-m-d', strtotime('-1 years'));
        $this->assertFalse($validator->isValid($input));
        $error = $validator->getErrors();
        $this->assertEquals($error[0], Validate_Age::TOO_YOUNG);
    }

    public function testValid()
    {
        $validator = new Validate_Age(18);

        $input = date('Y-m-d', strtotime('-20 years'));
        $this->assertTrue($validator->isValid($input));
    }
}

?>
