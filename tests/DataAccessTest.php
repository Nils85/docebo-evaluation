<?php
require_once 'WebRoot/DataAccess/DataAccess.php';
require_once 'WebRoot/DataAccess/NodeList.php';

/**
 * Unit testing.
 * @author Vince <vincent.boursier@gmail.com>
 */
class DataAccessTest extends PHPUnit\Framework\TestCase
{
	static $dao;

	public static function setUpBeforeClass()
	{
		self::$dao = new DataAccess('sqlite', ':memory:');
		//self::$dao = new DataAccess('sqlite', 'test.db');
	}

	public function testRootNodes()
	{
		$nodes = self::$dao->getRootNodes('english', 0, 100);
		$this->assertSame($nodes, ['node_id' => 5, 'name' => 'Docebo', 'children_count' => 8]);
	}

	public function testRootNodesPageSize0()
	{
		$nodes = self::$dao->getRootNodes('english', 0, 0);
		$this->assertSame($nodes, []);
	}

	public function testChildNodesPageSize0()
	{
		$nodes = self::$dao->getChildNodes(0, 'english', 0, 0);
		$this->assertSame($nodes, []);
	}

	public function testChildNodesKeyword()
	{
		$nodes = self::$dao->getChildNodes(5, 'english', 0, 100, 'Market');
		$this->assertSame($nodes, ['node_id' => 1, 'name' => 'Marketing', 'children_count' => 0]);
	}

	public function testException()
	{
		$nodes = self::$dao->getChildNodes(99, 'english', 0, 100);
		$this->expectException(Exception::class);
	}
}