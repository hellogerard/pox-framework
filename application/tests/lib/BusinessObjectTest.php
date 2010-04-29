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
        $this->cow = $value;
    }

    public function setHorseId($value)
    {
        $this->horse_id = 2 * $value;
    }
}


class BusinessObjectTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // get a new object factory and save it in registry
        $factory = new ObjectFactory();
        Zend_Registry::set('factory', $factory);
    }

    public function testSet()
    {
        $object = new Animals();
        $object->cow = 'cow';
        $this->assertEquals('cow', $object->cow);
    }

    public function testGetViaLoad()
    {
        $object = new Animals();
        $this->assertEquals('horse', $object->horse);
    }

    public function testGetViaSpecial()
    {
        $object = new Animals();
        $this->assertEquals('cat', $object->cat);
    }

    public function testGetCollection()
    {
        $object = new Animals();
        $dogs = $object->dogs;
        $this->assertEquals('hound', $dogs[0]->dog);
        $this->assertEquals('beagle', $dogs[1]->dog);
        $this->assertEquals('setter', $dogs[2]->dog);
        $this->assertEquals('boxer', $dogs[3]->dog);
    }

    public function testGetPaging()
    {
        $object = new Animals();
        $object->pageNo = 2;
        $object->pageSize = 2;

        $dogs = $object->dogs;
        $this->assertEquals('setter', $dogs[0]->dog);
        $this->assertEquals('boxer', $dogs[1]->dog);
        $this->assertEquals(2, count($dogs));
    }

    public function testIsset()
    {
        $object = new Animals();

        $this->assertTrue((bool) $object->cat);
        $this->assertTrue(isset($object->cat));
        $this->assertTrue(! empty($object->cat));

        $this->assertFalse((bool) $object->goat);
        $this->assertFalse(isset($object->goat));
        $this->assertFalse(! empty($object->goat));
    }

    public function testToCamelCase()
    {
        $object = new Animals();

        $object->horse_id = 100;

        $this->assertEquals(200, $object->horse_id);
    }

    public function testHydrate()
    {
        $object = new Animals;

        $object->hydrate();

        $this->assertEquals('horse', $object->horse);
    }

    public function testHydrateWithNewData()
    {
        $object = new Animals;

        $object->horse = 'goat';
        $object->hydrate();

        $this->assertEquals('goat', $object->horse);
    }
}

?>
