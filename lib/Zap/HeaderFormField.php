<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/FormField.php';

/**
 * A container to use around control widgets in a form
 *
 * Adds a label and space to output messages.
 *
 * @package   Zap
 * @copyright 2005-2006 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_HeaderFormField extends Zap_FormField
{
	// {{{ protected function getCSSClassNames()

	/**
	 * Gets the array of CSS classes that are applied to this header form field
	 *
	 * @return array the array of CSS classes that are applied to this header
	 *                form field.
	 */
	protected function getCSSClassNames()
	{
		$classes = parent::getCSSClassNames();
		array_unshift($classes, 'swat-header-form-field');
		return $classes;
	}

	// }}}
}


