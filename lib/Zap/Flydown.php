<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/OptionControl.php';
require_once 'Zap/HtmlTag.php';
require_once 'Zap/State.php';
require_once 'Zap/FlydownDivider.php';
require_once 'Zap/FlydownBlankOption.php';
require_once 'Zap/String.php';

/**
 * A flydown (aka combo-box) selection widget
 *
 * @package   Zap
 * @copyright 2004-2011 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_Flydown extends Zap_OptionControl implements Zap_State
{
	/**
	 * Flydown value
	 *
	 * The index value of the selected option, or null if no option is
	 * selected.
	 *
	 * @var string
	 */
	protected $_value = null;

	/**
	 * Show a blank option
	 *
	 * Whether or not to show a blank value at the top of the flydown.
	 *
	 * @var boolean
	 */
	protected $_showBlank = true;

	/**
	 * Blank title
	 *
	 * The user visible title to display in the blank field.
	 *
	 * @var string
	 */
	protected $_blankTitle = '';

	public function getShowBlank()
	{
		return $this->_showBlank;
	}

	/**
	 * Displays this flydown
	 *
	 * Displays this flydown as a XHTML select.
	 */
	public function display()
	{
		if (! $this->_visible) {
			return;
		}

		parent::display();

		$options  = $this->getOptions();
		$selected = false;

		if ($this->_showBlank) {
			$options = array_merge(array($this->_getBlankOption()), $options);
		}

		// only show a select if there is more than one option
		if (count($options) > 1) {
			$flydownValue = ($this->_serializeValues) ?
				$this->_value : (string) $this->_value;

			if ($this->_serializeValues) {
				$salt = $this->getForm()->getSalt();
			}

			$selectTag = new Zap_HtmlTag('select');
			$selectTag->name  = $this->_id;
			$selectTag->id    = $this->_id;
			$selectTag->class = $this->_getCSSClassString();

			if (!$this->isSensitive()) {
				$selectTag->disabled = 'disabled';
			}

			$optionTag = new Zap_HtmlTag('option');

			$selectTag->open();

			foreach ($options as $flydownOption) {
				if ($this->_serializeValues) {
					$optionTag->value = Zap_String::signedSerialize(
						$flydownOption->getValue(), 
						$salt
					);
				} else {
					$optionTag->value = (string) $flydownOption->getValue();
				}

				if ($flydownOption instanceof Zap_FlydownDivider) {
					$optionTag->disabled = 'disabled';
					$optionTag->class    = 'swat-flydown-option-divider';
				} elseif ($flydownOption instanceof Zap_FlydownBlankOption) {
					$optionTag->removeAttribute('disabled');
					$optionTag->class = 'swat-blank-option';
				} else {
					$optionTag->removeAttribute('disabled');
					$optionTag->removeAttribute('class');

					// add option-specific CSS classes from option metadata
					$classes = $this->getOptionMetadata(
						$flydownOption, 'classes');

					if (is_array($classes)) {
						$optionTag->class = implode(' ', $classes);
					} elseif ($classes) {
						$optionTag->class = strval($classes);
					}
				}

				$value = ($this->_serializeValues) ?
					$flydownOption->getValue() : (string) $flydownOption->getValue();

				if ($flydownValue === $value && ! $selected &&
					! ($flydownOption instanceof Zap_FlydownDivider)
				) {

					$optionTag->selected = 'selected';
					$selected = true;
				} else {
					$optionTag->removeAttribute('selected');
				}

				$optionTag->setContent($flydownOption->getTitle());

				$optionTag->display();
			}

			$selectTag->close();

		} elseif (count($options) == 1) {
			// get first and only element
			$this->_displaySingle(current($options));
		}
	}

	/**
	 * Figures out what option was selected
	 *
	 * Processes this widget and figures out what select element from this
	 * flydown was selected. Any validation errors cause an error message to
	 * be attached to this widget in this method.
	 */
	public function process()
	{
		parent::process();

		if (! $this->_processValue()) {
			return;
		}

		if ($this->_required && $this->isSensitive()) {
			// When values are not serialized, an empty string is treated as
			// null. As a result, you should not use a null value and an empty
			// string value in the same flydown except when using serialized
			// values.
			if (($this->_serializeValues && $this->_value === null) ||
				(!$this->_serializeValues && '' == $this->_value)
			) {

				$this->addMessage($this->getValidationMessage('required'));
			}
		}
	}

	/**
	 * Adds a divider to this flydown
	 *
	 * A divider is an unselectable flydown option.
	 *
	 * @param string $title the title of the divider. Defaults to two em
	 *                       dashes.
	 */
	public function addDivider($title = '——')
	{
		$this->options[] = new SwatFlydownDivider(null, $title);
	}

	/**
	 * Resets this flydown
	 *
	 * Resets this flydown to its default state. This method is useful to
	 * call from a display() method when form persistence is not desired.
	 */
	public function reset()
	{
		reset($this->_options);
		$this->_value = null;
	}

	/**
	 * Gets the current state of this flydown
	 *
	 * @return boolean the current state of this flydown.
	 *
	 * @see SwatState::getState()
	 */
	public function getState()
	{
		return $this->_value;
	}

	/**
	 * Sets the current state of this flydown
	 *
	 * @param boolean $state the new state of this flydown.
	 *
	 * @see SwatState::setState()
	 */
	public function setState($state)
	{
		$this->_value = $state;
	}

	/**
	 * Gets the id attribute of the XHTML element displayed by this widget
	 * that should receive focus
	 *
	 * @return string the id attribute of the XHTML element displayed by this
	 *                 widget that should receive focus or null if there is
	 *                 no such element.
	 *
	 * @see SwatWidget::getFocusableHtmlId()
	 */
	public function getFocusableHtmlId()
	{
		$focusableId = null;

		if ($this->_visible) {
			$count = count($this->getOptions());
			if ($this->_showBlank) {
				$count++;
			}

			if ($count > 1) {
				$focusableId = $this->_id;
			}
		}

		return $focusableId;
	}

	/**
	 * Processes the value of this flydown from user-submitted form data
	 *
	 * @return boolean true if the value was processed from form data
	 */
	protected function _processValue()
	{
		$form = $this->getForm();
		$data = &$form->getFormData();

		if (! isset($data[$this->_id])) {
			return false;
		}

		if ($this->_serializeValues) {
			$salt = $form->getSalt();
			$this->_value = Zap_String::signedUnserialize(
				$data[$this->_id], 
				$salt
			);
		} else {
			$this->_value = (string) $data[$this->_id];
		}

		return true;
	}

	/**
	 * Displays this flydown if there is only a single option
	 */
	protected function _displaySingle(Zap_Option $flydownOption)
	{
		$title = $flydownOption->getTitle();
		$value = $flydownOption->getValue();

		$hiddenTag = new Zap_HtmlTag('input');
		$hiddenTag->type = 'hidden';
		$hiddenTag->name = $this->_id;

		if ($this->_serializeValues) {
			$salt = $this->getForm()->getSalt();
			$hiddenTag->value = Zap_String::signedSerialize($value, $salt);
		} else {
			$hiddenTag->value = (string) $value;
		}

		$hiddenTag->display();

		$spanTag = new Zap_HtmlTag('span');
		$spanTag->class = 'swat-flydown-single';
		$spanTag->setContent($title, $flydownOption->getContentType());
		$spanTag->display();
	}

	/**
	 * Gets the the blank option for this flydown.
	 *
	 * @return SwatFlydownBlankOption the blank value option.
	 */
	protected function _getBlankOption()
	{
		return new Zap_FlydownBlankOption(null, $this->_blankTitle);
	}

	/**
	 * Gets the array of CSS classes that are applied to this flydown
	 *
	 * @return array the array of CSS classes that are applied to this flydown.
	 */
	protected function _getCSSClassNames()
	{
		$classes = array('swat-flydown');
		$classes = array_merge($classes, parent::_getCSSClassNames());
		return $classes;
	}
}


