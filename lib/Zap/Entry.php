<?php

/**
 * A single line text entry widget
 *
 * @package   Zap
 * @copyright 2004-2009 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_Entry extends Zap_InputControl implements Zap_State
{
	/**
	 * Entry value
	 *
	 * Text content of the widget, or null.
	 *
	 * @var string
	 */
	protected $_value = null;

	/**
	 * Input size
	 *
	 * Size in characters of the HTML text form input, or null.
	 *
	 * @var integer
	 */
	protected $_size = 50;

	/**
	 * Maximum length
	 *
	 * Maximum number of allowable characters in HTML text form input, or null.
	 *
	 * @var integer
	 */
	protected $_maxlength = null;

	/**
	 * Access key
	 *
	 * Access key for this form input, for keyboard nagivation.
	 *
	 * @var string
	 */
	protected $_accessKey = null;

	/**
	 * Minimum length
	 *
	 * Minimum number of allowable characters in HTML text form input, or null.
	 *
	 * @var integer
	 */
	protected $_minlength = null;

	/**
	 * Tab index
	 *
	 * The ordinal tab index position of the XHTML input tag, or null.
	 * Values 1 or greater will affect the tab index of this widget. A value
	 * of 0 or null will use the position of the input tag in the XHTML
	 * character stream to determine tab order.
	 *
	 * @var integer
	 */
	protected $_tabIndex = null;

	/**
	 * Whether or not to use browser-based autocompletion on this entry
	 *
	 * Note: Be careful writing JavaScript when using this property as it
	 * changes the id of the XHTML element.
	 *
	 * @var boolean
	 */
	protected $_autocomplete = true;

	/**
	 * Read only?
	 *
	 * If read-only, the input will not allow editing its contents
	 *
	 * @var boolean
	 */
	protected $_readOnly = false;

	/**
	 * If autocomplete is turned off, this nonce is used to obfuscate the
	 * name of the XHTML input tag.
	 *
	 * @var string
	 */
	protected $_nonce = null;

	/**
	 * Whether or not to trim the value on process.
	 *
	 * If auto_trim is true, then we trim all values before doing other
	 * processing. When trimming, zero length strings are converted to null.
	 *
	 * @var boolean
	 */
	protected $_autoTrim = true;

	/**
	 * Displays this entry widget
	 *
	 * Outputs an appropriate XHTML tag.
	 */
	public function display()
	{
		if (! $this->_visible) {
			return;
		}

		parent::display();

		$inputTag = $this->getInputTag();
		$inputTag->display();

		if (! $this->_autocomplete) {
			$nonceTag = new Zap_HtmlTag('input');
			$nonceTag->type  = 'hidden';
			$nonceTag->name  = $this->_id . '_nonce';
			$nonceTag->value = $this->getNonce();
			$nonceTag->display();
		}
	}

	// }}}
	// {{{ public function process()

	/**
	 * Processes this entry widget
	 *
	 * If any validation type errors occur, an error message is attached to
	 * this entry widget.
	 */
	public function process()
	{
		parent::process();

		// if nothing was submitted by the user, shortcut return
		if (!$this->hasRawValue()) {
			$this->value = null;
			return;
		}

		$this->value = $this->getRawValue();

		if ($this->auto_trim) {
			$this->value = trim($this->value);
			if ($this->value === '')
				$this->value = null;
		}

		$len = ($this->value === null) ? 0 : strlen($this->value);

		if (! $this->_required && null === $this->value) {
			return;

		} elseif ($this->value === null) {
			$this->addMessage($this->getValidationMessage('required'));

		} elseif ($this->maxlength !== null && $len > $this->maxlength) {
			$message = $this->getValidationMessage('too-long');
			$message->primary_content =
				sprintf($message->primary_content, $this->maxlength);

			$this->addMessage($message);

		} elseif ($this->minlength !== null && $len < $this->minlength) {
			$message = $this->getValidationMessage('too-short');
			$message->primary_content =
				sprintf($message->primary_content, $this->minlength);

			$this->addMessage($message);
		}
	}

	// }}}
	// {{{ public function getState()

	/**
	 * Gets the current state of this entry widget
	 *
	 * @return string the current state of this entry widget.
	 *
	 * @see Zap_State::getState()
	 */
	public function getState()
	{
		return $this->value;
	}

	// }}}
	// {{{ public function setState()

	/**
	 * Sets the current state of this entry widget
	 *
	 * @param string $state the new state of this entry widget.
	 *
	 * @see Zap_State::setState()
	 */
	public function setState($state)
	{
		$this->value = $state;
	}

	// }}}

	/**
	 * Gets the id attribute of the XHTML element displayed by this widget
	 * that should receive focus
	 *
	 * @return string the id attribute of the XHTML element displayed by this
	 *                 widget that should receive focus or null if there is
	 *                 no such element.
	 *
	 * @see Zap_Widget::getFocusableHtmlId()
	 */
	public function getFocusableHtmlId()
	{
		if ($this->_visible) {
			return ($this->_autocomplete) ? $this->_id : $this->getNonce();
		} else {
			return null;
		}
	}

	/**
	 * Gets a validation message for this entry
	 *
	 * Can be used by sub-classes to change the validation messages.
	 *
	 * @param string $id the string identifier of the validation message.
	 *
	 * @return Zap_Message the validation message.
	 */
	protected function getValidationMessage($id)
	{
		switch ($id) {
		case 'too-short':
			$text = $this->show_field_title_in_messages ?
				Zap::_('The %%s must be at least %s characters long.') :
				Zap::_('This field must be at least %s characters long.');

			break;
		default:
			return parent::getValidationMessage($id);
		}

		$message = new Zap_Message($text, 'error');
		return $message;
	}

	// }}}

	/**
	 * Get the input tag to display
	 *
	 * Can be used by sub-classes to change the setup of the input tag.
	 *
	 * @return Zap_HtmlTag Input tag to display.
	 */
	protected function getInputTag()
	{
		$tag = new Zap_HtmlTag('input');
		$tag->type  = 'text';
		$tag->name  = ($this->_autocomplete) ? $this->_id : $this->getNonce();
		$tag->id    = ($this->_autocomplete) ? $this->_id : $this->getNonce();
		$tag->class = $this->_getCSSClassString();

		// event handlers to select on focus
		$tag->onmousedown = 'if(!this._focused){this._focus_click=true;}';
		$tag->onmouseup = 'if(this._focus_click){'.
			'this.select();this._focus_click=false;}';

		$tag->onfocus = 'this._focused=true;'.
			'if(!this._focus_click){ this.select();}';

		$tag->onblur = 'this._focused=false;this._focus_click=false;';

		if ($this->_readOnly) {
			$tag->readonly = 'readonly';
		}

		if (!$this->isSensitive())
			$tag->disabled = 'disabled';

		$value = $this->getDisplayValue($this->_value);

		// escape value for display because we actually want to show entities
		// for editing
		$value = htmlspecialchars($value);
		$tag->value = $value;

		$tag->size      = $this->_size;
		$tag->maxlength = $this->_maxlength;
		$tag->accesskey = $this->_accessKey;
		$tag->tabindex  = $this->_tabIndex;

		return $tag;
	}

	/**
	 * Formats a value to display
	 *
	 * Can be used by subclasses to change what is displayed.
	 *
	 * @param string $value the value to format for display.
	 *
	 * @return string the formatted value.
	 */
	protected function getDisplayValue($value)
	{
		return $value;
	}

	/**
	 * Gets the array of CSS classes that are applied to this entry widget
	 *
	 * @return array the array of CSS classes that are applied to this entry
	 *                widget.
	 */
	protected function _getCSSClassNames()
	{
		$classes = array('swat-entry');
		$classes = array_merge($classes, parent::_getCSSClassNames());
		return $classes;
	}

	// }}}
	// {{{ protected function getNonce()

	protected function getNonce()
	{
		if ($this->nonce === null)
			$this->nonce = 'n'.md5(rand());

		return $this->nonce;
	}

	// }}}
	// {{{ protected function getRawValue()

	/**
	 * Gets the raw value entered by the user before processing
	 *
	 * @return string the raw value entred by the user before processing or
	 *                 null if no value was entered by the user.
	 *
	 * @see Zap_Entry::hasRawValue()
	 */
	protected function getRawValue()
	{
		$value = null;

		$data = &$this->getForm()->getFormData();

		if ($this->autocomplete) {
			$id = $this->id;
			if (isset($data[$id]) && $data[$id] != '') {
				$value = $data[$id];
			}
		} else {
			if (isset($data[$this->id.'_nonce'])) {
				$id = $data[$this->id.'_nonce'];
				if (isset($data[$id]) && $data[$id] != '') {
					$value = $data[$id];
				}
			}
		}

		return $value;
	}

	// }}}
	// {{{ protected function hasRawValue()

	/**
	 * Gets whether or not a value was submitted by the user for this entry
	 *
	 * Note: Users can submit a value of nothing and this method will return
	 * true. This method only returns false if no data was submitted at all.
	 *
	 * @return boolean true if a value was submitted by the user for this entry
	 *                  and false if no value was submitted by the user.
	 *
	 * @see Zap_Entry::getRawValue()
	 */
	protected function hasRawValue()
	{
		$has_value = false;

		$data = &$this->getForm()->getFormData();

		if ($this->autocomplete) {
			$id = $this->id;
			if (isset($data[$id])) {
				$has_value = true;
			}
		} else {
			if (isset($data[$this->id.'_nonce'])) {
				$id = $data[$this->id.'_nonce'];
				if (isset($data[$id])) {
					$has_value = true;
				}
			}
		}

		return $has_value;
	}

	// }}}
}


