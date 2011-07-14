<?php

require_once 'Zap/Exception.php';
require_once 'Zap/InputControl.php';
require_once 'Zap/Flydown.php';
require_once 'Zap/Date.php';
require_once 'Zap/State.php';
require_once 'Zap/YUI.php';
require_once 'Zap/HtmlTag.php';

/**
 * A date entry widget
 *
 * @package   Zap
 * @copyright 2004-2010 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_DateEntry extends Zap_InputControl implements Zap_State
{
	const YEAR     = 1;
	const MONTH    = 2;
	const DAY      = 4;
	const TIME     = 8;
	const CALENDAR = 16;

	/**
	 * Date of this date entry widget
	 *
	 * @var Zap_Date
	 */
	protected $_value = null;

	/**
	 * Required date parts
	 *
	 * Bitwise combination of {@link SwatDateEntry::YEAR},
	 * {@link SwatDateEntry::MONTH}, {@link SwatDateEntry::DAY} and
	 * {@link SwatDateEntry::TIME}.
	 *
	 * For example, to require the month and day to be entered in a date
	 * selector widget use the following:
	 *
	 * <code>
	 * $date->required_parts = SwatDateEntry::MONTH | SwatDateEntry::DAY;
	 * </code>
	 *
	 * @var integer
	 */
	protected $_requiredParts;

	/**
	 * Displayed date parts
	 *
	 * Bitwise combination of {@link SwatDateEntry::YEAR},
	 * {@link SwatDateEntry::MONTH}, {@link SwatDateEntry::DAY},
	 * {@link SwatDateEntry::TIME} and {@link SwatDateEntry::CALENDAR}.
	 *
	 * For example, to show a date selector widget with just the month and year
	 * use the following:
	 *
	 * <code>
	 * $date->display_parts = SwatDateEntry::YEAR | SwatDateEntry::MONTH;
	 * </code>
	 *
	 * @var integer
	 */
	protected $_displayParts;

	/**
	 * Start date of the valid range (inclusive)
	 *
	 * Defaults to 20 years in the past.
	 *
	 * @var Zap_Date
	 */
	protected $_validRangeStart;

	/**
	 * End date of the valid range (exclusive)
	 *
	 * Defaults to 20 years in the future.
	 *
	 * @var Zap_Date
	 */
	protected $_validRangeEnd;

	/**
	 * Whether the numeric month code is displayed in the month flydown
	 *
	 * This is useful for credit card date entry
	 *
	 * @var boolean
	 */
	protected $_showMonthNumber = false;

	/**
	 * Whether or not this time entry should auto-complete to the current date
	 *
	 * @var boolean
	 */
	protected $_useCurrentDate = true;

	/**
	 * Creates a new date entry widget
	 *
	 * Sets default required and display parts and sets default valid range
	 * for this date entry.
	 *
	 * @param string $id a non-visible unique id for this widget.
	 *
	 * @see SwatWidget::__construct()
	 */
	public function __construct($id = null)
	{
		parent::__construct($id);

		$this->_requiredParts = self::YEAR | self::MONTH | self::DAY;
		$this->_displayParts  = self::YEAR | self::MONTH |
		                        self::DAY | self::CALENDAR;

		$this->setValidRange(-20, 20);

		$this->_requiresId = true;

		$yui = new Zap_YUI(array('event'));
		$this->_htmlHeadEntrySet->addEntrySet($yui->getHtmlHeadEntrySet());

		$this->addJavaScript(
			'packages/swat/javascript/swat-date-entry.js',
			Zap::PACKAGE_ID
		);
	}

	/**
	 * Clones the valid date range of this date entry
	 */
	public function __clone()
	{
		$this->_validRangeStart = clone $this->_validRangeStart;
		$this->_validRangeEnd   = clone $this->_validRangeEnd;
	}

	/**
	 * Set the valid date range
	 *
	 * Convenience method to set the valid date range by year offsets.
	 *
	 * @param integer $start_offset offset from the current year in years used
	 *                               to set the starting year of the valid
	 *                               range.
	 * @param integer $end_offset offset from the current year in years used
	 *                             to set the ending year of the valid range.
	 */
	public function setValidRange($startOffset, $endOffset)
	{
		// Beginning of this year
		$date = new Zap_Date();
		$date->setMonth(1);
		$date->setDay(1);
		$date->setHour(0);
		$date->setMinute(0);
		$date->setSecond(0);
		$date->setTZById('UTC');

		$this->_validRangeStart = clone $date;
		$this->_validRangeEnd   = clone $date;

		$year = $date->getYear();
		$this->_validRangeStart->setYear($year + $startOffset);
		$this->_validRangeEnd->setYear($year + $endOffset + 1);
	}

	/**
	 * Displays this date entry
	 *
	 * Creates internal widgets if they do not exits then displays required
	 * JavaScript, then displays internal widgets.
	 */
	public function display()
	{
		if (! $this->_visible) {
			return;
		}

		parent::display();

		$divTag = new Zap_HtmlTag('div');
		$divTag->id    = $this->_id;
		$divTag->class = $this->_getCSSClassString();
		$divTag->open();

		echo '<span class="swat-date-entry-span">';

		foreach ($this->getDatePartOrder() as $datepart) {
			if ($datepart == 'd' && $this->_displayParts & self::DAY) {
				$dayFlydown = $this->_getCompositeWidget('day_flydown');

				if (null === $dayFlydown->getState() &&
					null !== $this->_value
				) {
					$dayFlydown->setState($this->_value->getDay());
				}

				$dayFlydown->display();
			} elseif ($datepart == 'm' && $this->_displayParts & self::MONTH) {
				$monthFlydown = $this->_getCompositeWidget('month_flydown');

				if ($monthFlydown->getState() === null &&
					$this->_value !== null
				) {
					$monthFlydown->setState($this->_value->getMonth());
				}

				$monthFlydown->display();
			} elseif ($datepart == 'y' && $this->_displayParts & self::YEAR) {
				$yearFlydown = $this->_getCompositeWidget('year_flydown');

				if ($yearFlydown->getState() === null &&
					$this->_value !== null
				) {
					$yearFlydown->setState($this->_value->getYear());
				}

				$yearFlydown->display();
			}
		}

		echo '</span>';

		if ($this->_displayParts & self::CALENDAR) {
			$calendar = $this->_getCompositeWidget('calendar');
			$calendar->display();
		}

		if ($this->_displayParts & self::TIME) {
			$timeEntry = $this->_getCompositeWidget('time_entry');

			// if we aren't using the current date then we won't use the
			// current time
			if (! $this->_useCurrentDate) {
				$timeEntry->_useCurrentTime = false;
			}

			echo ' ';

			if ($timeEntry->getState() === null 
				&& $this->_value !== null
			) {
				$timeEntry->setState($this->_value);
			}

			$timeEntry->display();
		}

		Zap::displayInlineJavaScript($this->_getInlineJavaScript());

		$divTag->close();
	}

	/**
	 * Processes this date entry
	 *
	 * Creates internal widgets if they do not exist and then assigns their
	 * values based on the date entered by the user. If the date is not valid,
	 * an error message is attached to this date entry.
	 */
	public function process()
	{
		parent::process();

		if (! $this->isVisible()) {
			return;
		}

		$year   = 0;
		$month  = 1;
		$day    = 1;
		$hour   = 0;
		$minute = 0;
		$second = 0;

		$allEmpty = true;
		$anyEmpty = false;

		if ($this->_displayParts & self::YEAR) {
			$yearFlydown = $this->_getCompositeWidget('year_flydown');
			$year = $yearFlydown->getState();
			if ($year === null) {
				if ($this->_requiredParts & self::YEAR) {
					$anyEmpty = true;
				} else {
					$year = date('Y');
				}
			} else {
				$allEmpty = false;
			}
		} else {
			$year = date('Y');
		}

		if ($this->_displayParts & self::MONTH) {
			$monthFlydown = $this->_getCompositeWidget('month_flydown');
			$month = $monthFlydown->getState();
			if ($month === null) {
				if ($this->_requiredParts & self::MONTH) {
					$anyEmpty = true;
				} else {
					$month = 1;
				}
			} else {
				$allEmpty = false;
			}
		}

		if ($this->_displayParts & self::DAY) {
			$dayFlydown = $this->_getCompositeWidget('day_flydown');
			$day = $dayFlydown->getState();
			if ($day === null) {
				if ($this->_requiredParts & self::DAY) {
					$anyEmpty = true;
				} else {
					$day = 1;
				}
			} else {
				$allEmpty = false;
			}
		}

		if ($this->_displayParts & self::TIME) {
			$timeEntry = $this->_getCompositeWidget('time_entry');

			if ($timeEntry->getState() === null) {
				if ($this->_requiredParts & self::TIME) {
					$anyEmpty = true;
				} else {
					$hour   = 0;
					$minute = 0;
					$second = 0;
				}
			} else {
				$hour     = $timeEntry->getState()->getHour();
				$minute   = $timeEntry->getState()->getMinute();
				$second   = $timeEntry->getState()->getSecond();
				$allEmpty = false;
			}
		}

		if ($allEmpty) {
			if ($this->_required && $this->isSensitive()) {
				$message = Zap::_('The %s field is required.');
				$this->addMessage(new Zap_Message($message, 'error'));
			}
			$this->_value = null;
		} elseif ($anyEmpty) {
			$message = Zap::_('The %s field is not a valid date.');
			$this->addMessage(new SwatMessage($message, 'error'));
			$this->_value = null;
		} else {
			try {
				$date = new Zap_Date();
				if ($date->setDate($year, $month, $day) === false) {
					throw new Zap_Exception('Invalid date.');
				}

				if ($date->setTime($hour, $minute, $second) === false) {
					throw new Zap_Exception('Invalid date.');
				}

				$date->setTZById('UTC');

				$this->_value = $date;
				$this->_validateRanges();
			} catch (SwatException $e) {
				$message = Zap::_('The %s field is not a valid date.');
				$this->addMessage(new Zap_Message($message, 'error'));
				$this->_value = null;
			}
		}
	}

	/**
	 * Gets the current state of this date entry widget
	 *
	 * @return boolean the current state of this date entry widget.
	 *
	 * @see SwatState::getState()
	 */
	public function getState()
	{
		if (null === $this->_value) {
			return null;
		} else {
			return $this->_value->getDate();
		}
	}

	/**
	 * Sets the current state of this date entry widget
	 *
	 * @param boolean $state the new state of this date entry widget.
	 *
	 * @see SwatState::setState()
	 */
	public function setState($state)
	{
		$this->_value = new Zap_Date($state);
	}

	/**
	 * Checks if the entered date is within the valid range
	 *
	 * @return boolean true if the entered date is within the valid range and
	 *                  false if the entered date is not within the valid range.
	 */
	public function isValid()
	{
		return ($this->isStartDateValid() && $this->isEndDateValid());
	}

	/**
	 * Gets the inline JavaScript required for this control
	 *
	 * @return string the inline JavaScript required for this control.
	 */
	protected function _getInlineJavaScript()
	{
		$useCurrentDate = ($this->_useCurrentDate) ? 'true' : 'false';

		$javascript = sprintf(
			"var %s_obj = new SwatDateEntry('%s', %s);",
			$this->_id, 
			$this->_id, 
			$useCurrentDate
		);

		if ($this->_displayParts & self::DAY) {
			$dayFlydown = $this->_getCompositeWidget('day_flydown');
			$lookupDays = array();

			foreach ($dayFlydown->getOptions() as $key => $option) {
				$lookupDays[] = sprintf(
					'%s: %s',
					$option->getValue(),
					($dayFlydown->getShowBlank()) ? $key + 1 : $key
				);
			}

			$javascript .= sprintf(
				"\n%s_obj.addLookupTable('day', {%s});",
				$this->_id, 
				implode(', ', $lookupDays)
			);
		}

		if ($this->_displayParts & self::MONTH) {
			$monthFlydown = $this->_getCompositeWidget('month_flydown');
			$lookupMonths = array();

			foreach ($monthFlydown->getOptions() as $key => $option) {
				$lookupMonths[] = sprintf(
					'%s: %s',
					$option->getValue(),
					($monthFlydown->getShowBlank()) ? $key + 1 : $key
				);
			}

			$javascript .= sprintf(
				"\n%s_obj.addLookupTable('month', {%s});",
				$this->_id, 
				implode(', ', $lookupMonths)
			);
		}

		if ($this->_displayParts & self::YEAR) {
			$yearFlydown = $this->_getCompositeWidget('year_flydown');
			$lookupYears = array();

			foreach ($yearFlydown->getOptions() as $key => $option) {
				$lookupYears[] = sprintf(
					'%s: %s',
					$option->getValue(),
					($yearFlydown->getShowBlank()) ? $key + 1 : $key
				);
			}

			$javascript .= sprintf( 
				"\n%s_obj.addLookupTable('year', {%s});",
				$this->_id, 
				implode(', ', $lookupYears)
			);
		}

		if ($this->_displayParts & self::TIME) {
			$javascript .= sprintf(
				"\n%s_obj.setTimeEntry(%s_time_entry_obj);",
				$this->_id, 
				$this->_id
			);
		}

		if ($this->_displayParts & self::CALENDAR) {
			$javascript .= sprintf(
				"\n%s_obj.setCalendar(%s_calendar_obj);",
				$this->_id, 
				$this->_id
			);
		}

		return $javascript;
	}

	/**
	 * Gets the array of CSS classes that are applied to this date entry widget
	 *
	 * @return array the array of CSS classes that are applied to this date
	 *                entry widget.
	 */
	protected function _getCSSClassNames()
	{
		$classes = array('swat-date-entry');
		$classes = array_merge($classes, parent::_getCSSClassNames());
		return $classes;
	}

	/**
	 * Checks if the entered date is valid with respect to the valid start
	 * date
	 *
	 * @return boolean true if the entered date is on or after the valid start
	 *                  date and false if the entered date is before the valid
	 *                  start date.
	 */
	protected function _isStartDateValid()
	{
		$this->_validRangeStart->setTZById('UTC');
		return (Zap_Date::compare(
			$this->_value, 
			$this->_validRangeStart, true) >= 0
		);
	}

	/**
	 * Checks if the entered date is valid with respect to the valid end date
	 *
	 * @return boolean true if the entered date is before the valid end date
	 *                  and false if the entered date is on or after the valid
	 *                  end date.
	 */
	protected function _isEndDateValid()
	{
		$this->_validRangeEnd->setTZById('UTC');
		return (Zap_Date::compare(
			$this->_value, 
			$this->_validRangeEnd, true) < 0
		);
	}

	/**
	 * Makes sure the date the user entered is within the valid range
	 *
	 * If the date is not within the valid range, this method attaches an
	 * error message to this date entry.
	 */
	protected function _validateRanges()
	{
		if (! $this->_isStartDateValid()) {
			$message = sprintf(
				Zap::_('The date you have entered is invalid. '.
					'It must be on or after %s.'),
				$this->_getFormattedDate($this->_validRangeStart)
			);

			$this->addMessage(new Zap_Message($message, 'error'));

		} elseif (! $this->isEndDateValid()) {
			$message = sprintf(
				Zap::_('The date you have entered is invalid. '.
					'It must be before %s.'),
				$this->_getFormattedDate($this->_validRangeEnd)
			);

			$this->addMessage(new Zap_Message($message, 'error'));
		}
	}

	/**
	 * Creates the composite widgets used by this date entry
	 *
	 * @see SwatWidget::createCompositeWidgets()
	 */
	protected function _createCompositeWidgets()
	{
		if ($this->_displayParts & self::YEAR) {
			$this->_addCompositeWidget(
				$this->_createYearFlydown(), 
				'year_flydown'
			);
		}

		if ($this->_displayParts & self::MONTH) {
			$this->_addCompositeWidget(
				$this->_createMonthFlydown(), 
				'month_flydown'
			);
		}

		if ($this->_displayParts & self::DAY) {
			$this->_addCompositeWidget(
				$this->_createDayFlydown(), 
				'day_flydown'
			);
		}

		if ($this->_displayParts & self::TIME) {
			$this->_addCompositeWidget(
				$this->_createTimeEntry(), 
				'time_entry'
			);
		}

		if ($this->_displayParts & self::CALENDAR) {
			$this->_addCompositeWidget(
				$this->_createCalendar(), 
				'calendar'
			);
		}
	}

	/**
	 * Creates the year flydown for this date entry
	 *
	 * @return SwatFlydown the year flydown for this date entry.
	 */
	private function _createYearFlydown()
	{
		$flydown   = new Zap_Flydown($this->_id.'_year');
		$startYear = $this->_validRangeStart->getYear();

		// Subtract a second from the end date. Since end date is exclusive,
		// this means that if the end date is the first of a year, we'll
		// prevent showing that year in the flydown.
		$rangeEnd = clone $this->_validRangeEnd;
		$rangeEnd->subtractSeconds(1);

		$endYear = $rangeEnd->getYear();

		for ($i = $startYear; $i <= $endYear; $i++) {
			$flydown->addOption($i, $i);
		}

		return $flydown;
	}

	/**
	 * Creates the month flydown for this date entry
	 *
	 * @return SwatFlydown the month flydown for this date entry.
	 */
	private function _createMonthFlydown()
	{
		$flydown = new Zap_Flydown($this->_id.'_month');

		// Subtract a second from the end date. This makes comparison correct,
		// and prevents displaying extra months.
		$rangeEnd = clone $this->_validRangeEnd;
		$rangeEnd->subtractSeconds(1);

		$startYear  = $this->_validRangeStart->getYear();
		$endYear    = $rangeEnd->getYear();
		$startMonth = $this->_validRangeStart->getMonth();
		$endMonth   = $rangeEnd->getMonth();

		if ($endYear == $startYear) {
			for ($i = $startMonth; $i <= $endMonth; $i++) {
				$flydown->addOption($i, $this->_getMonthOptionText($i));
			}
		} elseif (($endYear - $startYear) == 1) {
			for ($i = $start_month; $i <= 12; $i++) {
				$flydown->addOption($i, $this->_getMonthOptionText($i));
			}

			// if end month is december, we've already displayed above
			if ($endMonth < 12) {
				for ($i = 1; $i <= $endMonth; $i++) {
					$flydown->addOption($i, $this->_getMonthOptionText($i)); 
				}
			}
		} else {
			for ($i = 1; $i <= 12; $i++) {
				$flydown->addOption($i, $this->_getMonthOptionText($i));
			}
		}

		return $flydown;
	}

	/**
	 * Gets the title of a month flydown option
	 *
	 * @param integer $month the numeric identifier of the month.
	 *
	 * @return string the option text of the month.
	 */
	private function _getMonthOptionText($month)
	{
		$text = '';

		if ($this->_showMonthNumber) {
			$text .= str_pad($month, 2, '0', STR_PAD_LEFT).' - ';
		}

		$date = new Zap_Date('2010-' . $month . '-01');
		$text .= $date->formatLikeIntl('MMMM');

		return $text;
	}

	/**
	 * Creates the day flydown for this date entry
	 *
	 * @return SwatFlydown the day flydown for this date entry.
	 */
	private function _createDayFlydown()
	{
		$flydown = new Zap_Flydown($this->_id . '_day');

		// Subtract a second from the end date. This makes comparison correct,
		// and prevents displaying extra days.
		$rangeEnd = clone $this->_validRangeEnd;
		$rangeEnd->subtractSeconds(1);

		$startYear  = $this->_validRangeStart->getYear();
		$endYear    = $rangeEnd->getYear();
		$startMonth = $this->_validRangeStart->getMonth();
		$endMonth   = $rangeEnd->getMonth();
		$startDay   = $this->_validRangeStart->getDay();
		$endDay     = $rangeEnd->getDay();

		$endCheck = clone $this->_validRangeStart;
		$endCheck->addSeconds(2678400); // add 31 days

		if ($startYear == $endYear && $startMonth == $endMonth) {
			// Only days left in the month
			for ($i = $startDay; $i <= $endDay; $i++) {
				$flydown->addOption($i, $i);
			}

		} elseif (Zap_Date::compare($endCheck, $rangeEnd, true) != -1) {
			// extra days at the beginning of the next month allowed
			$daysInMonth = $this->_validRangeStart->getDaysInMonth();

			for ($i = $startDay; $i <= $daysInMonth; $i++) {
				$flydown->addOption($i, $i);
			}

			for ($i = 1; $i <= $endDay; $i++) {
				$flydown->addOption($i, $i);
			}

		} else {
			// all days are valid
			for ($i = 1; $i <= 31; $i++) {
				$flydown->addOption($i, $i);
			}
		}

		return $flydown;
	}

	/**
	 * Creates the time entry widget for this date entry
	 *
	 * @return SwatTimeEntry the time entry widget for this date entry.
	 */
	private function createTimeEntry()
	{
		require_once 'Zap/TimeEntry.php';
		$timeEntry = new Zap_TimeEntry($this->_id.'_time_entry');
		return $timeEntry;
	}

	/**
	 * Creates the calendar widget for this date entry
	 *
	 * @return SwatCalendar the calendar widget for this date entry.
	 */
	private function _createCalendar()
	{
		require_once 'Zap/Calendar.php';
		$calendar = new Zap_Calendar($this->_id . '_calendar');
		$calendar->setValidRangeStart($this->_validRangeStart)
			     ->setValidRangeEnd($this->_validRangeEnd);
		return $calendar;
	}

	/**
	 * Formats a date for this date entry
	 *
	 * Returns a date string formatted according to the properties of this
	 * date entry widget. This is used primarily for returning formatted
	 * valid start and valid end dates for user error messages.
	 *
	 * @param SwatDate $date the date object to format.
	 *
	 * @return string a date formatted according to the properties of this date
	 *                 entry.
	 */
	private function _getFormattedDate(Zap_Date $date)
	{
		// note: the display of the date is not locale specific as this
		// is quite difficult without a good i18n/l10n library

		$format = '';

		if ($this->display_parts & self::MONTH) {
			$format .= ' MMMM';
		}

		if ($this->display_parts & self::DAY) {
			$format .= ' d,';
		}

		if ($this->display_parts & self::YEAR) {
			$format .= ' yyyy';
		}

		if ($this->display_parts & self::TIME) {
			$format .= ' h:mm a';
		}

		$format = trim($format, ', ');

		return $date->formatLikeIntl($format);
	}

	/**
	 * Gets the order of date parts for the current locale
	 *
	 * Note: The technique used within this method does not work correcty for
	 * RTL languages that display month names, month abbreviations or weekday
	 * names. Since we're displaying months textually these locales may have
	 * date parts incorrectly ordered.
	 *
	 * @return array an array containg the values 'd', 'm' and 'y' in the
	 *                correct order for the current locale.
	 */
	private function getDatePartOrder()
	{
		$format = nl_langinfo(D_FMT);

		// expand short form format
		$format = str_replace('%D', '%m/%d/%y', $format);

		$day = $month = $year = null;

		$matches = array();
		if (preg_match('/(%d|%e)/', $format, $matches,
			PREG_OFFSET_CAPTURE) == 1)
			$day = $matches[0][1];

		$matches = array();
		if (preg_match('/(%[bB]|%m)/', $format, $matches,
			PREG_OFFSET_CAPTURE) == 1)
			$month = $matches[0][1];

		$matches = array();
		if (preg_match('/(%[Yy])/', $format, $matches,
			PREG_OFFSET_CAPTURE) == 1)
			$year = $matches[0][1];

		if ($day === null  || $month === null || $year === null) {
			// fallback to d-m-y if the locale format is unknown
			$order = array('d', 'm', 'y');
		} else {
			$order = array();
			$order[$day] = 'd';
			$order[$month] = 'm';
			$order[$year] = 'y';
			ksort($order);
		}

		return $order;
	}
}


