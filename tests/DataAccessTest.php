<?php
require_once 'WebRoot/DataAccess.php';

/**
 * Unit testing.
 * @author Vince <vincent.boursier@gmail.com>
 */
class DataAccessTest extends PHPUnit\Framework\TestCase
{
	public function testGetNodes()
	{
		$dao = new DataAccess('sqlite', ':memory:');
		$nodes = $dao->getNodes($id);
	}
}