<?php

require_once 'Zap/Control.php';
require_once 'Zap/YUI.php';
require_once 'Zap/HtmlTag.php';
require_once 'Zap/Date.php';

/**
 * Pop-up calendar widget
 *
 * This widget uses JavaScript to display a popup date selector. It is used
 * inside the {@link SwatDateEntry} widget but can be used by itself as well.
 *
 * @package   Zap
 * @copyright 2004-2010 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_Calendar extends Zap_Control
{
	/**
	 * Start date of the valid range (inclusive).
	 *
	 * @var SwatDate
	 */
	protected $_validRangeStart;

	/**
	 * End date of the valid range (exclusive).
	 *
	 * @var SwatDate
	 */
	protected $_validRangeEnd;

	/**
	 * Creates a new calendar
	 *
	 * @param string $id a non-visible unique id for this widget.
	 *
	 * @see SwatWidget::__construct()
	 */
	public function __construct($id = null)
	{
		parent::__construct($id);

		$this->_requiresId = true;

		$yui = new Zap_YUI(array('dom', 'container'));
		$this->_htmlHeadEntrySet->addEntrySet($yui->getHtmlHeadEntrySet());

		$this->addJavaScript(
			'packages/swat/javascript/swat-calendar.js',
			Zap::PACKAGE_ID
		);

		$this->addJavaScript(
			'packages/swat/javascript/swat-z-index-manager.js',
			Zap::PACKAGE_ID
		);

		$this->addStyleSheet(
			'packages/swat/styles/swat-calendar.css',
			Zap::PACKAGE_ID
		);
	}

	public function setValidRangeStart(Zap_Date $validRangeStart)
	{
		$this->_validRangeStart = $validRangeStart;

		return $this;
	}
	
	public function setValidRangeEnd(Zap_Date $validRangeEnd)
	{
		$this->_validRangeEnd = $validRangeEnd;

		return $this;
	}

	/**
	 * Displays this calendar widget
	 */
	public function display()
	{
		if (! $this->_visible) {
			return;
		}

		parent::display();

		$containerDivTag = new Zap_HtmlTag('div');
		$containerDivTag->id    = $this->_id;
		$containerDivTag->class = $this->_getCSSClassString();
		$containerDivTag->open();

		// toggle button content is displayed with JavaScript

		if ($this->_validRangeStart === null) {
			$today = new Zap_Date();
			$value = $today->formatLikeIntl('MM/dd/yyyy');
		} else {
			$value = $this->_validRangeStart->formatLikeIntl('MM/dd/yyyy');
		}

		$inputTag = new Zap_HtmlTag('input');
		$inputTag->type  = 'hidden';
		$inputTag->id    = $this->_id . '_value';
		$inputTag->name  = $this->_id . '_value';
		$inputTag->value = $value;
		$inputTag->display();

		$containerDivTag->close();

		Zap::displayInlineJavaScript($this->_getInlineJavaScript());
	}

	/**
	 * Gets the array of CSS classes that are applied to this calendar widget
	 *
	 * @return array the array of CSS classes that are applied to this calendar
	 *                widget.
	 */
	protected function _getCSSClassNames()
	{
		$classes = array('swat-calendar');
		$classes = array_merge($classes, parent::_getCSSClassNames());
		return $classes;
	}

	/**
	 * Gets inline calendar JavaScript
	 *
	 * Inline JavaScript is the majority of the calendar code.
	 */
	protected function _getInlineJavaScript()
	{
		static $shown = false;

		if (! $shown) {
			$javascript = $this->_getInlineJavaScriptTranslations();
			$shown = true;
		} else {
			$javascript = '';
		}

		if (isset($this->_validRangeStart)) {
			$startDate = $this->_validRangeStart->formatLikeIntl('MM/dd/yyyy');
		} else {
			$startDate = '';
		}

		if (isset($this->_validRangeEnd)) {
			// JavaScript calendar is inclusive, subtract one second from range
			$tmp = clone $this->_validRangeEnd;
			$tmp->subtractSeconds(1);
			$endDate = $tmp->formatLikeIntl('MM/dd/yyyy');
		} else {
			$endDate = '';
		}

		$javascript.= sprintf(
			"var %s_obj = new SwatCalendar('%s', '%s', '%s');",
			$this->_id,
			$this->_id,
			$startDate,
			$endDate
		);

		return $javascript;
	}

	/**
	 * Gets translatable string resources for the JavaScript object for
	 * this widget
	 *
	 * @return string translatable JavaScript string resources for this widget.
	 */
	protected function _getInlineJavaScriptTranslations()
	{
		/*
		 * This date is arbitrary and is just used for getting week and
		 * month names.
		 */
		$date = new Zap_Date();
		$date->setDay(1);
		$date->setMonth(1);
		$date->setYear(1995);

		// Get the names of weeks (locale-specific)
		$weekNames = array();
		for ($i = 1; $i < 8; $i++) {
			$weekNames[] = $date->formatLikeIntl('EEE');
			$date->setDay($i + 1);
		}
		$weekNames = "['".implode("', '", $weekNames)."']";

		// Get the names of months (locale-specific)
		$monthNames = array();
		for ($i = 1; $i < 13; $i++) {
			$monthNames[] = $date->formatLikeIntl('MMM');
			$date->setMonth($i + 1);
		}
		$monthNames = "['".implode("', '", $monthNames)."']";

		$prevAltText = Zap::_('Previous Month');
		$nextAltText = Zap::_('Next Month');
		$closeText   = Zap::_('Close');
		$nodateText  = Zap::_('No Date');
		$todayText   = Zap::_('Today');

		$openToggleText  = Zap::_('open calendar');
		$closeToggleText = Zap::_('close calendar');

		return
			"SwatCalendar.week_names = {$weekNames};\n".
			"SwatCalendar.month_names = {$monthNames};\n".
			"SwatCalendar.prev_alt_text = '{$prevAltText}';\n".
			"SwatCalendar.next_alt_text = '{$nextAltText}';\n".
			"SwatCalendar.close_text = '{$closeText}';\n".
			"SwatCalendar.nodate_text = '{$nodateText}';\n".
			"SwatCalendar.today_text = '{$todayText}';\n".
			"SwatCalendar.open_toggle_text = '{$openToggleText}';\n".
			"SwatCalendar.close_toggle_text = '{$closeToggleText}';\n";
	}
}


