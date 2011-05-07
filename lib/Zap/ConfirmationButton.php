<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/Button.php';

/**
 * A button widget with a javascript confirmation dialog
 *
 * This widget displays as an XHTML form submit button, so it should be used
 * within {@link SwatForm}.
 *
 * @package   Zap
 * @copyright 2004-2006 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @deprecated Confirmation functionality has been moved into SwatButton.
 * @see        SwatButton
 */
class Zap_ConfirmationButton extends Zap_Button
{
	// {{{ public function __construct()

	/**
	 * Creates a new confirmation button widget
	 *
	 * @see SwatWidget::__construct()
	 */
	public function __construct($id = null)
	{
		parent::__construct($id);

		$this->confirmation_message =
			Swat::_('Are you sure you wish to continue?');
	}

	// }}}
}


