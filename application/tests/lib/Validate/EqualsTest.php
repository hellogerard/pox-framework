<?php


require_once(dirname(dirname(dirname(__FILE__))) . '/TestHelper.php');


class Validate_EqualsTest extends PHPUnit_Framework_TestCase
{
    public function testNotEqualValue()
    {
        $validator = new Validate_Equals(1);

        $this->assertFalse($validator->isValid(2));
        $error = $validator->getErrors();
        $this->assertEquals($error[0], Validate_Equals::NOT_EQUAL);
    }

    public function testNotEqualType()
    {
        $validator = new Validate_Equals(1);

        $this->assertFalse($validator->isValid("1"));
        $error = $validator->getErrors();
        $this->assertEquals($error[0], Validate_Equals::NOT_EQUAL);
    }

    public function testEqual()
    {
        $validator = new Validate_Equals(1);

        $this->assertTrue($validator->isValid(1));
    }
}

?>
