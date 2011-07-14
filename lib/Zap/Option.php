<?php

require_once 'Zap/Object.php';

/**
 * A simple class for storing options used in various Swat controls
 *
 * @package   Zap
 * @copyright 2005-2006 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_Option extends Zap_Object
{
	/**
	 * Option title
	 *
	 * @var string
	 */
	protected $_title = null;

	/**
	 * Optional content type for title
	 *
	 * Default text/plain, use text/xml for XHTML fragments.
	 *
	 * @var string
	 */
	protected $_contentType = 'text/plain';

	/**
	 * Option value
	 *
	 * @var mixed
	 */
	protected $_value = null;

	/**
	 * Creates an option
	 *
	 * @param mixed $value the value of this option.
	 * @param string $title the user visible title of this option.
	 * @param string $content_type an optional content type of for this
	 *                              option's title. The content type defaults
	 *                              to 'text/plain'.
	 */
	public function __construct($value, $title, $contentType = 'text/plain')
	{
		$this->_value = $value;
		$this->_title = $title;
		$this->_contentType = $contentType;
	}

	public function getTitle()
	{
		return $this->_title;
	}

	public function getValue()
	{
		return $this->_value;
	}

	public function getContentType()
	{
		return $this->_contentType;
	}
}

