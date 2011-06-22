<?php

require_once 'Zap/String.php';
require_once 'Zap/InputControl.php';
require_once 'Zap/HtmlTag.php';
require_once 'Zap/Exception/UndefinedStockType.php';

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
    /**
     * The visible text on this button
     *
     * @var string
     */
    protected $_title = null;

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
    protected $_stockId = null;

    /**
     * The access key for this button
     *
     * The access key is used for keyboard nagivation and screen readers.
     *
     * @var string
     */
    protected $_accessKey = null;

    /**
     * The ordinal tab index position of the XHTML input tag, or null if the
     * tab index should be automatically set by the browser
     *
     * @var integer
     */
    protected $_tabIndex = null;

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
    protected $_showProcessingThrobber = false;

    /**
     * Optional content to display beside the processing throbber
     *
     * @var string
     *
     * @see Zap_Button::$show_processing_throbber
     */
    protected $_processingThrobberMessage = null;

    /**
     * Optional confirmation message to display when this button is clicked
     *
     * If this message is specified, users will have to click through a
     * JavaScript confirmation dialog to submit the form. If this is null, no
     * confirmation is performed.
     *
     * @var string
     */
    protected $_confirmationMessage = null;

    /**
     * A CSS class set by the stock_id of this button
     *
     * @var string
     */
    protected $_stockClass = null;

    /**
     * Clicked
     *
     * This is set to true after processing if this button was clicked.
     * The form will also contain a refernce to the clicked button in the
     * {@link Zap_Form::$button} class variable.
     *
     * @var boolean
     */
    protected $_clicked = false;

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
        $this->_htmlHeadEntrySet->addEntrySet($yui->getHtmlHeadEntrySet());
        $this->addJavaScript(
            'packages/swat/javascript/swat-button.js',
            Zap::PACKAGE_ID
        );

        $this->_requiresId = true;
	}

	public function setTitle($title)
	{
		$this->_title = $title;

		return $this;
	}

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

        if (null === $this->_stockId) {
            $this->setFromStock('submit', false);
        } else {
            $this->setFromStock($this->_stockId, false);
        }
    }

    /**
     * Displays this button
     *
     * Outputs an XHTML input tag.
     */
    public function display()
    {
        if (! $this->_visible) {
            return;
        }

        parent::display();

        $inputTag = $this->_getInputTag();
        $inputTag->display();

        if ($this->_showProcessingThrobber ||
            null !== $this->_confirmationMessage
        ) {
            Zap::displayInlineJavaScript($this->_getInlineJavaScript());
        }
    }

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
            $this->_clicked = true;
            $this->getForm()->setButton($this);
        }
    }

    /**
     * Returns whether this button has been clicked
     *
     * @return boolean whether this button has been clicked.
     */
    public function hasBeenClicked()
    {
        return $this->_clicked;
    }

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
    public function setFromStock($stockId, $overwriteProperties = true)
    {
        switch ($stockId) {
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
                "Stock type with id of '{$stockId}' not found.",
                0, $stockId);
        }

        if ($overwriteProperties || (null === $this->_title)) {
            $this->_title = $title;
        }

        $this->_stockClass = $class;
    }

    /**
     * Get the HTML tag to display for this button
     *
     * Can be used by sub-classes to change the setup of the input tag.
     *
     * @return Zap_HtmlTag the HTML tag to display for this button.
     */
    protected function _getInputTag()
    {
        // We do not use a 'button' element because it is broken differently in
        // different versions of Internet Explorer

        $tag = new Zap_HtmlTag('input');

        $tag->type      = 'submit';
        $tag->name      = $this->_id;
        $tag->id        = $this->_id;
        $tag->value     = $this->_title;
        $tag->class     = $this->_getCSSClassString();
        $tag->tabindex  = $this->_tabIndex;
        $tag->accesskey = $this->_accessKey;

        if (! $this->isSensitive()) {
            $tag->disabled = 'disabled';
        }

        return $tag;
    }

    /**
     * Gets the array of CSS classes that are applied to this button
     *
     * @return array the array of CSS classes that are applied to this button.
     */
    protected function _getCSSClassNames()
    {
        $classes = array('swat-button');
        $form    = $this->getFirstAncestor('Zap_Form');
        $primary = (null !== $form &&
            $form->getFirstDescendant('Zap_Button') === $this);

        if ($primary) {
            $classes[] = 'swat-primary';
		}

        if (null !== $this->_stockClass) {
			$classes[] = $this->_stockClass;
		}

        $classes = array_merge($classes, parent::_getCSSClassNames());

        return $classes;
    }

    /**
     * Gets the name of the JavaScript class to instantiate for this button
     *
     * Subclasses of this class may want to return a subclass of the default
     * JavaScript button class.
     *
     * @return string the name of the JavaScript class to instantiate for this
     *                 button. Defaults to 'Zap_Button'.
     */
    protected function _getJavaScriptClass()
    {
        return 'SwatButton';
    }

    /**
     * Gets the inline JavaScript required for this control
     *
     * @return stirng the inline JavaScript required for this control.
     */
    protected function _getInlineJavaScript()
    {
        $show_processing_throbber = ($this->_showProcessingThrobber) ?
            'true' : 'false';

		$javascript = sprintf(
			"var %s_obj = new %s('%s', %s);",
            $this->_id,
            $this->_getJavaScriptClass(),
            $this->_id,
			$showProcessingThrobber
		);

        if ($this->_showProcessingThrobber) {
			$javascript .= sprintf(
				"\n%s_obj.setProcessingMessage(%s);",
				$this->_id, 
				Zap_String::quoteJavaScriptString(
					$this->_processingThrobberMessage
				)
			);
        }

        if (null !== $this->_confirmationMessage) {
			$javascript .= sprintf(
				"\n%s_obj.setConfirmationMessage(%s);",
				$this->_id, 
				Zap_String::quoteJavaScriptString(
					$this->_confirmationMessage
				)
			);
		}

        return $javascript;
    }
}


