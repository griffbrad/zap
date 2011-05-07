<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/Entry.php';

/**
 * An phone number entry widget
 *
 * @package   Zap
 * @copyright 2010 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_PhoneEntry extends Zap_Entry
{
	// {{{ protected function getInputTag()

	/**
	 * Get the input tag to display
	 *
	 * @return SwatHtmlTag the input tag to display.
	 */
	protected function getInputTag()
	{
		$tag = parent::getInputTag();
		$tag->type = 'tel';
		return $tag;
	}

	// }}}
	// {{{ protected function getCSSClassNames()

	/**
	 * Gets the array of CSS classes that are applied to this entry
	 *
	 * @return array the array of CSS classes that are applied to this
	 *                entry.
	 */
	protected function getCSSClassNames()
	{
		$classes = array('swat-phone-entry');
		$classes = array_merge($classes, parent::getCSSClassNames());
		return $classes;
	}

	// }}}
}


