<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/Entry.php';

/**
 * A single line search entry widget
 *
 * @package   Zap
 * @copyright 2007 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_SearchEntry extends Zap_Entry
{
	/**
	 * An XHTML name for this search entry widget
	 *
	 * The name is used as the XHTML form element name. This is useful for
	 * HTTP GET forms where the input name is displayed in the request URI.
	 * If the name is not specified, the widget id is used as the name.
	 *
	 * @var string
	 */
	public $_name;
	
	public function __construct($id = null)
	{
		parent::__construct($id);

		$this->_requiresId = true;

		$yui = new Zap_YUI(array('dom', 'event'));
		$this->_htmlHeadEntrySet->addEntrySet($yui->getHtmlHeadEntrySet());
		$this->addJavaScript(
			'packages/swat/javascript/swat-search-entry.js',
			Zap::PACKAGE_ID
		);

		$this->addStyleSheet(
			'packages/swat/styles/swat-search-entry.css',
			Zap::PACKAGE_ID
		);
	}

	/**
	 * Displays this search entry
	 *
	 * Outputs an appropriate XHTML tag and JavaScript.
	 */
	public function display()
	{
		if (! $this->_visible) {
			return;
		}

		parent::display();

		Zap::displayInlineJavaScript($this->_getInlineJavaScript());
	}

	/**
	 * Gets the inline JavaScript for this entry to function
	 *
	 * The inline JavaScript creates an instance of the
	 * SwatSearchEntry widget with the name $this->id_'obj'.
	 *
	 * @return srting the inline JavaScript required for this control to
	 *					function
	 */
	protected function _getInlineJavaScript()
	{
		return "var {$this->_id}_obj = new SwatSearchEntry('{$this->_id}');";
	}

	protected function getInputTag()
	{
		$tag = parent::getInputTag();

		if (null !== $this->_name) {
			$tag->_name = $this->_name;
		}

		return $tag;
	}

	/**
	 * Gets the array of CSS classes that are applied to this entry
	 *
	 * @return array the array of CSS classes that are applied to this
	 *                entry.
	 */
	protected function _getCSSClassNames()
	{
		$classes = array('swat-search-entry');
		$classes = array_merge($classes, parent::_getCSSClassNames());
		return $classes;
	}

	/**
	 * Gets the raw value entered by the user before processing
	 *
	 * @return string the raw value entred by the user before processing or
	 *                 null if no value was entered by the user.
	 */
	protected function getRawValue()
	{
		$value = null;

		if ($this->name === null) {
			$value = parent::getRawValue();
		} else {
			$data = &$this->getForm()->getFormData();
			$id = $this->name;
			if (isset($data[$id]) && $data[$id] != '') {
				$value = $data[$id];
			}
		}

		return $value;
	}

	/**
	 * Gets whether or not a value was submitted by the user for this entry
	 *
	 * Note: Users can submit a value of nothing and this method will return
	 * true. This method only returns false if no data was submitted at all.
	 *
	 * @return boolean true if a value was submitted by the user for this entry
	 *                  and false if no value was submitted by the user.
	 */
	protected function hasRawValue()
	{
		$hasValue = false;

		if (null === $this->name) {
			$hasValue = parent::hasRawValue();
		} else {
			$data = &$this->getForm()->getFormData();
			$id   = $this->_name;

			if (isset($data[$id])) {
				$hasValue = true;
			}
		}

		return $hasValue;
	}
}


