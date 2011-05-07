<?php

require_once 'Concentrate/Graph.php';

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
class Concentrate_Graph_Node
{
	protected $data = null;

	protected $inArcs = array();

	protected $outArcs = array();

	protected $graph = null;

	public function __construct(Concentrate_Graph $graph, $data = null)
	{
		$this->setGraph($graph);
		$this->setData($data);
	}

	public function setGraph(Concentrate_Graph $graph)
	{
		if ($this->graph !== $graph) {
			$this->inArcs  = array();
			$this->outArcs = array();
			$this->graph   = $graph;
			$this->graph->addNode($this);
		}
	}

	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}

	public function getData()
	{
		return $this->data;
	}

	public function connectTo(Concentrate_Graph_Node $node)
	{
		$key = $node->getKey();

		if (!isset($this->outArcs[$key])) {
			$this->outArcs[$key]           = $node;
			$node->inArcs[$this->getKey()] = $this;
			if (!$this->graph->isDirected()) {
				$node->connectTo($this);
			}
		}
	}

	public function disconnectFrom(Concentrate_Graph_Node $node)
	{
		$key = $node->getKey();
		if (isset($this->outArcs[$key])) {
			unset($this->outArcs[$key]);
			unset($node->inArcs[$this->getKey()]);
			if (!$this->graph->isDirected()) {
				$node->disconnectFrom($this);
			}
		}
	}

	public function getNeighbors()
	{
		return array_values($this->outArcs);
	}

	public function isConnectedTo(Concentrate_Graph_Node $node)
	{
		$key = $node->getKey();
		return ($node->graph === $this->graph && isset($this->outArcs[$key]));
	}

	public function getOutDegree()
	{
		return count($this->outArcs);
	}

	public function getInDegree()
	{
		return count($this->inArcs);
	}

	public function getKey()
	{
		return spl_object_hash($this);
	}

	public function __toString()
	{
		return strval($this->getData());
	}
}

?>
