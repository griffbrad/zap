<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/NumericCellRenderer.php';
require_once 'Zap/String.php';

/**
 * A percentage cell renderer
 *
 * @package   Zap
 * @copyright 2006 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_PercentageCellRenderer extends Zap_NumericCellRenderer
{
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

		SwatCellRenderer::render();

		$old_value = $this->value;
		$this->value = $this->value * 100;
		printf('%s%%', $this->getDisplayValue());
		$this->value = $old_value;
	}

	// }}}
}


