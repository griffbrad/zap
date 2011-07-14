<?php

require_once 'Zap/Exception.php';

/**
 * Thrown when a stock type is used that is not defined
 *
 * @package   Zap
 * @copyright 2005-2006 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_Exception_UndefinedStockType extends Zap_Exception
{
	/**
	 * The name of the stock type that is undefined
	 *
	 * @var string
	 */
	protected $_stockType = null;

	/**
	 * Creates a new undefined stock type exception
	 *
	 * @param string $message the message of the exception.
	 * @param integer $code the code of the exception.
	 * @param string $stock_type the name of the stock type that is undefined.
	 */
	public function __construct($message = null, $code = 0,
		$stockType= null)
	{
		parent::__construct($message, $code);
		$this->_stockType = $stockType;
	}

	/**
	 * Gets the name of the stock type that is undefined
	 *
	 * @return string the name of the stock type that is undefined.
	 */
	public function getStockType()
	{
		return $this->_stockType;
	}
}

