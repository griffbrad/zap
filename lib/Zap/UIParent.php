<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

/**
 * Interface for widgets that are parents for other widgets.
 *
 * @package   Zap
 * @copyright 2005-2007 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
interface Zap_UIParent
{
	// {{{ public function addChild()

	/**
	 * Adds a child object to this parent object
	 *
	 * This method is used by {@link Zap_UI} when building a widget tree and
	 * does not need to be called elsewhere. To add a field to a field view,
	 * use {@link Zap_FieldView::appendField()}.
	 *
	 * @param Zap_Object $child the child object to add to this parent object.
	 */
	public function addChild(Zap_Object $child);

	// }}}
	// {{{ public function getDescendants()

	/**
	 * Gets descendant UI-objects
	 *
	 * Retrieves an ordered array of all UI-objects in the widget subtree below
	 * this object. Widgets are ordered in the array as they are found in
	 * a breadth-first traversal of the subtree.
	 *
	 * @param string $class_name optional class name. If set, only UI-objects
	 *                            that are instances of <i>$class_name</i> are
	 *                            returned.
	 *
	 * @return array the descendant UI-objects of this object. If descendant
	 *                objects have identifiers, the identifier is used as the
	 *                array key.
	 */
	public function getDescendants($class_name = null);

	// }}}
	// {{{ public function getFirstDescendant()

	/**
	 * Gets the first descendant UI-object of a specific class
	 *
	 * Retrieves the first descendant UI-object in the subtree that is a
	 * descendant of the specified class name. This uses a depth-first
	 * traversal to search the tree.
	 *
	 * @param string $class_name class name to look for.
	 *
	 * @return Zap_UIObject the first descendant UI-object or null if no
	 *                       matching descendant is found.
	 *
	 * @see Zap_Widget::getFirstAncestor()
	 */
	public function getFirstDescendant($class_name);

	// }}}
	// {{{ public function getDescendantStates()

	/**
	 * Gets descendant states
	 *
	 * Retrieves an array of states of all stateful UI-objects in the widget
	 * subtree below this UI-object.
	 *
	 * @return array an array of UI-object states with UI-object identifiers as
	 *                array keys.
	 */
	public function getDescendantStates();

	// }}}
	// {{{ public function setDescendantStates()

	/**
	 * Sets descendant states
	 *
	 * Sets states on all stateful UI-objects in the widget subtree below this
	 * UI-object.
	 *
	 * @param array $states an array of UI-object states with UI-object
	 *                       identifiers as array keys.
	 */
	public function setDescendantStates(array $states);

	// }}}
}


