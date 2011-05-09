<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/RadioList.php';

/**
 * A radio list selection widget for a Yes/No option.
 *
 * @package   Zap
 * @copyright 2009 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_YesNoRadioList extends Zap_RadioList
{
	// {{{ constants

	const NO  = false;
	const YES = true;

	// }}}
	// {{{ public function __construct()

	/**
	 * Creates a new yes/no radio list
	 *
	 * Sets the options of this radio list to be yes and no.
	 *
	 * @param string $id a non-visible unique id for this widget.
	 *
	 * @see SwatWidget::__construct()
	 */
	public function __construct($id = null)
	{
		parent::__construct($id);
		$this->addOption(self::NO,  Zap::_('No'));
		$this->addOption(self::YES, Zap::_('Yes'));
	}

	// }}}
}


