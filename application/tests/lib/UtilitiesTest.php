<?php


require_once(dirname(dirname(__FILE__)) . '/TestHelper.php');


class UtilitiesTest extends PHPUnit_Framework_TestCase
{
    public function testEncryptDecrypt()
    {
        $treasure = "The quick brown fox jumped over the lazy dogs.";
        $salt = "I am the keymaster.";

        $iv = Utilities::newIv();
        $encrypted = Utilities::encrypt($salt, $iv, $treasure);
        $decrypted = Utilities::decrypt($salt, $iv, $encrypted);

        $this->assertEquals($treasure, $decrypted);
    }
}

