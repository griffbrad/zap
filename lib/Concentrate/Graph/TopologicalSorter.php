<?php

require_once 'Concentrate/Exception.php';
require_once 'Concentrate/Graph.php';

/**
 * Topological sorting algorithm used is the Kahn Algorithm taken from
 * {@link http://en.wikipedia.org/wiki/Topological_sorting}.
 *
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
class Concentrate_Graph_TopologicalSorter
{
	public function sort(Concentrate_Graph $graph)
	{
		$graph = clone $graph;

		$sorted = array();

		$nodes = $this->getZeroInDegreeNodes($graph);
		while (count($nodes) > 0) {
			$node = array_pop($nodes);
			array_unshift($sorted, $node);
			foreach ($node->getNeighbors() as $neighborNode) {
				$node->disconnectFrom($neighborNode);
				if ($neighborNode->getInDegree() === 0) {
					$nodes[] = $neighborNode;
				}
			}
		}

		if (count($this->getOutDegreeNodes($graph)) > 0) {
			throw new Concentrate_CyclicDependencyException(
				'Could not get topological order of graph because one or '
				. 'more cycles were detected.'
			);
		}

		return $sorted;
	}

	protected function getZeroInDegreeNodes(Concentrate_Graph $graph)
	{
		$nodes = array();

		foreach ($graph->getNodes() as $node) {
			if ($node->getInDegree() === 0) {
				$nodes[] = $node;
			}
		}

		return $nodes;
	}

	protected function getOutDegreeNodes(Concentrate_Graph $graph)
	{
		$nodes = array();

		foreach ($graph->getNodes() as $node) {
			if ($node->getOutDegree() > 0) {
				$nodes[] = $node;
			}
		}

		return $nodes;
	}
}

?>
