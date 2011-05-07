<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/Flydown.php';

/**
 * A flydown (aka combo-box) selection widget for a Yes/No option.
 *
 * @package   Zap
 * @copyright 2005-2007 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_YesNoFlydown extends Zap_Flydown
{
	// {{{ constants

	const NO  = false;
	const YES = true;

	// }}}
	// {{{ public function __construct()

	/**
	 * Creates a new yes/no flydown
	 *
	 * Sets the options of this flydown to be yes and no.
	 *
	 * @param string $id a non-visible unique id for this widget.
	 *
	 * @see SwatWidget::__construct()
	 */
	public function __construct($id = null)
	{
		parent::__construct($id);
		$this->addOption(self::NO,  Swat::_('No'));
		$this->addOption(self::YES, Swat::_('Yes'));
	}

	// }}}
}


