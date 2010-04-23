<?php


require_once(dirname(dirname(__FILE__)) . '/TestHelper.php');


class CachedObject extends BusinessObject
{
    private $_value;

    public function setValue($value)
    {
        $this->_value = $value;
    }

    public function getValue()
    {
        return $this->_value;
    }
}


class ObjectFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $_factory;

    public function setUp()
    {
        $this->_factory = new ObjectFactory();
    }

    public function testGet()
    {
        $object = $this->_factory->get('CachedObject');
        $this->assertTrue($object instanceof CachedObject);
    }

    public function testPut()
    {
        $object = $this->_factory->get('CachedObject');
        $this->assertNull($object->getValue());
        $object->setValue('12345');
        $this->_factory->put($object);

        $object2 = $this->_factory->get('CachedObject');
        $this->assertEquals($object2->getValue(), '12345');
    }

    public function testRm()
    {
        $object = $this->_factory->get('CachedObject');
        $this->_factory->rm($object);
        $object = $this->_factory->get('CachedObject');
        $this->assertNull($object);
    }
}

?>
