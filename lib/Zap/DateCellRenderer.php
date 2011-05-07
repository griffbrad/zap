<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/CellRenderer.php';
require_once 'Zap/Date.php';
require_once 'Zap/String.php';

/**
 * A text renderer.
 *
 * @package   Zap
 * @copyright 2005-2010 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_DateCellRenderer extends Zap_CellRenderer
{
	// {{{ public properties

	/**
	 * Date to render
	 *
	 * This may either be a {@link SwatDate} object, or may be an
	 * ISO-formatted date string that can be passed into the SwatDate
	 * constructor.
	 *
	 * @var string|SwatDate
	 */
	public $date = null;

	/**
	 * Format
	 *
	 * Either a {@link SwatDate} format mask, or class constant. Class
	 * constants are preferable for sites that require translation.
	 *
	 * @var mixed
	 */
	public $format = SwatDate::DF_DATE_TIME;

	/**
	 * Time zone format
	 *
	 * A time zone format class constant from SwatDate.
	 *
	 * @var integer
	 */
	public $time_zone_format = null;

	/**
	 * The time zone to render the date in
	 *
	 * The time zone may be specified either as a valid time zone identifier
	 * or as a HotDateTimeZone object. If the render time zone is null, no
	 * time zone conversion is performed.
	 *
	 * @var string|HotDateTimeZone
	 */
	public $display_time_zone = null;

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

		if ($this->date !== null) {

			if (is_string($this->date)) {
				$date = new SwatDate($this->date);
			} elseif ($this->date instanceof SwatDate) {
				// Time zone conversion mutates the original object so create
				// a new date for display.
				$date = clone $this->date;
			} else {
				throw new InvalidArgumentException(
					'The $date must be either a string or a SwatDate object.');
			}

			if ($this->display_time_zone instanceof HotDateTimeZone) {
				$date->convertTZ($this->display_time_zone);
			} elseif (is_string($this->display_time_zone)) {
				$date->convertTZById($this->display_time_zone);
			} elseif ($this->display_time_zone !== null) {
				throw new InvalidArgumentException(
					'The $display_time_zone must be either a string or a '.
					'HotDateTimeZone object.');
			}

			echo SwatString::minimizeEntities(
				$date->formatLikeIntl($this->format, $this->time_zone_format));
		}
	}

	// }}}
}


