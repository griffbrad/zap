<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'ReCaptcha/ReCaptcha.php';
require_once 'Zap/Message.php';
require_once 'Zap/InputControl.php';

/**
 * A widget used to display and validate reCAPTCHA's
 *
 * @package   Zap
 * @copyright 2007 silverorange, 2011 Delta Systems
 * @lisence   http://www.gnu.org/copyleft/lesser.html LGPL Lisence 2.1
 */
class Zap_ReCaptcha extends Zap_InputControl
{
	// {{{ public properties

	/**
	 * Public Key
	 *
	 * The public key obtained from the ReCaptcha website used to communicate
	 * with their servers.
	 *
	 * @var string
	 */
	public $public_key = null;

	/**
	 * Private Key
	 *
	 * The private key obtained from the ReCaptcha website used to communicate
	 * with their servers.
	 *
	 * @var string
	 */
	public $private_key = null;

	/**
	 * If you are displaying a page to the user over SSL, be sure to set this
	 * to true so an error dialog doesn't come up in the user's browser.
	 *
	 * @var boolean
	 */
	public $secure = false;

	// }}}
	// {{{ public function display()

	/**
	 * Displays this ReCaptcha widget
	 */
	public function display()
	{
		if (!$this->visible)
			return;

		parent::display();

		/*
		 * Second parameter is null because errors are displayed as
		 * SwatMessage objects affixed to this widget.
		 */
		ReCaptcha::display($this->public_key, null, $this->secure);
	}

	// }}}
	// {{{ public function process()

	/**
	 * Processes this ReCaptcha Widget
	 *
	 * If the user entered an incorrect response a message is displayed and
	 * validation is halted.
	 */
	public function process()
	{
		parent::process();

		$form = $this->getForm();
		$data = $form->getFormData();

		$response = ReCaptcha::validate($this->private_key,
			$_SERVER['REMOTE_ADDR'],
			$data['recaptcha_challenge_field'],
			$data['recaptcha_response_field']);

		if (!$response->is_valid) {
			$message = new SwatMessage(Zap::_(
				'The words you entered did not match the words displayed. '.
				'Please try again.'), 'error');

			$this->addMessage($message);
		}
	}

	// }}}
}
