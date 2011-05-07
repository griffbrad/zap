<?php

/**
 * A child of a {@link SwatNoteBook}
 *
 * @package   Zap
 * @copyright 2008 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @see       SwatNoteBook
 * @see       SwatNoteBookPage
 */
interface SwatNoteBookChild
{
	// {{{ public function getPages()

	/**
	 * Gets the notebook pages of this child
	 *
	 * @return array an array of {@link SwatNoteBookPage} objects.
	 *
	 * @see SwatNoteBookPage
	 */
	public function getPages();

	// }}}
}


