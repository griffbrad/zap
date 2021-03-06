<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/Control.php';
require_once 'Zap/HtmlTag.php';
require_once 'Swat/exceptions/SwatUndefinedStockTypeException.php';

/**
 * A a tool link in the widget tree
 *
 * @package   Zap
 * @copyright 2005-2007 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_ToolLink extends Zap_Control
{
	// {{{ public properties

	/**
	 * The href attribute in the XHTML anchor tag
	 *
	 * Optionally uses vsprintf() syntax, for example:
	 * <code>
	 * $tool_link->link = 'MySection/MyPage/%s?id=%s';
	 * </code>
	 *
	 * @var string
	 *
	 * @see SwatToolLink::$value
	 */
	public $link = null;

	/**
	 * The title of this link
	 *
	 * @var string
	 */
	public $title = null;

	/**
	 * Optional content type for the title of the link
	 *     default text/plain
	 *     use text/xml for XHTML fragments
	 *
	 * @var string
	 */
	public $content_type = 'text/plain';

	/**
	 * A value or array of values to substitute into the link of this cell
	 *
	 * The value property may be specified either as an array of values or as
	 * a single value. If an array is passed, a call to vsprintf() is done
	 * on the {@link SwatToolLink::$link} property. If the value is a string
	 * a single sprintf() call is made.
	 *
	 * @var mixed
	 *
	 * @see SwatToolLink::$link
	 */
	public $value = null;

	/**
	 * The stock id of this tool link
	 *
	 * Specifying a stock id initializes this tool link with a set of
	 * stock values.
	 *
	 * @var string
	 *
	 * @see SwatToolLink::setFromStock()
	 */
	public $stock_id = null;

	/**
	 * Access key for this link
	 *
	 * Specifying an access key makes this tool link keyboard-accessible.
	 *
	 * @var string
	 */
	public $access_key = null;

	/**
	 * An optional tooltip for this element
	 *
	 * An optional string that will be displayed when the element is moused
	 * over. Setting the tooltip property to null will display no tooltip.
	 *
	 * @var string
	 */
	public $tooltip = null;

	// }}}
	// {{{ protected properties

	/**
	 * A CSS class set by the stock_id of this tool link
	 *
	 * @var string
	 */
	protected $stock_class = null;

	// }}}
	// {{{ public function __construct()

	/**
	 * Creates a new toollink
	 *
	 * @param string $id a non-visible unique id for this widget.
	 *
	 * @see SwatWidget::__construct()
	 */
	public function __construct($id = null)
	{
		parent::__construct($id);

		$this->addStyleSheet('packages/swat/styles/swat-tool-link.css',
			Zap::PACKAGE_ID);
	}

	// }}}
	// {{{ public function init()

	/**
	 * Initializes this widget
	 *
	 * Loads properties from stock if $stock_id is set.
	 *
	 * @see SwatWidget::init()
	 */
	public function init()
	{
		parent::init();

		if ($this->stock_id !== null)
			$this->setFromStock($this->stock_id, false);
	}

	// }}}
	// {{{ public function display()

	/**
	 * Displays this tool link
	 */
	public function display()
	{
		if (!$this->visible)
			return;

		parent::display();

		if ($this->isSensitive())
			$this->getSensitiveTag()->display();
		else
			$this->getInsensitiveTag()->display();
	}

	// }}}
	// {{{ public function setFromStock()

	/**
	 * Sets the values of this tool link to a stock type
	 *
	 * Valid stock type ids are:
	 *
	 * - create
	 * - add
	 * - edit
	 * - delete
	 * - cancel
	 * - preview
	 * - change-order
	 * - help
	 * - print
	 * - email
	 *
	 * @param string $stock_id the identifier of the stock type to use.
	 * @param boolean $overwrite_properties whether to overwrite properties if
	 *                                       they are already set.
	 *
	 * @throws SwatUndefinedStockTypeException
	 */
	public function setFromStock($stock_id, $overwrite_properties = true)
	{
		switch ($stock_id) {
		case 'create':
			$title = Zap::_('Create');
			$class = 'swat-tool-link-create';
			break;

		case 'add':
			$title = Zap::_('Add');
			$class = 'swat-tool-link-add';
			break;

		case 'edit':
			$title = Zap::_('Edit');
			$class = 'swat-tool-link-edit';
			break;

		case 'delete':
			$title = Zap::_('Delete');
			$class = 'swat-tool-link-delete';
			break;

		case 'cancel':
			$title = Zap::_('Cancel');
			$class = 'swat-tool-link-cancel';
			break;

		case 'preview':
			$title = Zap::_('Preview');
			$class = 'swat-tool-link-preview';
			break;

		case 'change-order':
			$title = Zap::_('Change Order');
			$class = 'swat-tool-link-change-order';
			break;

		case 'help':
			$title = Zap::_('Help');
			$class = 'swat-tool-link-help';
			break;

		case 'print':
			$title = Zap::_('Print');
			$class = 'swat-tool-link-print';
			break;

		case 'email':
			$title = Zap::_('Email');
			$class = 'swat-tool-link-email';
			break;

		default:
			throw new SwatUndefinedStockTypeException(
				"Stock type with id of '{$stock_id}' not found.",
				0, $stock_id);
		}

		if ($overwrite_properties || ($this->title === null))
			$this->title = $title;

		$this->stock_class = $class;
	}

	// }}}
	// {{{ protected function getCSSClassNames()

	/**
	 * Gets the array of CSS classes that are applied to this tool link
	 *
	 * @return array the array of CSS classes that are applied to this tool
	 *                link.
	 */
	protected function getCSSClassNames()
	{
		$classes = array('swat-tool-link');

		if (!$this->isSensitive())
			$classes[] = 'swat-tool-link-insensitive';

		if ($this->stock_class !== null)
			$classes[] = $this->stock_class;

		$classes = array_merge($classes, $this->classes);

		return $classes;
	}

	// }}}
	// {{{ protected function getSensitiveTag()

	/**
	 * Gets the tag used to display this tool link when it is sensitive
	 *
	 * @return SwatHtmlTag the tag used to display this tool link when it is
	 *                      sensitive.
	 */
	protected function getSensitiveTag()
	{
		$tag = new SwatHtmlTag('a');

		$tag->id = $this->id;
		$tag->class = $this->getCSSClassString();

		if ($this->value === null)
			$tag->href = $this->link;
		elseif (is_array($this->value))
			$tag->href = vsprintf($this->link, $this->value);
		else
			$tag->href = sprintf($this->link, $this->value);

		if ($this->tooltip !== null)
			$tag->title = $this->tooltip;

		$tag->accesskey = $this->access_key;
		$tag->setContent($this->title, $this->content_type);

		return $tag;
	}

	// }}}
	// {{{ protected function getInsensitiveTag()

	/**
	 * Gets the tag used to display this tool link when it is not sensitive
	 *
	 * @return SwatHtmlTag the tag used to display this tool link when it is
	 *                      not sensitive.
	 */
	protected function getInsensitiveTag()
	{
		$tag = new SwatHtmlTag('span');

		$tag->id = $this->id;
		$tag->class = $this->getCSSClassString();

		if ($this->tooltip !== null)
			$tag->title = $this->tooltip;

		$tag->setContent($this->title, $this->content_type);

		return $tag;
	}

	// }}}
}


