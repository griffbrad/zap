<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/UIObject.php';
require_once 'Zap/Message.php';

/**
 * Base class for all widgets
 *
 * <strong>Widget composition:</strong>
 *
 * Complicated widgets composed of multiple individual widgets can be easily
 * built using <code>Zap_Widget</code>'s composite features. The main methods
 * used for widget composition are:
 * {@link Zap_Widget::createCompositeWidgets()},
 * {@link Zap_Widget::addCompositeWidget()} and
 * {@link Zap_Widget::getCompositeWidget()}.
 *
 * Developers should implement the <code>createCompositeWidgets()</code> method
 * by creating composite widgets and adding them to this widget by calling
 * <code>addCompositeWidget()</code>. As long as the parent implemtations of
 * {@link Zap_Widget::init()} and {@link Zap_Widget::process()} are called,
 * nothing further needs to be done for <code>init()</code> and
 * <code>process()</code>. For the {@link Zap_Widget::display()} method,
 * developers can use the <code>getCompositeWidget()</code> method to retrieve
 * a specific composite widget for display. Composite widgets are <i>not</i>
 * displayed by the default implementation of <code>display()</code>.
 *
 * In keeping with object-oriented composition theory, none of the composite
 * widgets are publicly accessible. Methods could be added to make composite
 * widgets available publicly, but in that case it would be better to just
 * extend {@link Zap_Container}.
 *
 * @package   Zap
 * @copyright 2004-2007 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
abstract class Zap_Widget extends Zap_UIObject
{
	/**
	 * A non-visible unique id for this widget, or null
	 *
	 * @var string
	 */
	protected $_id = null;

	/**
	 * Sensitive
	 *
	 * Whether the widget is sensitive. If a widget is sensitive it reacts to
	 * user input. Insensitive widgets should display "grayed-out" to inform
	 * the user they are not sensitive. All widgets that the user can interact
	 * with should respect this property.
	 *
	 * @var boolean
	 */
	protected $_sensitive = true;

	/**
	 * Stylesheet
	 *
	 * The URI of a stylesheet for use with this widget. If this property is
	 * set before {@link Zap_Widget::init()} then the
	 * {@link Zap_UIObject::addStyleSheet()} method will be called to add this
	 * stylesheet to the header entries. Primarily this should be used by
	 * Zap_UI to set a stylesheet in Zap_ML. To set a stylesheet in PHP code,
	 * it is recommended to call <code>addStyleSheet()</code> directly.
	 *
	 * @var string
	 */
	protected $_stylesheet = null;

	/**
	 * Composite widgets of this widget
	 *
	 * Array is of the form 'key' => widget.
	 *
	 * @var array
	 */
	private $_compositeWidgets = array();

	/**
	 * Whether or not composite widgets have been created
	 *
	 * This flag is used by {@link Zap_Widget::confirmCompositeWidgets()} to
	 * ensure composite widgets are only created once.
	 *
	 * @var boolean
	 */
	private $_compositeWidgetsCreated = false;

	/**
	 * Messages affixed to this widget
	 *
	 * @var array
	 */
	protected $_messages = array();

	/**
	 * Specifies that this widget requires an id
	 *
	 * If an id is required then the init() method sets a unique id if an id
	 * is not already set manually.
	 *
	 * @var boolean
	 *
	 * @see Zap_Widget::init()
	 */
	protected $_requiresId = false;

	/**
	 * Whether or not this widget has been initialized
	 *
	 * @var boolean
	 *
	 * @see Zap_Widget::init()
	 */
	protected $_initialized = false;

	/**
	 * Whether or not this widget has been processed
	 *
	 * @var boolean
	 *
	 * @see Zap_Widget::process()
	 */
	protected $_processed = false;

	/**
	 * Whether or not this widget has been displayed
	 *
	 * @var boolean
	 *
	 * @see Zap_Widget::display()
	 */
	protected $_displayed = false;

	/**
	 * Creates a new widget
	 *
	 * @param string $id a non-visible unique id for this widget.
	 */
	public function __construct($id = null)
	{
		parent::__construct();

		$this->_id = $id;

		$this->addStylesheet(
			'packages/swat/styles/swat.css',
			Zap::PACKAGE_ID
		);
	}

	public function setId($id)
	{
		$this->_id = $id;

		return $this;
	}

	public function getId()
	{
		return $this->_id;
	}

	/**
	 * Initializes this widget
	 *
	 * Initialization is done post-construction. Initilization may be done
	 * manually by calling <code>init()</code> on the UI tree at any time. If a
	 * call to {@link Zap_Widget::process()} or {@link Zap_Widget::display()}
	 * is made  before the tree is initialized, this method is called
	 * automatically. As a result, you often do not need to worry about calling
	 * <code>init()</code>.
	 *
	 * Having an initialization method separate from the constructor allows
	 * properties to be manually set on widgets after construction but before
	 * initilization.
	 *
	 * Composite widgets of this widget are automatically initialized as well.
	 */
	public function init()
	{
		if ($this->_requiresId && null === $this->_id) {
			$this->_id = $this->_getUniqueId();
		}

		if (null !== $this->_stylesheet) {
			$this->addStyleSheet($this->_stylesheet);
		}

		foreach ($this->_getCompositeWidgets() as $widget) {
			$widget->init();
		}

		$this->_initialized = true;
	}

	/**
	 * Processes this widget
	 *
	 * After a form submit, this widget processes itself and its dependencies
	 * and then recursively processes  any of its child widgets.
	 *
	 * Composite widgets of this widget are automatically processed as well.
	 *
	 * If this widget has not been initialized, it is automatically initialized
	 * before processing.
	 */
	public function process()
	{
		if (! $this->isInitialized()) {
			$this->init();
		}

		foreach ($this->_getCompositeWidgets() as $widget) {
			$widget->process();
		}

		$this->_processed = true;
	}

	/**
	 * Displays this widget
	 *
	 * Displays this widget displays as well as recursively displays any child
	 * widgets of this widget.
	 *
	 * If this widget has not been initialized, it is automatically initialized
	 * before displaying.
	 */
	public function display()
	{
		if (! $this->isInitialized()) {
			$this->init();
		}

		$this->_displayed = true;
	}

	/**
	 * Displays the HTML head entries for this widget
	 *
	 * Each entry is displayed on its own line. This method should
	 * be called inside the <head /> element of the layout.
	 */
	public function displayHtmlHeadEntries()
	{
		$set = $this->getHtmlHeadEntrySet();
		$set->display();
	}

	/**
	 * Gets the Zap_HtmlHeadEntry objects needed by this widget
	 *
	 * If this widget has not been displayed, an empty set is returned to
	 * reduce the number of required HTTP requests.
	 *
	 * @return Zap_HtmlHeadEntrySet the {@link Zap_HtmlHeadEntry} objects
	 *                               needed by this widget.
	 */
	public function getHtmlHeadEntrySet()
	{
		if ($this->isDisplayed()) {
			$set = new Zap_HtmlHeadEntrySet($this->_htmlHeadEntrySet);
		} else {
			$set = new Zap_HtmlHeadEntrySet();
		}

		foreach ($this->_getCompositeWidgets() as $widget) {
			$set->addEntrySet($widget->getHtmlHeadEntrySet());
		}

		return $set;
	}

	/**
	 * Adds a message to this widget
	 *
	 * The message may be shown by the {@link Zap_Widget::display()} method and
	 * will as cause {@link Zap_Widget::hasMessage()} to return as true.
	 *
	 * @param Zap_Message $message the message to add.
	 */
	public function addMessage(Zap_Message $message)
	{
		$this->_messages[] = $message;
	}

	/**
	 * Gets all messages
	 *
	 * Gathers all messages from children of this widget and this widget
	 * itself.
	 *
	 * Messages from composite widgets of this widget are included by default.
	 *
	 * @return array an array of {@link Zap_Message} objects.
	 */
	public function getMessages()
	{
		$messages = $this->_messages;

		foreach ($this->_getCompositeWidgets() as $widget) {
			$messages = array_merge($messages, $widget->getMessages());
		}

		return $messages;
	}

	/**
	 * Checks for the presence of messages
	 *
	 * @return boolean true if this widget or the subtree below this widget has
	 *                  one or more messages.
	 */
	public function hasMessage()
	{
		$hasMessage = (0 < count($this->_messages));

		if (! $hasMessage) {
			foreach ($this->_getCompositeWidgets() as $widget) {
				if ($widget->hasMessage()) {
					$hasMessage = true;
					break;
				}
			}
		}

		return $hasMessage;
	}

	/**
	 * Determines the sensitivity of this widget.
	 *
	 * Looks at the sensitive property of the ancestors of this widget to
	 * determine if this widget is sensitive.
	 *
	 * @return boolean whether this widget is sensitive.
	 *
	 * @see Zap_Widget::$_sensitive
	 */
	public function isSensitive()
	{
		if (null !== $this->_parent && $this->_parent instanceof Zap_Widget) {
			return ($this->_parent->isSensitive() && $this->_sensitive);
		} else {
			return $this->_sensitive;
		}
	}

	/**
	 * Whether or not this widget is initialized
	 *
	 * @return boolean whether or not this widget is initialized.
	 */
	public function isInitialized()
	{
		return $this->_initialized;
	}

	/**
	 * Whether or not this widget is processed
	 *
	 * @return boolean whether or not this widget is processed.
	 */
	public function isProcessed()
	{
		return $this->_processed;
	}

	/**
	 * Whether or not this widget is displayed
	 *
	 * @return boolean whether or not this widget is displayed.
	 */
	public function isDisplayed()
	{
		return $this->_displayed;
	}

	/**
	 * Gets the id attribute of the XHTML element displayed by this widget
	 * that should receive focus
	 *
	 * Elements receive focus either through JavaScript methods or by clicking
	 * on label elements with their for attribute set. If there is no such
	 * element (for example, there are several elements and none is more
	 * important than the others) then null is returned.
	 *
	 * By default, widgets return null and are un-focusable. Sub-classes that
	 * are focusable should override this method to return the appripriate
	 * XHTML id.
	 *
	 * @return string the id attribute of the XHTML element displayed by this
	 *                 widget that should receive focus or null if there is
	 *                 no such element.
	 */
	public function getFocusableHtmlId()
	{
		return null;
	}

	/**
	 * Replace this widget with a new container
	 *
	 * Replaces this widget in the widget tree with a new {@link Zap_Container},
	 * then adds this widget to the new container.
	 *
	 * @param Zap_Container $container optional container to use
	 *
	 * @throws Zap_Exception
	 *
	 * @return Zap_Container a reference to the new container.
	 */
	public function replaceWithContainer(Zap_Container $container = null)
	{
		if (null === $this->_parent) {
			throw new Zap_Exception('Widget does not have a parent, unable '.
				'to replace this widget with a container.');
		}

		if (null === $container) {
			$container = new Zap_Container();
		}

		$parent = $this->_parent;
		$parent->replace($this, $container);
		$container->add($this);

		return $container;
	}

	/**
	 * Performs a deep copy of the UI tree starting with this UI object
	 *
	 * @param string $id_suffix optional. A suffix to append to copied UI
	 *                           objects in the UI tree.
	 *
	 * @return Zap_UIObject a deep copy of the UI tree starting with this UI
	 *                       object.
	 *
	 * @see Zap_UIObject::copy()
	 */
	public function copy($idSuffix = '')
	{
		$copy = parent::copy($idSuffix);

		if ('' != $id_suffix && null !== $copy->getId()) {
			$copy->setId($copy->getId() . $idSuffix);
		}

		foreach ($this->_compositeWidgets as $key => $compositeWidget) {
			$compositeCopy = $compositeWidget->copy($idSuffix);
			$compositeCopy->setParent($copy);
			$copy->_compositeWidgets[$key] = $compositeCopy;
		}

		return $copy;
	}

	/**
	 * @todo document me
	 */
	abstract public function printWidgetTree();

	/**
	 * Gets the array of CSS  classes that are applied  to this widget
	 *
	 * @return array the array of CSS  classes that are applied to this widget.
	 */
	protected function _getCSSClassNames()
	{
		$classes = array();

		if (! $this->isSensitive()) {
			$classes[] = 'swat-insensitive';
		}

		$classes = array_merge($classes, parent::_getCSSClassNames());

		return $classes;
	}

	/**
	 * Creates and adds composite widgets of this widget
	 *
	 * Created composite widgets should be added in this method using
	 * {@link Zap_Widget::addCompositeWidget()}.
	 */
	protected function _createCompositeWidgets()
	{
	}

	/**
	 * Adds a composite a widget to this widget
	 *
	 * @param Zap_Widget $widget the composite widget to add.
	 * @param string $key a key identifying the widget so it may be retrieved
	 *                     later. The key does not have to be the widget's id
	 *                     but the key does have to be unique within this
	 *                     widget relative to the keys of other composite
	 *                     widgets.
	 *
	 * @throws Zap_DuplicateIdException if a composite widget with the
	 *                                   specified key is already added to this
	 *                                   widget.
	 * @throws Zap_Exception if the specified widget is already the child of
	 *                        another object.
	 */
	protected final function _addCompositeWidget(Zap_Widget $widget, $key)
	{
		if (array_key_exists($key, $this->_compositeWidgets)) {
			throw new Zap_DuplicateIdException(sprintf(
				"A composite widget with the key '%s' already exists in this ".
				"widget.", $key), 0, $key);
		}

		if (null !== $widget->_parent) {
			throw new Zap_Exception('Cannot add a composite widget that '.
				'already has a parent.');
		}

		$this->_compositeWidgets[$key] = $widget;
		$widget->setParent($this);
	}

	/**
	 * Gets a composite widget of this widget by the composite widget's key
	 *
	 * This is used by other methods to retrieve a specific composite widget.
	 * This method ensures composite widgets are created before trying to
	 * retrieve the specified widget.
	 *
	 * @param string $key the key of the composite widget to get.
	 *
	 * @return Zap_Widget the specified composite widget.
	 *
	 * @throws Zap_WidgetNotFoundException if no composite widget with the
	 *                                     specified key exists in this widget.
	 */
	protected final function _getCompositeWidget($key)
	{
		$this->_confirmCompositeWidgets();

		if (! array_key_exists($key, $this->_compositeWidgets)) {
			throw new Zap_WidgetNotFoundException(sprintf(
				"Composite widget with key of '%s' not found in %s. Make sure ".
				"the composite widget was created and added to this widget.",
				$key, get_class($this)), 0, $key);
		}
		
		return $this->_compositeWidgets[$key];
	}

	/**
	 * Gets all composite widgets added to this widget
	 *
	 * This method ensures composite widgets are created before retrieving the
	 * widgets.
	 *
	 * @param string $class_name optional class name. If set, only widgets
	 *                            that are instances of <code>$class_name</code>
	 *                            are returned.
	 *
	 * @return array all composite wigets added to this widget. The array is
	 *                indexed by the composite widget keys.
	 *
	 * @see Zap_Widget::addCompositeWidget()
	 */
	protected final function _getCompositeWidgets($className = null)
	{
		$this->_confirmCompositeWidgets();

		if (! (null === $className ||
			class_exists($className) || interface_exists($className))
		) {
			return array();
		}

		$out = array();

		foreach ($this->_compositeWidgets as $key => $widget) {
			if (null === $className || $widget instanceof $className) {
				$out[$key] = $widget;
			}
		}

		return $out;
	}

	/**
	 * Confirms composite widgets have been created
	 *
	 * Widgets are only created once. This method may be called multiple times
	 * in different places to ensure composite widgets are available. In general,
	 * it is best to call this method before attempting to use composite
	 * widgets.
	 *
	 * This method is called by the default implementations of init(),
	 * process() and is called any time {@link Zap_Widget::getCompositeWidget()}
	 * is called so it rarely needs to be called manually.
	 */
	protected final function _confirmCompositeWidgets()
	{
		if (!$this->_compositeWidgetsCreated) {
			$this->_createCompositeWidgets();
			$this->_compositeWidgetsCreated = true;
		}
	}
}


