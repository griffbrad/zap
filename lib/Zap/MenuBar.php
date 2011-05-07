<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/Menu.php';

/**
 * A menu bar control
 *
 * A menu bar is like a {@link SwatMenu} but it displays menu items
 * horizontally in a bar.
 *
 * @package   Zap
 * @copyright 2007 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 *
 * @see SwatMenu
 * @see SwatMenuItem
 */
class Zap_MenuBar extends Zap_Menu
{
	// {{{ protected function getJavaScriptClass()

	/**
	 * Gets the name of the JavaScript class to instantiate for this menu
	 *
	 * @return string the name of the JavaScript class to instantiate for this
	 *                 menu. For the SwatMenuBar widget, this is
	 *                 'YAHOO.widget.MenuBar'.
	 */
	protected function getJavaScriptClass()
	{
		return 'YAHOO.widget.MenuBar';
	}

	// }}}
	// {{{ protected function getMenuItemCSSClass()

	/**
	 * Gets the CSS class name to use for menu items in this menu
	 *
	 * @return string the CSS class name to use for menu items in this menu.
	 */
	protected function getMenuItemCSSClassName()
	{
		return 'yuimenubaritem';
	}

	// }}}
	// {{{ protected function getCSSClassNames()

	/**
	 * Gets the array of CSS classes that are applied to this menu bar
	 *
	 * @return array the array of CSS classes that are applied to this menu bar.
	 */
	protected function getCSSClassNames()
	{
		$classes = array('yuimenubar');
		$classes = array_merge($classes, parent::getCSSClassNames());
		return $classes;
	}

	// }}}
}


