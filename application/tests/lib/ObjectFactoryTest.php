<?php


require_once(dirname(dirname(__FILE__)) . '/TestHelper.php');


class CachedObject extends BusinessObject
{
    private $_id;
    private $_value;

    public function __construct($id = null)
    {
        $this->_id = $id;
    }

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
    protected $_cache;

    public function setUp()
    {
        $this->_factory = new ObjectFactory();
        $this->_cache = Zend_Registry::get('cache');
    }

    public function testGet()
    {
        $object = $this->_factory->get('CachedObject', 1);
        $this->assertTrue($object instanceof CachedObject);
    }

    public function testPut()
    {
        $object = $this->_factory->get('CachedObject', 1);
        $this->assertNull($object->getValue());
        $object->setValue('12345');
        $this->_factory->put($object);

        $object2 = $this->_factory->get('CachedObject', 1);
        $this->assertEquals('12345', $object2->getValue());
        $this->_factory = null; // calls destructor

        $key = 'CachedObject' . serialize(array(1));
        $result = $this->_cache->get($key);
        $result = unserialize($result);
        $this->assertEquals($object, $result);
    }

    public function testRm()
    {
        $object = $this->_factory->get('CachedObject', 1);
        $this->_factory->rm($object);

        $object = $this->_factory->get('CachedObject', 1);
        $this->assertNull($object);
        $this->_factory = null; // calls destructor

        $key = 'CachedObject' . serialize(array(1));
        $result = $this->_cache->get($key);
        $this->assertNull($result);
    }

    public function testNewInstance()
    {
        $object = $this->_factory->get('CachedObject');
        $this->assertNull($object->getValue());
        $object->setValue('12345');
        $this->_factory->put($object);

        $object2 = $this->_factory->get('CachedObject');
        $this->assertNull($object2->getValue());
    }

    public function testPutAndRm()
    {
        // test that cache remove takes precedence over cache put
        $object = $this->_factory->get('CachedObject', 1);
        $object->setValue('12345');

        $this->_factory->rm($object);
        $this->_factory->put($object);
        $this->_factory = null; // calls destructor

        $key = 'CachedObject' . serialize(array(1));
        $result = $this->_cache->get($key);
        $this->assertNull($result);
    }
}

?>
