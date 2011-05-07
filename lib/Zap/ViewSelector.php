<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

/**
 * Interface for view selectors
 *
 * @package   Zap
 * @copyright 2007 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @see       SwatViewSelection
 * @see       SwatView
 */
interface SwatViewSelector
{
	// {{{ public function getId()

	/**
	 * Gets the identifier of this selector
	 *
	 * @return string the identifier of this selector.
	 */
	public function getId();

	// }}}
}


