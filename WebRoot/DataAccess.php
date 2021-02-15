<?php

/**
 * Data Access Object.
 * @author Vince <vincent.boursier@gmail.com>
 */
class DataAccess
{
	const PDO_OPTIONS = [
		PDO::ATTR_PERSISTENT => true,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false];

	private $pdo;

	/**
	 * Constructor.
	 * @param string $driver
	 * @param string $host
	 * @param int $port
	 * @param string $database
	 * @param string $user
	 * @param string $password
	 */
	public function __construct($driver, $host, $port, $database, $user, $password)
	{
		$source = 'sqlite:SQLite.db';

		switch ($driver)
		{
			case 'sqlite':
				$source = $database . '.db';
				break;

			case 'mysql':
			case 'pgsql':
				if (strpos($host, 'unix_socket=') === 0)
				{ $source = $host . ';dbname=' . $database; }
				elseif ($port > 0)
				{ $source = 'host=' . $host . ';port=' . $port . ';dbname=' . $database; }
				else
				{ $source = 'host=' . $host . ';dbname=' . $database; }
				break;

			case 'sqlsrv':
				if ($port > 0)
				{ $source = 'Server=' . $host . ',' . $port . ';Database=' . $database; }
				else
				{ $source = 'Server=' . $host . ';Database=' . $database; }
				break;

			case 'oci':
				if ($port > 0)
				{ $source = 'dbname=//' . $host . ':' . $port . '/' . $database; }
				else
				{ $source = 'dbname=//' . $host . '/' . $database; }
				break;
		}

		$this->pdo = new PDO($driver . ':' . $source, $user, $password, self::PDO_OPTIONS);
	}

	/**
	 * Select nodes that matches.
	 * @param int $id
	 * @param string $language
	 * @param int $pageNum
	 * @param int $pageSize
	 * @param string $searchKeyword
	 * @return array
	 */
	public function getNodes($id, $language, $pageNum, $pageSize, $searchKeyword)
	{
		if ($pageSize == 0)
		{ return []; }

		$nodeInfo = $this->selectParentNode($id, $language);

		if (empty($nodeInfo['nodeName']))
		{ return []; }

		$nodes = [
			'node_id' => $id,
			'name' => $nodeInfo['nodeName'],
			'children_count' => 0,
			'nodes' => $this->selectChildNodes(
				$nodeInfo['iLeft'],
				$nodeInfo['iRight'],
				$nodeInfo['level'] +1,
				$nodeInfo['language'])
		];

		$nodes['children_count'] = count($nodes['nodes']);
		return $nodes;
	}

	/**
	 * Get the root node.
	 * @param int $id
	 * @param string $language
	 * @return array
	 */
	private function selectParentNode($id, $language)
	{
		$sql = 'select
			node_tree.level as level,
			node_tree.iLeft as iLeft,
			node_tree.iRight as iRight,
			node_tree_names.nodeName as nodeName
			from node_tree
			inner join node_tree_names on node_tree.idNode=node_tree_names.idNode
			where node_tree_names.language=? and node_tree.idNode=?';

		$query = $this->pdo->prepare($sql);

		if ($query == false)  // or null
		{
			//TODO: create tables if they don't exist
			$query = $this->pdo->prepare($sql);
		}

		$query->bindValue(1, $id, PDO::PARAM_INT);
		$query->bindValue(2, $language, PDO::PARAM_STR);
		$query->execute();
		$row = $query->fetchAll(PDO::FETCH_ASSOC);

		return isset($row[0]) ? $row[0] : [];
	}

	/**
	 * Get child nodes.
	 * @param int $nodeLeft
	 * @param int $nodeRight
	 * @param int $level
	 * @param string $language
	 * @return array
	 */
	private function selectChildNodes($nodeLeft, $nodeRight, $level, $language)
	{
		$nodes = [];

		$query = $this->pdo->prepare('select
			node_tree.idNode as nodeID,
			node_tree.level as level,
			node_tree.iLeft as iLeft,
			node_tree.iRight as iRight,
			node_tree_names.nodeName as nodeName
			from node_tree
			inner join node_tree_names on node_tree.idNode=node_tree_names.idNode
			where node_tree.iLeft > ? and node_tree.iRight < ? and node_tree_names.language = ?
			order by node_tree.level');

		$query->bindValue(1, $nodeLeft, PDO::PARAM_INT);
		$query->bindValue(2, $nodeRight, PDO::PARAM_INT);
		$query->bindValue(3, $language, PDO::PARAM_STR);
		$query->execute();

		while ($row = $query->fetch(PDO::FETCH_ASSOC))
		{
			if ($row['level'] > $currentLevel)
			{
				//TODO: I probably need a recursive function... or an object to handle all node levels...
			}
			else
			{
				$nodes = [
					'node_id' => $row['nodeID'],
					'name' => $row['nodeName'],
					'children_count' => 0,
					'nodes' => []
				];
			}
		}

		return $nodes;
	}
}