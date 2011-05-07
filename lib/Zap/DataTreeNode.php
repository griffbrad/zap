<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/TreeNode.php';

/**
 * A tree node containing a value and a title
 *
 * @package   Zap
 * @copyright 2005-2006 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_DataTreeNode extends Zap_TreeNode
{
	// {{{ public properties

	/**
	 * The value of this node
	 *
	 * The value is used for processing. It is either a string or an integer.
	 *
	 * @var mixed
	 */
	public $value;

	/**
	 * The title of this node
	 *
	 * The title is used for display.
	 *
	 * @var string
	 */
	public $title;

	// }}}
	// {{{ public function __construct()

	/**
	 * Creates a new data node
	 *
	 * @param mixed $value the value of the node. It is either a string or an
	 *                      integer.
	 * @param string $title the title of the node.
	 */
	public function __construct($value, $title)
	{
		$this->value = $value;
		$this->title = $title;
	}

	// }}}
}


