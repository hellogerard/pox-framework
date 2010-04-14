<?php


require_once(dirname(dirname(dirname(__FILE__))) . '/TestHelper.php');


class Validate_AgeTest extends PHPUnit_Framework_TestCase
{
    public function testYearEmpty()
    {
        $validator = new Validate_Age();

        $input = array('', '03', '13');
        $this->assertFalse($validator->isValid($input));
        $error = $validator->getErrors();
        $this->assertEquals($error[0], Validate_Age::YEAR_EMPTY);
    }

    public function testMonthEmpty()
    {
        $validator = new Validate_Age();

        $input = array('2007', '', '13');
        $this->assertFalse($validator->isValid($input));
        $error = $validator->getErrors();
        $this->assertEquals($error[0], Validate_Age::MONTH_EMPTY);
    }

    public function testDayEmpty()
    {
        $validator = new Validate_Age();

        $input = array('2007', '03', '');
        $this->assertFalse($validator->isValid($input));
        $error = $validator->getErrors();
        $this->assertEquals($error[0], Validate_Age::DAY_EMPTY);
    }

    public function testTooYoung()
    {
        $validator = new Validate_Age(18);

        $input = array(date('Y', strtotime("-1 years")), '03', '13');
        $this->assertFalse($validator->isValid($input));
        $error = $validator->getErrors();
        $this->assertEquals($error[0], Validate_Age::TOO_YOUNG);
    }

    public function testException()
    {
        $validator = new Validate_Age();

        $input = array('03', '2007', '13');

        try {
            $validator->isValid($input);
            $this->fail("An expected exception was not raised.");
        } catch (Exception $e) {
            $this->assertEquals($e->getMessage(),
                "Fields must be passed in as 'year', 'month', 'day'");
        }
    }

    public function testValid()
    {
        $validator = new Validate_Age(18);

        $input = array(date('Y', strtotime("-20 years")), '03', '13');
        $this->assertTrue($validator->isValid($input));
    }
}

?>
