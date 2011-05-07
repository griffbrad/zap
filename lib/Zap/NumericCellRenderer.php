<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/CellRenderer.php';
require_once 'SwatI18N/SwatI18NLocale.php';

/**
 * A numeric cell renderer
 *
 * @package   Zap
 * @copyright 2006-2007 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_NumericCellRenderer extends Zap_CellRenderer
{
	// {{{ public properties

	/**
	 * Value can be either a float or an integer
	 *
	 * @var float
	 */
	public $value;

	/**
	 * Number of digits to display after the decimal point
	 *
	 * If null, the native number of digits displayed by PHP is used. The native
	 * number of digits could be a relatively large number of digits for uneven
	 * fractions.
	 *
	 * @var integer
	 */
	public $precision = null;

	// }}}
	// {{{ public function render()

	/**
	 * Renders the contents of this cell
	 *
	 * @see SwatCellRenderer::render()
	 */
	public function render()
	{
		if (!$this->visible)
			return;

		parent::render();

		echo $this->getDisplayValue();
	}

	// }}}
	// {{{ protected function getDisplayValue()

	public function getDisplayValue()
	{
		$value = $this->value;

		if (is_numeric($this->value)) {
			$locale = SwatI18NLocale::get();
			$value = $locale->formatNumber($this->value, $this->precision);
		}

		return $value;
	}

	// }}}
}


