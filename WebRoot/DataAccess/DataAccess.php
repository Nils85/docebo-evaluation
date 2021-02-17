<?php
namespace DataAccess;
use PDO;
use PDOStatement;

/**
 * Data Access Layer.
 * @author Vince <vincent.boursier@gmail.com>
 */
class DataAccess
{
	/**
	 * SQL scripts used to create tables if they don't exist.
	 * @var string[] Path from web root
	 */
	const SCRIPT_FILES = ['tables.sql', 'data.sql'];

	/**
	 * Connection options for PDO.
	 * @see https://www.php.net/manual/en/pdo.setattribute.php
	 * @var array
	 */
	const PDO_OPTIONS = [
		PDO::ATTR_PERSISTENT => true,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_STRINGIFY_FETCHES => false];

	private $pdo;

	/**
	 * Constructor.
	 * @param string $driver PDO driver name
	 * @param string $database Database name
	 * @param string $host Host name or server IP address
	 * @param string $user User name
	 * @param string $password User password
	 * @param int $port Database port
	 * @throws Exception If unknown driver
	 */
	public function __construct($driver, $database, $host = 'localhost', $user = 'root', $password = '', $port = 0)
	{
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

			default:
				throw new Exception('Unknown driver ' . $driver);
		}

		$this->pdo = new PDO($driver . ':' . $source, $user, $password, self::PDO_OPTIONS);
	}

	/**
	 * Prepare an SQL statement and automatically create tables in the database if necessary.
	 * @param string $sql
	 * @return PDOStatement
	 */
	private function tryPrepareQuery($sql)
	{
		$preparedStatement = $this->pdo->prepare($sql);

		if ($preparedStatement instanceof PDOStatement)
		{ return $preparedStatement; }

		foreach (self::SCRIPT_FILES as $path)
		{
			foreach (explode(';', file_get_contents(__DIR__ . '/' . $path)) as $query)
			{
				$query = trim($query);

				if ($query != '')
				{ $this->pdo->exec($query); }
			}
		}

		return $this->pdo->prepare($sql);
	}

	/**
	 * Select nodes at the top level.
	 * @param string $language
	 * @param int $pageNum
	 * @param int $pageSize
	 * @param string $searchKeyword Filter the result if provided (optional)
	 * @return array
	 */
	public function getRootNodes($language, $pageNum, $pageSize, $searchKeyword = '')
	{
		if ($pageSize == 0)  // It's useless but not an error
		{ return []; }

		return $this->selectRootNodes(1, $language, $pageNum, $pageSize, $searchKeyword);
	}

	/**
	 * Select node children.
	 * @param int $id Parent node ID
	 * @param string $language
	 * @param int $pageNum
	 * @param int $pageSize
	 * @param string $searchKeyword Filter the result if provided (optional)
	 * @return array
	 * @throws Exception If node ID doesn't exist
	 */
	public function getChildNodes($id, $language, $pageNum, $pageSize, $searchKeyword = '')
	{
		if ($pageSize == 0)  // It's useless but not an error
		{ return []; }

		$nodeInfo = $this->selectNodeID($id);

		if (empty($nodeInfo))
		{ throw new Exception('Invalid node id'); }

		return $this->selectChildNodes(
			$nodeInfo['iLeft'],
			$nodeInfo['iRight'],
			$nodeInfo['Level'] +1,
			$language,
			$pageNum,
			$pageSize,
			$searchKeyword);
	}

	/**
	 * Get one node by ID.
	 * @param int $id
	 * @param string $language
	 * @return array
	 */
	private function selectNodeID($id)
	{
		$query = $this->tryPrepareQuery(
			'select
				iLeft as iLeft,
				iRight as iRight,
				level as Level
			from node_tree where idNode = ?');

		$query->bindValue(1, $id, PDO::PARAM_INT);
		$query->execute();
		$row = $query->fetchAll(PDO::FETCH_ASSOC);

		return isset($row[0]) ? $row[0] : [];
	}

	/**
	 * Get nodes by level.
	 * @param int $level
	 * @param string $language
	 * @param int $pageNum
	 * @param int $pageSize
	 * @param string $keyword
	 * @return array
	 */
	private function selectRootNodes($level, $language, $pageNum, $pageSize, $keyword = '')
	{
		$sql =
			'select
				node_tree.idNode as NodeID,
				node_tree.level as Level,
				node_tree.iLeft as iLeft,
				node_tree.iRight as iRight,
				node_tree_names.nodeName as NodeName
			from node_tree
			inner join node_tree_names on node_tree.idNode = node_tree_names.idNode
			where node_tree_names.language = ? and node_tree.level between ? and ?';

		if ($keyword != '')
		{ $sql .= ' and node_tree_names.nodeName like ?'; }

		$sql .= ' order by node_tree.iLeft';

		$query = $this->tryPrepareQuery($sql);
		$query->bindValue(1, $language, PDO::PARAM_STR);
		$query->bindValue(2, $level, PDO::PARAM_INT);
		$query->bindValue(3, $level +1, PDO::PARAM_INT);

		if ($keyword != '')
		{ $query->bindValue(4, "%$keyword%", PDO::PARAM_STR); }

		$query->execute();

		$nodes = new NodeList($level);
		$offset = $pageNum * $pageSize;

		while ($row = $query->fetch(PDO::FETCH_ASSOC))
		{
			if ($row['Level'] == $level)
			{
				if (--$offset >= 0)
				{ continue; }

				if ($nodes->counter >= $pageSize)
				{ break; }
			}

			$nodes->add($row);
		}

		return $nodes->toArray();
	}

	/**
	 * Get nodes between boundaries.
	 * @param int $nodeLeft
	 * @param int $nodeRight
	 * @param int $level
	 * @param string $language
	 * @param int $pageNum
	 * @param int $pageSize
	 * @param string $keyword
	 * @return array
	 */
	private function selectChildNodes($nodeLeft, $nodeRight, $level, $language, $pageNum, $pageSize, $keyword = '')
	{
		$sql =
			'select
				node_tree.idNode as NodeID,
				node_tree.level as Level,
				node_tree.iLeft as iLeft,
				node_tree.iRight as iRight,
				node_tree_names.nodeName as NodeName
			from node_tree
			inner join node_tree_names on node_tree.idNode = node_tree_names.idNode
			where node_tree.level between ? and ?
				and node_tree.iLeft > ? and node_tree.iRight < ?
				and node_tree_names.language = ?';

		if ($keyword != '')
		{ $sql .= ' and node_tree_names.nodeName like ?'; }

		$sql .= ' order by node_tree.iLeft';

		$query = $this->pdo->prepare($sql);
		$query->bindValue(1, $level, PDO::PARAM_INT);
		$query->bindValue(2, $level +1, PDO::PARAM_INT);
		$query->bindValue(3, $nodeLeft, PDO::PARAM_INT);
		$query->bindValue(4, $nodeRight, PDO::PARAM_INT);
		$query->bindValue(5, $language, PDO::PARAM_STR);

		if ($keyword != '')
		{ $query->bindValue(6, "%$keyword%", PDO::PARAM_STR); }

		$query->execute();

		$nodes = new NodeList($level);
		$offset = $pageNum * $pageSize;

		while ($row = $query->fetch(PDO::FETCH_ASSOC))
		{
			if ($row['Level'] == $level)  // New node
			{
				if (--$offset >= 0)
				{ continue; }

				if ($nodes->counter >= $pageSize)
				{ break; }
			}

			$nodes->add($row);
		}

		return $nodes->toArray();
	}
}