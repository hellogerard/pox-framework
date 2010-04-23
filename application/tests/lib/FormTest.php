<?php


require_once(dirname(dirname(__FILE__)) . '/TestHelper.php');


class FormTest extends PHPUnit_Framework_TestCase
{
    protected $_validators = array(
        'Username' => array(
            'Alpha',
            'messages' => 'Username is invalid'
        ),
        'Password' => array(
            'presence' => 'required',
            'messages' => 'Password is required'
        )
    );

    public function testGoodInput()
    {
        $input = array(
            'Username' => 'gerard',
            'Password' => 'password'
        );

        $form = new Form($this->_validators, $input);

        try {
            $form->getAlpha('Username');
            $this->fail("An expected exception was not raised.");
        } catch (Exception $e) {
            $this->assertEquals("Form::isValid() must be called first", $e->getMessage());
        }

        $this->assertTrue($form->isValid());
        $this->assertEquals($form->getAlpha('Username'), 'gerard');
    }

    public function testBadInput()
    {
        $input = array(
            'Username' => 'gerard1234'
        );

        $form = new Form($this->_validators, $input);

        $this->assertFalse($form->isValid());

        $expected = array(
            'UsernameErr' => 'Username is invalid',
            'PasswordErr' => 'Password is required'
        );

        $messages = $form->getMessages();
        $this->assertEquals($messages, $expected);
    }

    public function testHTMLEntities()
    {
        $input = array(
            'Username' => 'gerard',
            'Password' => 'pass&'
        );

        $form = new Form($this->_validators, $input);

        $this->assertTrue($form->isValid());
        $this->assertEquals($form->getHTMLEntities('Password'), 'pass&amp;');
    }
}

?>
