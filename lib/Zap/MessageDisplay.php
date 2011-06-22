<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/Control.php';
require_once 'Zap/HtmlTag.php';
require_once 'Zap/Message.php';
require_once 'Zap/Exception/InvalidClass.php';
require_once 'Zap/YUI.php';

/**
 * A control to display {@link SwatMessage} objects
 *
 * @package   Zap
 * @copyright 2005-2010 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_MessageDisplay extends Zap_Control
{
	/**
	 * Dismiss link for message is on.
	 */
	const DISMISS_ON   = 1;

	/**
	 * Dismiss link for message is off.
	 */
	const DISMISS_OFF  = 2;

	/**
	 * Dismiss link for message is automatically displayed for certain message
	 * types.
	 *
	 * @see SwatMessageDisplay::getDismissableMessageTypes()
	 */
	const DISMISS_AUTO = 3;

	/**
	 * The messages to display
	 *
	 * This is an array of {@link SwatMessage} objects.
	 *
	 * @var array
	 */
	protected $_displayMessages = array();

	/**
	 * Messages in this display that are dismissable
	 *
	 * This is an array with values corresponding to a keys in the
	 * {@link SwatMessageDisplay::$display_messages} array.
	 *
	 * @var array
	 */
	protected $_dismissableMessages = array();

	/**
	 * Creates a new message display
	 *
	 * @param string $id a non-visible unique id for this widget.
	 *
	 * @see SwatWidget::__construct()
	 */
	public function __construct($id = null)
	{
		parent::__construct($id);

		$this->_requiresId = true;

		$yui = new Zap_YUI(array('animation'));
		$this->_htmlHeadEntrySet->addEntrySet($yui->getHtmlHeadEntrySet());

		$this->addJavaScript(
			'packages/swat/javascript/swat-message-display.js',
			Zap::PACKAGE_ID
		);

		$this->addStyleSheet(
			'packages/swat/styles/swat-message.css',
			Zap::PACKAGE_ID
		);

		$this->addStyleSheet(
			'packages/swat/styles/swat-message-display.css',
			Zap::PACKAGE_ID
		);
	}

	/**
	 * Adds a message
	 *
	 * Adds a new message. The message will be shown by the display() method
	 *
	 * @param string|SwatMessage $message the message to add. If the message is
	 *                                     a string, a new {@link SwatMessage}
	 *                                     object of the default message type
	 *                                     is created.
	 * @param integer $dismissable optional. Whether or not to show a dismiss
	 *                              link for the added message on this message
	 *                              display. This should be one of the
	 *                              SwatMessageDisplay::DISMISS_* constants.
	 *                              By default, the <i>$dismiss_link</i> is
	 *                              set to
	 *                              {@link SwatMessageDisplay::DISMISS_AUTO}.
	 *
	 * @throws SwatInvalidClassException
	 */
	public function add($message, $dismissable = self::DISMISS_AUTO)
	{
		if (is_string($message)) {
			$message = new Zap_Message($message);
		} elseif (!($message instanceof Zap_Message)) {
			throw new Zap_Exception_InvalidClass (
				'Cannot add message. $message must be either a string or a '.
				'Zap_Message.', 0, $message);
		}

		$this->_displayMessages[] = $message;

		if (self::DISMISS_AUTO == $dismissable) {
			$dismissable = (in_array($message->getType(),
				$this->_getDismissableMessageTypes())) ?
				self::DISMISS_ON : self::DISMISS_OFF;
		}

		if (self::DISMISS_ON == $dismissable) {
			$this->_dismissableMessages[] = count($this->_displayMessages) - 1;
		}
	}

	/**
	 * Displays messages in this message display
	 *
	 * The CSS class of each message is determined by the message being
	 * displayed.
	 */
	public function display()
	{
		if (! $this->_visible) {
			return;
		}

		if (0 == $this->getMessageCount()) {
			return;
		}

		parent::display();

		$wrapperDiv = new Zap_HtmlTag('div');
		$wrapperDiv->id    = $this->_id;
		$wrapperDiv->class = $this->_getCSSClassString();
		$wrapperDiv->open();

		$hasDismissLink = false;
		$messageCount   = count($this->_displayMessages);

		$count = 1;

		foreach ($this->_displayMessages as $key => $message) {
			if (in_array($key, $this->_dismissableMessages)) {
				$hasDismissLink = true;
			}

			$first = ($count === 1);
			$last  = ($count === $messageCount);

			$this->_displayMessage($key, $message, $first, $last);

			$count++;
		}

		$wrapperDiv->close();

		if ($hasDismissLink)
			Zap::displayInlineJavaScript($this->_getInlineJavaScript());
	}

	/**
	 * Gets whether or not this message display is visible
	 *
	 * The message display control respectes normal visibility rules. In
	 * addition, a message display is considered not visible if it contains no
	 * messages.
	 *
	 * @return boolean true if this message display is visible and false if it
	 *                  is not.
	 *
	 * @see SwatUIObject::isVisible()
	 */
	public function isVisible()
	{
		return (($this->getMessageCount() > 0) && parent::isVisible());
	}

	/**
	 * Gets the number of messages in this message display
	 *
	 * @return integer the number of messages in this message display.
	 */
	public function getMessageCount()
	{
		return count($this->_displayMessages);
	}

	/**
	 * Display a single message of this message display
	 *
	 * @param integer $message_id a unique identifier for the message within
	 *                             this message display.
	 * @param SwatMessage $message the message to display.
	 * @param boolean $first optional. Whether or not the message is the first
	 *                       message in this message display.
	 * @param boolean $last optional. Whether or not the message is the last
	 *                       message in this message display.
	 */
	protected function _displayMessage($messageId, Zap_Message $message,
		$first = false, $last = false)
	{
		$messageDiv   = new Zap_HtmlTag('div');
		$containerDiv = new Zap_HtmlTag('div');

		$messageDiv->id    = $this->_id . '_' . $messageId;
		$messageDiv->class = $message->getCSSClassString();

		if ($first) {
			$messageDiv->class .= ' swat-message-first';
		}

		if ($last) {
			$messageDiv->class .= ' swat-message-last';
		}

		$messageDiv->open();

		$containerDiv->class = 'swat-message-container';
		$containerDiv->open();

		$primaryContent = new Zap_HtmlTag('h3');
		$primaryContent->class = 'swat-message-primary-content';
		$primaryContent->setContent(
			$message->getPrimaryContent(), 
			$message->getContentType()
		);

		$primaryContent->display();

		if (null !== $message->getSecondaryContent()) {
			$secondaryDiv = new Zap_HtmlTag('div');
			$secondaryDiv->class = 'swat-message-secondary-content';
			$secondaryDiv->setContent(
				$message->getSecondaryContent(), 
				$message->getContentType()
			);

			$secondaryDiv->display();
		}

		$containerDiv->close();
		$messageDiv->close();
	}

	/**
	 * Gets an array of message types that are dismissable by default
	 *
	 * @return array message types that are dismissable by default.
	 */
	protected function _getDismissableMessageTypes()
	{
		return array(
			'notice',
			'warning',
		);
	}

	/**
	 * Gets the array of CSS classes that are applied to this message display
	 *
	 * @return array the array of CSS classes that are applied to this message
	 *                display.
	 */
	protected function _getCSSClassNames()
	{
		$classes = array('swat-message-display');
		$classes = array_merge($classes, parent::_getCSSClassNames());
		return $classes;
	}

	/**
	 * Gets the inline JavaScript for hiding messages
	 */
	protected function getInlineJavaScript()
	{
		static $shown = false;

		if (! $shown) {
			$javascript = $this->_getInlineJavaScriptTranslations();
			$shown = true;
		} else {
			$javascript = '';
		}

		$dismissableMessages = '[' 
							 . implode(', ', $this->_dismissableMssages)
							 . ']';

		$javascript.= sprintf(
			"var %s_obj = new %s('%s', %s);",
			$this->_id, 
			$this->_getJavaScriptClass(), 
			$this->_id,
			$dismissableMessages
		);

		return $javascript;
	}

	/**
	 * Gets the name of the JavaScript class to instantiate for this message
	 * display
	 *
	 * Sub-classes of this class may want to return a sub-class of the default
	 * JavaScript form class.
	 *
	 * @return string the name of the JavaScript class to instantiate for this
	 *                 form . Defaults to 'SwatMessageDisplay'.
	 */
	protected function _getJavaScriptClass()
	{
		return 'SwatMessageDisplay';
	}

	/**
	 * Gets translatable string resources for the JavaScript object for
	 * this widget
	 *
	 * @return string translatable JavaScript string resources for this widget.
	 */
	protected function _getInlineJavaScriptTranslations()
	{
		$closeText  = Zap::_('Dismiss message.');
		return "SwatMessageDisplayMessage.close_text = '{$closeText}';\n";
	}
}


