<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/Container.php';
require_once 'Zap/HtmlTag.php';

/**
 * Base class for containers that display an XHTML element
 *
 * @package   Zap
 * @copyright 2006 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_DisplayableContainer extends Zap_Container
{
	// {{{ public function display()

	/**
	 * Displays this container
	 */
	public function display()
	{
		if (!$this->visible)
			return;

		SwatWidget::display();

		$div = new SwatHtmlTag('div');
		$div->id = $this->id;
		$div->class = $this->getCSSClassString();

		$div->open();
		$this->displayChildren();
		$div->close();
	}

	// }}}
	// {{{ protected function getCSSClassNames()

	/**
	 * Gets the array of CSS classes that are applied to this displayable
	 * container
	 *
	 * @return array the array of CSS classes that are applied to this
	 *                displayable container.
	 */
	protected function getCSSClassNames()
	{
		$classes = array('swat-displayable-container');
		$classes = array_merge($classes, parent::getCSSClassNames());
		return $classes;
	}

	// }}}
}


