<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Swat/exceptions/SwatException.php';

/**
 * Thrown when an object does not implement a required interface
 *
 * @package   Swat
 * @copyright 2005-2006 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class SwatDoesNotImplementException extends SwatException
{
	// {{{ protected properties

	/**
	 * The object that does not implement a required interface
	 *
	 * @var mixed
	 */
	protected $object = null;

	// }}}
	// {{{ public function __construct()

	/**
	 * Creates a new does not implement exception
	 *
	 * @param string $message the message of the exception.
	 * @param integer $code the code of the exception.
	 * @param mixed $object the object that does not implement a required
	 *                       interface.
	 */
	public function __construct($message = null, $code = 0, $object = null)
	{
		parent::__construct($message, $code);
		$this->object = $object;
	}

	// }}}
	// {{{ public function getObject()

	/**
	 * Gets the object that does not implement a required interface
	 *
	 * @return mixed the object that does not implement a required interface.
	 */
	public function getObject()
	{
		return $this->object;
	}

	// }}}
}

?>
