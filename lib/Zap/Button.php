<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/String.php';
require_once 'Zap/InputControl.php';
require_once 'Zap/HtmlTag.php';
require_once 'Swat/exceptions/SwatUndefinedStockTypeException.php';

/**
 * A button widget
 *
 * This widget displays as an XHTML form submit button, so it must be used
 * within {@link Zap_Form}.
 *
 * @package   Zap
 * @copyright 2004-2008 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_Button extends Zap_InputControl
{
	// {{{ public properties

	/**
	 * The visible text on this button
	 *
	 * @var string
	 */
	public $title = null;

	/**
	 * The stock id of this button
	 *
	 * Specifying a stock id before the {@link Zap_Button::init()} method is
	 * called causes this button to be initialized with a set of stock values.
	 *
	 * @var string
	 *
	 * @see Zap_ToolLink::setFromStock()
	 */
	public $stock_id = null;

	/**
	 * The access key for this button
	 *
	 * The access key is used for keyboard nagivation and screen readers.
	 *
	 * @var string
	 */
	public $access_key = null;

	/**
	 * The ordinal tab index position of the XHTML input tag, or null if the
	 * tab index should be automatically set by the browser
	 *
	 * @var integer
	 */
	public $tab_index = null;

	/**
	 * Whether or not to show a processing throbber when this button is
	 * clicked
	 *
	 * Showing a processing throbber is appropriate when this button is used
	 * to submit forms that can take a long time to process. By default, the
	 * processing throbber is not displayed.
	 *
	 * @var boolean
	 */
	public $show_processing_throbber = false;

	/**
	 * Optional content to display beside the processing throbber
	 *
	 * @var string
	 *
	 * @see Zap_Button::$show_processing_throbber
	 */
	public $processing_throbber_message = null;

	/**
	 * Optional confirmation message to display when this button is clicked
	 *
	 * If this message is specified, users will have to click through a
	 * JavaScript confirmation dialog to submit the form. If this is null, no
	 * confirmation is performed.
	 *
	 * @var string
	 */
	public $confirmation_message = null;

	// }}}
	// {{{ protected properties

	/**
	 * A CSS class set by the stock_id of this button
	 *
	 * @var string
	 */
	protected $stock_class = null;

	/**
	 * Clicked
	 *
	 * This is set to true after processing if this button was clicked.
	 * The form will also contain a refernce to the clicked button in the
	 * {@link Zap_Form::$button} class variable.
	 *
	 * @var boolean
	 */
	protected $clicked = false;

	// }}}
	// {{{ public function __construct()

	/**
	 * Creates a new button
	 *
	 * @param string $id a non-visible unique id for this widget.
	 *
	 * @see Zap_Widget::__construct()
	 */
	public function __construct($id = null)
	{
		parent::__construct($id);

		$yui = new Zap_YUI(array('dom', 'event', 'animation'));
		$this->html_head_entry_set->addEntrySet($yui->getHtmlHeadEntrySet());
		$this->addJavaScript(
			'packages/swat/javascript/swat-button.js',
			Zap::PACKAGE_ID);

		$this->requires_id = true;
	}

	// }}}
	// {{{ public function init()

	/**
	 * Initializes this widget
	 *
	 * Loads properties from stock if $stock_id is set, otherwise sets a
	 * default stock title.
	 *
	 * @see Zap_Widget::init()
	 */
	public function init()
	{
		parent::init();

		if ($this->stock_id === null)
			$this->setFromStock('submit', false);
		else
			$this->setFromStock($this->stock_id, false);
	}

	// }}}
	// {{{ public function display()

	/**
	 * Displays this button
	 *
	 * Outputs an XHTML input tag.
	 */
	public function display()
	{
		if (!$this->visible)
			return;

		parent::display();

		$input_tag = $this->getInputTag();
		$input_tag->display();

		if ($this->show_processing_throbber ||
			$this->confirmation_message !== null) {
			Zap::displayInlineJavaScript($this->getInlineJavaScript());
		}
	}

	// }}}
	// {{{ public function process()

	/**
	 * Does button processing
	 *
	 * Sets whether this button has been clicked and also updates the form
	 * this button belongs to with a reference to this button if this button
	 * submitted the form.
	 */
	public function process()
	{
		parent::process();

		$data = &$this->getForm()->getFormData();

		if (isset($data[$this->id])) {
			$this->clicked = true;
			$this->getForm()->button = $this;
		}
	}

	// }}}
	// {{{ public function hasBeenClicked()

	/**
	 * Returns whether this button has been clicked
	 *
	 * @return boolean whether this button has been clicked.
	 */
	public function hasBeenClicked()
	{
		return $this->clicked;
	}

	// }}}
	// {{{ public function setFromStock()

	/**
	 * Sets the values of this button to a stock type
	 *
	 * Valid stock type ids are:
	 *
	 * - submit
	 * - create
	 * - add
	 * - apply
	 * - delete
	 * - cancel
	 *
	 * @param string $stock_id the identifier of the stock type to use.
	 * @param boolean $overwrite_properties whether to overwrite properties if
	 *                                       they are already set.
	 *
	 * @throws Zap_UndefinedStockTypeException
	 */
	public function setFromStock($stock_id, $overwrite_properties = true)
	{
		switch ($stock_id) {
		case 'submit':
			$title = Zap::_('Submit');
			$class = 'swat-button-submit';
			break;

		case 'create':
			$title = Zap::_('Create');
			$class = 'swat-button-create';
			break;

		case 'add':
			$title = Zap::_('Add');
			$class = 'swat-button-add';
			break;

		case 'apply':
			$title = Zap::_('Apply');
			$class = 'swat-button-apply';
			break;

		case 'delete':
			$title = Zap::_('Delete');
			$class = 'swat-button-delete';
			break;

		case 'cancel':
			$title = Zap::_('Cancel');
			$class = 'swat-button-cancel';
			break;

		default:
			throw new Zap_UndefinedStockTypeException(
				"Stock type with id of '{$stock_id}' not found.",
				0, $stock_id);
		}

		if ($overwrite_properties || ($this->title === null))
			$this->title = $title;

		$this->stock_class = $class;
	}

	// }}}
	// {{{ protected function getInputTag()

	/**
	 * Get the HTML tag to display for this button
	 *
	 * Can be used by sub-classes to change the setup of the input tag.
	 *
	 * @return Zap_HtmlTag the HTML tag to display for this button.
	 */
	protected function getInputTag()
	{
		// We do not use a 'button' element because it is broken differently in
		// different versions of Internet Explorer

		$tag = new Zap_HtmlTag('input');

		$tag->type = 'submit';
		$tag->name = $this->id;
		$tag->id = $this->id;
		$tag->value = $this->title;
		$tag->class = $this->getCSSClassString();
		$tag->tabindex = $this->tab_index;
		$tag->accesskey = $this->access_key;

		if (!$this->isSensitive()) {
			$tag->disabled = 'disabled';
		}

		return $tag;
	}

	// }}}
	// {{{ protected function getCSSClassNames()

	/**
	 * Gets the array of CSS classes that are applied to this button
	 *
	 * @return array the array of CSS classes that are applied to this button.
	 */
	protected function getCSSClassNames()
	{
		$classes = array('swat-button');

		$form = $this->getFirstAncestor('Zap_Form');
		$primary = ($form !== null &&
			$form->getFirstDescendant('Zap_Button') === $this);

		if ($primary)
			$classes[] = 'swat-primary';

		if ($this->stock_class !== null)
			$classes[] = $this->stock_class;

		$classes = array_merge($classes, parent::getCSSClassNames());

		return $classes;
	}

	// }}}
	// {{{ protected function getJavaScriptClass()

	/**
	 * Gets the name of the JavaScript class to instantiate for this button
	 *
	 * Subclasses of this class may want to return a subclass of the default
	 * JavaScript button class.
	 *
	 * @return string the name of the JavaScript class to instantiate for this
	 *                 button. Defaults to 'Zap_Button'.
	 */
	protected function getJavaScriptClass()
	{
		return 'SwatButton';
	}

	// }}}
	// {{{ protected function getInlineJavaScript()

	/**
	 * Gets the inline JavaScript required for this control
	 *
	 * @return stirng the inline JavaScript required for this control.
	 */
	protected function getInlineJavaScript()
	{
		$show_processing_throbber = ($this->show_processing_throbber) ?
			'true' : 'false';

		$javascript = sprintf("var %s_obj = new %s('%s', %s);",
			$this->id,
			$this->getJavaScriptClass(),
			$this->id,
			$show_processing_throbber);

		if ($this->show_processing_throbber) {
			$javascript.= sprintf("\n%s_obj.setProcessingMessage(%s);",
				$this->id, Zap_String::quoteJavaScriptString(
					$this->processing_throbber_message));
		}

		if ($this->confirmation_message !== null)
			$javascript.= sprintf("\n%s_obj.setConfirmationMessage(%s);",
				$this->id, SwatString::quoteJavaScriptString(
					$this->confirmation_message));

		return $javascript;
	}

	// }}}
}


