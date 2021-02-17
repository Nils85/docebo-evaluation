<?php
namespace DataAccess;

/**
 * List of nodes able to detect and count children nodes.
 * @property-read int $counter
 * @author Vince <vincent.boursier@gmail.com>
 */
class NodeList
{
	private $nodes = [];
	private $currentNodeID;
	private $currentNodeName;
	private $childCounter = 0;
	private $parentLevel;
	private $counter = 0;

	/**
	 * Get accessor.
	 * @param string $property Name
	 * @return mixed
	 * @throws Exception
	 */
	public function __get($property)
	{
		if ($property == 'counter')
		{ return $this->counter; }

		throw new Exception('Undefined property NodeList->' . $property);
	}

	/**
	 * Constructor.
	 * @param int $baseLevel
	 */
	public function __construct($baseLevel)
	{
		$this->parentLevel = $baseLevel;
	}

	public function add(array $values)
	{
		if ($values['Level'] == $this->parentLevel)  // New node
		{
			if ($this->counter++ > 0)
			{
				// Store the previous node
				$this->nodes[] = [
					'node_id' => $this->currentNodeID,
					'name' => $this->currentNodeName,
					'children_count' => $this->childCounter];
			}

			$this->currentNodeID = $values['NodeID'];
			$this->currentNodeName = $values['NodeName'];
			$this->childCounter = 0;
		}
		else  // Child node
		{
			++$this->childCounter;
		}
	}

	/**
	 * Get the current associative array.
	 * @return array
	 */
	public function toArray()
	{
		if ($this->counter > 0)
		{
			$this->nodes[] = [
				'node_id' => $this->currentNodeID,
				'name' => $this->currentNodeName,
				'children_count' => $this->childCounter];
		}

		return $this->nodes;
	}
}