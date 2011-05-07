<?php

/**
 * Portions of this code are based on PEAR's Structures_Graph package, which is
 * licensed under the GNU Lesser General Public License version 2.1 or later.
 * The original license follows:
 *
 *   Copyright (c) 2003 Sérgio Gonçalves Carvalho.
 *
 *   Structures_Graph is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU Lesser General Public License as published
 *   by the Free Software Foundation; either version 2.1 of the License, or
 *   (at your option) any later version.
 *
 *   Structures_Graph is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Lesser General Public License for more details.
 *
 *   You should have received a copy of the GNU Lesser General Public License
 *   along with Structures_Graph; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *   02111-1307 USA
 *
 * @category  Tools
 * @package   Concentrate
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Concentrate_Graph
{
	protected $directed = true;

	protected $nodes = array();

	public function __construct($directed = true)
	{
		$this->directed = $directed;
	}

	public function isDirected()
	{
		return $this->directed;
	}

	public function addNode(Concentrate_Graph_Node $node)
	{
		$key = $node->getKey();
		if (!isset($this->nodes[$key])) {
			$this->nodes[$key] = $node;
			$node->setGraph($this);
		}
		return $this;
	}

	public function getNodes()
	{
		return array_values($this->nodes);
	}

	public function __clone()
	{
		$map = array();

		$newNodes = array();
		$oldNodes = $this->nodes;

		foreach ($oldNodes as $oldNode) {
			$newNode           = clone $oldNode;
			$newKey            = $newNode->getKey();
			$newNodes[$newKey] = $newNode;
			$oldKey            = $oldNode->getKey();
			$map[$oldKey]      = $newKey;
			$newNode->setGraph($this);
		}

		foreach ($oldNodes as $oldNode) {
			$oldKey  = $oldNode->getKey();
			$newKey  = $map[$oldKey];
			$newNode = $newNodes[$map[$oldKey]];
			foreach ($oldNode->getNeighbors() as $oldNeighbor) {
				$newNeighbor = $newNodes[$map[$oldNeighbor->getKey()]];
				$newNode->connectTo($newNeighbor);
			}
		}

		$this->nodes = $newNodes;
	}

	public function __toString()
	{
		$string = '';

		$len = 0;
		foreach ($this->nodes as $node) {
			$len = max(strlen($node), $len);
		}

		foreach ($this->nodes as $node) {
			$string .= str_pad($node, $len, ' ', STR_PAD_RIGHT);
			$string .= ' -> ';
			$string .= implode(', ', $node->getNeighbors());
			$string .= PHP_EOL;
		}

		return $string;
	}
}

?>
