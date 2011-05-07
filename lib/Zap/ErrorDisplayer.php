<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/Error.php';

/**
 * Abstract base class for displaying SwatError objects
 *
 * A custom error displayer can be used to change how uncaught errors are
 * displayed in an application. For example, you may want to display errors
 * in a separate file or display them using different XHTML markup.
 *
 * @package   Zap
 * @copyright 2006 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @see       SwatError::setDisplayer()
 */
abstract class Zap_ErrorDisplayer
{
	// {{{ public abstract function display()

	/**
	 * Displays a SwatError
	 *
	 * This is called by SwatError::process().
	 */
	public abstract function display(SwatError $e);

	// }}}
}


