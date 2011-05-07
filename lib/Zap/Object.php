<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap.php';
require_once 'Zap/Error.php';

/**
 * The base object type
 *
 * @package   Zap
 * @copyright 2004-2006 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_Object
{
	// {{{ public function __toString()

	/**
	 * Gets this object as a string
	 *
	 * This is a magic method that is called by PHP when this object is used
	 * in string context. For example:
	 *
	 * <code>
	 * $my_object = new Zap_Message('Hello, World!');
	 * echo $my_object;
	 * </code>
	 *
	 * @return string this object represented as a string.
	 */
	public function __toString()
	{
		ob_start();
		Zap::printObject($this);
		return ob_get_clean();
	}

	// }}}
}


