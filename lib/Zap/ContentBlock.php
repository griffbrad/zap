<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/Control.php';
require_once 'Zap/String.php';

/**
 * A block of content in the widget tree
 *
 * @package   Zap
 * @copyright 2005-2006 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_ContentBlock extends Zap_Control
{
	// {{{ public properties

	/**
	 * User visible textual content of this widget
	 *
	 * @var string
	 */
	public $content = '';

	/**
	 * Optional content type
	 *
	 * Default text/plain, use text/xml for XHTML fragments.
	 *
	 * @var string
	 */
	public $content_type = 'text/plain';

	// }}}
	// {{{ public function display()

	/**
	 * Displays this content
	 *
	 * Merely performs an echo of the content.
	 */
	public function display()
	{
		if (!$this->visible)
			return;

		parent::display();

		if ($this->content_type === 'text/plain')
			echo SwatString::minimizeEntities($this->content);
		else
			echo $this->content;
	}

	// }}}
}


