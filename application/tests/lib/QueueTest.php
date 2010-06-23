<?php


require_once(dirname(dirname(__FILE__)) . '/TestHelper.php');


class QueueTest extends PHPUnit_Framework_TestCase
{
    private $_db;

    public function setUp()
    {
        $this->_db = new Mock_Database;
        Zend_Registry::set('db', $this->_db);

        $sql = "CREATE TABLE queue (job_id INTEGER PRIMARY KEY, job, args, created_dt_tm);";

        $this->_db->query($sql);
    }

    public function tearDown()
    {
        $sql = "DROP TABLE queue;";

        $this->_db->query($sql);
    }

    public function testQueueWithArgs()
    {
        Queue::put('MyJob', '1');
        Queue::put('MyJob', '2');
        Queue::put('MyJob', '3');

        $sql =  "select count(*) from queue";
        $this->assertEquals(3, $this->_db->getOne($sql));

        $this->assertEquals(1, Queue::consume('MyJob'));
        $this->assertEquals(2, Queue::consume('MyJob'));
        $this->assertEquals(3, Queue::consume('MyJob'));

        $sql =  "select count(*) from queue";
        $this->assertEquals(0, $this->_db->getOne($sql));

        $this->assertNull(Queue::consume('MyJob'));
    }

    public function testQueueNoArgs()
    {
        Queue::put('MyJob');
        Queue::put('MyJob');
        Queue::put('MyJob');

        $sql =  "select count(*) from queue";
        $this->assertEquals(3, $this->_db->getOne($sql));

        $this->assertTrue(Queue::consume('MyJob'));
        $this->assertTrue(Queue::consume('MyJob'));
        $this->assertTrue(Queue::consume('MyJob'));

        $sql =  "select count(*) from queue";
        $this->assertEquals(0, $this->_db->getOne($sql));

        $this->assertNull(Queue::consume('MyJob'));
    }
}

