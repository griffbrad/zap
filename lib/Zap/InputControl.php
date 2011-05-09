<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/Control.php';
require_once 'Zap/FormField.php';

/**
 * Base class for controls that accept user input on forms.
 *
 * @package   Zap
 * @copyright 2005-2006 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
abstract class Zap_InputControl extends Zap_Control
{
	/**
	 * Whether this entry widget is required or not
	 *
	 * Must have a non-empty value when processed.
	 *
	 * @var boolean
	 */
	protected $_required = false;

	/**
	 * Whether to use the field title in validation messages
	 *
	 * @var boolean
	 */
	protected $_showFieldTitleInMessages = true;

	/**
	 * Initializes this widget
	 *
	 * Sets required property on the form field that contains this widget.
	 *
	 * @see SwatWidget::init()
	 */
	public function init()
	{
		parent::init();

		if ($this->_required && $this->_parent instanceof Zap_FormField) {
			$this->_parent->setRequired(true);
		}
	}

	public function setRequired($required)
	{
		$this->_required = $required;

		return $this;
	}

	/**
	 * Gets the form that this control is contained in
	 *
	 * You can also get the parent form with the
	 * {@link SwatUIObject::getFirstAncestor()} method but this method is more
	 * convenient and throws an exception .
	 *
	 * @return SwatForm the form this control is in.
	 *
	 * @throws SwatException
	 */
	public function getForm()
	{
		$form = $this->getFirstAncestor('SwatForm');

		if (null === $form) {
			$path   = get_class($this);
			$object = $this->_parent;

			while (null !== $object) {
				$path   = get_class($object) . '/' . $path;
				$object = $object->_parent;
			}

			throw new Zap_Exception("Input controls must reside inside a ".
				"Zap_Form widget. UI-Object path:\n" . $path);
		}

		return $form;
	}

	/**
	 * Gets a validation message for this control
	 *
	 * Can be used by sub-classes to change the validation messages.
	 *
	 * @param string $id the string identifier of the validation message.
	 *
	 * @return SwatMessage the validation message.
	 */
	protected function getValidationMessage($id)
	{
		switch ($id) {
		case 'required':
			$text = $this->show_field_title_in_messages ?
				Zap::_('The %s field is required.') :
				Zap::_('This field is required.');

			break;
		case 'too-long':
			$text = $this->show_field_title_in_messages ?
				Zap::_('The %%s field can be at most %s characters long.') :
				Zap::_('This field can be at most %s characters long.');

			break;
		default:
			$text = $this->show_field_title_in_messages ?
				Zap::_('There is a problem with the %s field.') :
				Zap::_('There is a problem with this field.');

			break;
		}

		$message = new SwatMessage($text, 'error');
		return $message;
	}
}


