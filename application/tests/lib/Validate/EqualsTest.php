<?php


require_once(dirname(dirname(dirname(__FILE__))) . '/TestHelper.php');


class Validate_EqualsTest extends PHPUnit_Framework_TestCase
{
    public function testNotEqual()
    {
        $validator = new Validate_Equals();

        $input = array(1, 2);
        $this->assertFalse($validator->isValid($input));
        $error = $validator->getErrors();
        $this->assertEquals($error[0], Validate_Equals::NOT_EQUAL);
    }

    public function testEqual()
    {
        $validator = new Validate_Equals();

        $input = array(1, 1);
        $this->assertTrue($validator->isValid($input));
    }
}

?>
