<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/Flydown.php';
require_once 'Zap/HtmlTag.php';
require_once 'Zap/String.php';

/**
 * A radio list selection widget
 *
 * @package   Zap
 * @copyright 2005-2006 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_RadioList extends Zap_Flydown
{
	/**
	 * Used for displaying radio buttons
	 *
	 * @var SwatHtmlTag
	 */
	private $_inputTag;

	/**
	 * Used for displaying radio button labels
	 *
	 * @var SwatHtmlTag
	 */
	private $_labelTag;

	/**
	 * Creates a new radiolist
	 *
	 * @param string $id a non-visible unique id for this widget.
	 *
	 * @see SwatWidget::__construct()
	 */
	public function __construct($id = null)
	{
		parent::__construct($id);

		$this->_showBlank  = false;
		$this->_requiresId = true;

		$this->addStyleSheet(
			'packages/swat/styles/swat-radio-list.css',
			Zap::PACKAGE_ID
		);
	}

	/**
	 * Displays this radio list
	 */
	public function display()
	{
		$options = $this->getOptions();

		if (! $this->_visible || null === $options) {
			return;
		}

		Zap_Widget::display();

		// add a hidden field so we can check if this list was submitted on
		// the process step
		$this->getForm()->addHiddenField($this->_id . '_submitted', 1);

		if (1 === count($options)) {
			// get first and only element
			$this->_displaySingle(current($options));
			return;
		}

		$ulTag = new Zap_HtmlTag('ul');
		$ulTag->id    = $this->_id;
		$ulTag->class = $this->_getCSSClassString();
		$ulTag->open();

		$liTag = new Zap_HtmlTag('li');
		$count = 0;

		foreach ($options as $option) {

			// add option-specific CSS classes from option metadata
			$classes = $this->getOptionMetadata($option, 'classes');
			if (is_array($classes)) {
				$liTag->class = implode(' ', $classes);
			} elseif ($classes) {
				$liTag->class = strval($classes);
			} else {
				$liTag->removeAttribute('class');
			}

			$liTag->id = $this->_id . '_li_' . (string) $count;
			$liTag->open();
			$count++;

			if ($option instanceof Zap_FlydownDivider) {
				$this->displayDivider($option);
			} else {
				$this->_displayOption($option);
				$this->_displayOptionLabel($option);
			}

			$liTag->close();
		}

		$ulTag->close();
	}

	/**
	 * Processes the value of this radio list from user-submitted form data
	 *
	 * @return boolean true if the value was processed from form data
	 */
	protected function _processValue()
	{
		$form = $this->getForm();

		if (null === $form->getHiddenField($this->_id . '_submitted')) {
			return false;
		}

		$data = &$form->getFormData();
		$salt = $form->getSalt();

		if (isset($data[$this->_id])) {
			if ($this->_serializeValues) {
				$this->_value =
					Zap_String::signedUnserialize($data[$this->_id], $salt);
			} else {
				$this->_value = $data[$this->_id];
			}
		} else {
			$this->_value = null;
		}

		return true;
	}

	/**
	 * Displays a divider option in this radio list
	 *
	 * @param Zap_Option $option
	 */
	protected function _displayDivider(Zap_Option $option)
	{
		$spanTag = new Zap_HtmlTag('span');
		$spanTag->class = 'swat-radio-list-divider';

		if (null !== $option->getValue()) {
			$spanTag->id = $this->id . '_' . (string) $option->getValue();
		}

		$spanTag->setContent($option->getTitle());
		$spanTag->display();
	}

	/**
	 * Displays an option in the radio list
	 *
	 * @param Zap_Option $option
	 */
	protected function _displayOption(Zap_Option $option)
	{
		if (null === $this->_inputTag) {
			$this->_inputTag = new Zap_HtmlTag('input');
			$this->_inputTag->type = 'radio';
			$this->_inputTag->name = $this->_id;
		}

		if (! $this->isSensitive()) {
			$this->_inputTag->disabled = 'disabled';
		}

		if ($this->_serializeValues) {
			$salt = $this->getForm()->getSalt();
			$this->_inputTag->value =
				Zap_String::signedSerialize($option->getValue(), $salt);
		} else {
			$this->_inputTag->value = (string) $option->getValue();
		}

		$this->_inputTag->removeAttribute('checked');

		// TODO: come up with a better system to set ids. This may  not be
		// unique and may also not be valid XHTML
		$this->_inputTag->id = $this->_id . '_' . (string) $option->getValue();

		if ($this->_serializeValues) {
			if ($option->getValue() === $this->_value) {
				$this->_inputTag->checked = 'checked';
			}
		} else {
			if ((string) $option->getValue() === (string) $this->_value) {
				$this->_inputTag->checked = 'checked';
			}
		}

		$this->_inputTag->display();
	}

	/**
	 * Displays an option in the radio list
	 *
	 * @param SwatOption $option
	 */
	protected function _displayOptionLabel(Zap_Option $option)
	{
		if (null === $this->_labelTag) {
			$this->_labelTag = new Zap_HtmlTag('label');
			$this->_labelTag->class = 'swat-control';
		}

		$this->_labelTag->for = $this->_id . '_' . (string) $option->getValue();
		$this->_labelTag->setContent($option->getTitle(), $option->getContentType());
		$this->_labelTag->display();
	}

	/**
	 * Gets the array of CSS classes that are applied to this radio list
	 *
	 * @return array the array of CSS classes that are applied to this radio
	 *                list.
	 */
	protected function _getCSSClassNames()
	{
		$classes = array('swat-radio-list');
		$classes = array_merge($classes, parent::_getCSSClassNames());
		return $classes;
	}
}


