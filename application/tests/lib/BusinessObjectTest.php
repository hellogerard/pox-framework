<?php


require_once(dirname(dirname(__FILE__)) . '/TestHelper.php');


class Dog extends BusinessObject
{
    private $_dog;

    public function __construct($dog)
    {
        $this->_dog = $dog;
        parent::__construct();
    }

    public function getDog()
    {
        return $this->_dog;
    }
}

class Animals extends BusinessObject
{
    public function load()
    {
        return array('horse' => 'horse');
    }

    public function getCat()
    {
        return 'cat';
    }

    public function getDogs()
    {
        $this->_hint('Dog');
        return array('hound', 'beagle', 'setter', 'boxer');
    }

    public function setCow($value)
    {
        $this->_data['cow'] = $value;
    }
}


class BusinessObjectTest extends PHPUnit_Framework_TestCase
{
    protected $_object;

    public function setUp()
    {
        // get a new object factory and save it in registry
        $factory = new ObjectFactory();
        Zend_Registry::set('factory', $factory);

        $this->_object = new Animals();
    }

    public function testSet()
    {
        $this->_object->cow = 'cow';
        $this->assertEquals($this->_object->cow, 'cow');
    }

    public function testGetViaLoad()
    {
        $this->assertEquals($this->_object->horse, 'horse');
    }

    public function testGetViaSpecial()
    {
        $this->assertEquals($this->_object->cat, 'cat');
    }

    public function testGetCollection()
    {
        $dogs = $this->_object->dogs;
        $this->assertEquals($dogs[0]->dog, 'hound');
        $this->assertEquals($dogs[1]->dog, 'beagle');
        $this->assertEquals($dogs[2]->dog, 'setter');
        $this->assertEquals($dogs[3]->dog, 'boxer');
    }

    public function testGetPaging()
    {
        $this->_object->pageNo = 2;
        $this->_object->pageSize = 2;

        $dogs = $this->_object->dogs;
        $this->assertEquals($dogs[0]->dog, 'setter');
        $this->assertEquals($dogs[1]->dog, 'boxer');
        $this->assertEquals(count($dogs), 2);
    }
}

?>
