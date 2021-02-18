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
		self::$dao = new DataAccess\DataAccess('sqlite', ':memory:');  // or 'test.db'
		//self::$dao = new DataAccess\DataAccess('mysql', 'Docebo', '127.0.0.1', 'root', '');
	}

	public function testRootNode()
	{
		$nodes = self::$dao->getRootNodes('english', 0, 100);
		$this->assertEquals($nodes, [['node_id' => 5, 'name' => 'Docebo', 'children_count' => 8]]);
	}

	public function testPageSize0()
	{
		$nodes = self::$dao->getRootNodes('english', 0, 0);
		$this->assertEmpty($nodes);

		$nodes = self::$dao->getChildNodes(1, 'english', 0, 0);
		$this->assertEmpty($nodes);
	}

	public function testKeywordSearch()
	{
		$nodes = self::$dao->getChildNodes(5, 'english', 0, 100, 'account');
		$this->assertEquals($nodes,
			[
				['node_id' => 4, 'name' => 'Customer Account', 'children_count' => 0],
				['node_id' => 6, 'name' => 'Accounting', 'children_count' => 0]
			]
		);
	}

	public function testException()
	{
		$this->expectException(Exception::class);
		self::$dao->getChildNodes(99, 'english', 0, 100);
	}
}