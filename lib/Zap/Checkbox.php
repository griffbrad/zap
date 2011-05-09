<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/InputControl.php';
require_once 'Zap/HtmlTag.php';
require_once 'Zap/State.php';

/**
 * A checkbox entry widget
 *
 * @package   Zap
 * @copyright 2004-2006 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_Checkbox extends Zap_InputControl implements Zap_State
{
	/**
	 * Checkbox value
	 *
	 * The state of the widget.
	 *
	 * @var boolean
	 */
	protected $_value = false;

	/**
	 * Access key
	 *
	 * Access key for this checkbox input, for keyboard nagivation.
	 *
	 * @var string
	 */
	protected $_accessKey = null;

	/**
	 * Creates a new checkbox
	 *
	 * @param string $id a non-visible unique id for this widget.
	 *
	 * @see SwatWidget::__construct()
	 */
	public function __construct($id = null)
	{
		parent::__construct($id);
		$this->_requiresId = true;
	}

	/**
	 * Displays this checkbox
	 *
	 * Outputs an appropriate XHTML tag.
	 */
	public function display()
	{
		if (! $this->_visible) {
			return;
		}

		parent::display();

		$this->getForm()->addHiddenField($this->id.'_submitted', 1);

		$inputTag = new Zap_HtmlTag('input');
		$inputTag->type      = 'checkbox';
		$inputTag->class     = $this->_getCSSClassString();
		$inputTag->name      = $this->_id;
		$inputTag->id        = $this->_id;
		$inputTag->value     = '1';
		$inputTag->accesskey = $this->_accessKey;

		if ($this->_value) {
			$inputTag->checked = 'checked';
		}

		if (! $this->isSensitive()) {
			$inputTag->disabled = 'disabled';
		}

		$inputTag->display();
	}

	/**
	 * Processes this checkbox
	 *
	 * Sets the internal value of this checkbox based on submitted form data.
	 */
	public function process()
	{
		parent::process();

		$id = $this->_id . '_submitted';

		if (null === $this->getForm()->getHiddenField($id)) {
			return;
		}

		$data = &$this->getForm()->getFormData();
		$this->_value = array_key_exists($this->_id, $data);
	}

	/**
	 * Gets the current state of this checkbox
	 *
	 * @return boolean the current state of this checkbox.
	 *
	 * @see SwatState::getState()
	 */
	public function getState()
	{
		return $this->_value;
	}

	/**
	 * Sets the current state of this checkbox
	 *
	 * @param boolean $state the new state of this checkbox.
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
		return ($this->_visible) ? $this->_id : null;
	}

	/**
	 * Gets the array of CSS classes that are applied to this checkbox
	 *
	 * @return array the array of CSS classes that are applied to this
	 *                checkbox.
	 */
	protected function _getCSSClassNames()
	{
		$classes = array('swat-checkbox');
		$classes = array_merge($classes, parent::_getCSSClassNames());
		return $classes;
	}
}


