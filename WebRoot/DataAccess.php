<?php

/**
 * Data Access Object.
 * @author Vince <vincent.boursier@gmail.com>
 */
class DataAccess
{
	const SCRIPT_FILES = ['tables.sql', 'data.sql'];
	const PDO_OPTIONS = [
		PDO::ATTR_PERSISTENT => true,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false];

	private $pdo;

	/**
	 * Constructor.
	 * @param string $driver
	 * @param string $database
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param int $port
	 */
	public function __construct($driver, $database, $host = 'localhost', $user = 'root', $password = '', $port = 0)
	{
		$source = 'sqlite:SQLite.db';

		switch ($driver)
		{
			case 'sqlite':
				$source = $database;
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
				$source = $port > 0
					? 'Server=' . $host . ',' . $port . ';Database=' . $database
					: 'Server=' . $host . ';Database=' . $database;
				break;

			case 'oci':
				$source = $port > 0
					? 'dbname=//' . $host . ':' . $port . '/' . $database
					: 'dbname=//' . $host . '/' . $database;
				break;
		}

		$this->pdo = new PDO($driver . ':' . $source, $user, $password);
	}

	/**
	 * Automatically create tables in the databases.
	 */
	private function initializeDatabase()
	{
		foreach (self::SCRIPT_FILES as $path)
		{
			foreach (explode(';', file_get_contents($path)) as $query)
			{
				$query = trim($query);

				if ($query != '')
				{ $this->pdo->exec($query); }
			}
		}
	}

	/**
	 * Select node children.
	 * @param int $id Parent node ID
	 * @param string $language
	 * @param int $pageNum
	 * @param int $pageSize
	 * @param string $searchKeyword Filter the result if provided (optional)
	 * @return array
	 */
	public function getChildNodes($id, $language, $pageNum, $pageSize, $searchKeyword = '')
	{
		if ($pageSize == 0)
		{ return []; }

		$nodeInfo = $this->selectNode($id, $language);

		if (empty($nodeInfo['nodeName']))
		{ return []; }

		$nodes = [
			'node_id' => $id,
			'name' => $nodeInfo['nodeName'],
			'children_count' => 0,
			'nodes' => $this->selectNodes(
				$nodeInfo['iLeft'],
				$nodeInfo['iRight'],
				$nodeInfo['level'] +1,
				$language,
				$pageNum,
				$pageSize,
				$searchKeyword)
		];

		$nodes['children_count'] = count($nodes['nodes']);
		return $nodes;
	}

	/**
	 * Get one node.
	 * @param int $id
	 * @param string $language
	 * @return array
	 */
	private function selectNode($id, $language)
	{
		$sql =
			'select
				node_tree.level as level,
				node_tree.iLeft as iLeft,
				node_tree.iRight as iRight,
				node_tree_names.nodeName as nodeName
			from node_tree
			inner join node_tree_names on node_tree.idNode = node_tree_names.idNode
			where node_tree.idNode = ? and node_tree_names.language = ?';

		$query = $this->pdo->prepare($sql);

		if ($query == false)  // or null
		{
			$this->initializeDatabase();
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
	 * Possible optimization:
	 * All child levels could be fetch with only one SQL query without calling recursive function
	 * but this needs more complex code... So I don't know if it's worth it.
	 * @todo Store the prepared query in a static variable
	 * @param int $nodeLeft
	 * @param int $nodeRight
	 * @param int $level
	 * @param string $language
	 * @param int $pageNum
	 * @param int $pageSize
	 *
	 * @return array
	 */
	private function selectNodes($nodeLeft, $nodeRight, $level, $language, $pageNum, $pageSize, $keyword = '')
	{
		$nodes = [];
		$sql =
			'select
				node_tree.idNode as nodeID,
				node_tree.level as level,
				node_tree.iLeft as iLeft,
				node_tree.iRight as iRight,
				node_tree_names.nodeName as nodeName
			from node_tree
			inner join node_tree_names on node_tree.idNode = node_tree_names.idNode
			where node_tree.iLeft > ? and node_tree.iRight < ?
				and node_tree.level = ? and node_tree_names.language = ?';

		if ($keyword != '')
		{ $sql .= " and node_tree_names.nodeName like '%$keyword%'"; }

		$query = $this->pdo->prepare($sql);
		$query->bindValue(1, $nodeLeft, PDO::PARAM_INT);
		$query->bindValue(2, $nodeRight, PDO::PARAM_INT);
		$query->bindValue(3, $level, PDO::PARAM_INT);
		$query->bindValue(4, $language, PDO::PARAM_STR);
		$query->execute();

		while ($row = $query->fetch(PDO::FETCH_ASSOC))
		{
			$newNode = [
				'node_id' => $row['nodeID'],
				'name' => $row['nodeName'],
				'children_count' => 0,
				'nodes' => []
			];

			// If there is a gap between iLeft and iRight it's probably because children exist
			if ($row['iRight'] > $row['iLeft'] +1)
			{
				$newNode['nodes'] = $this->selectNodes($row['iLeft'], $row['iRight'], $row['level']+1, $language);
				$newNode['children_count'] = count($newNode['nodes']);
			}

			$nodes[] = $newNode;
		}

		return $nodes;
	}
}