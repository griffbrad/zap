<?php

/**
 * A form widget which can contain other widgets
 *
 * Zap_Forms are very useful for processing widgets. For most widgets, if they
 * are not inside a Zap_Form they will not be able to be processed properly.
 *
 * With Swat's default style, Zap_Form widgets have no visible margins, padding
 * or borders.
 *
 * @package   Zap
 * @copyright 2004-2010 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_Form extends Zap_DisplayableContainer
{
	const METHOD_POST = 'post';
	const METHOD_GET  = 'get';

	const PROCESS_FIELD = '_swat_form_process';
	const HIDDEN_FIELD = '_swat_form_hidden_fields';
	const AUTHENTICATION_TOKEN_FIELD = '_swat_form_authentication_token';
	const SERIALIZED_PREFIX = '_swat_form_serialized_';

	const ENCODING_FIELD        = '_swat_form_character_encoding';
	const ENCODING_ENTITY_VALUE = '&auml;&trade;&reg;';
	const ENCODING_UTF8_VALUE   = "\xc3\xa4\xe2\x84\xa2\xc2\xae";
	const ENCODING_8BIT_VALUE   = "\xe4\x99\xae";

	/**
	 * The action attribute of the HTML form tag
	 *
	 * @var string
	 */
	protected $_action = '#';

	/**
	 * Encoding type of the form
	 *
	 * Used for multipart forms for file uploads.
	 *
	 * @var string
	 */
	protected $_encodingType = null;

	/**
	 * A list of character set encodings the server can process
	 *
	 * The client will use this list to pick an appropriate character set for
	 * form data sent to the server.
	 *
	 * The list is formatted according to the HTML form element's
	 * {@link http://www.w3.org/TR/html401/interact/forms.html#adef-accept-charset accept-charset}
	 * attribute syntax.
	 *
	 * By default, the only accepted character set is UTF-8.
	 *
	 * @var string
	 */
	protected $_acceptCharset = 'utf-8';

	/**
	 * Whether or not to automatically focus the a default SwatControl when
	 * this form loads
	 *
	 * Autofocusing is good for applications or pages that are keyboard driven
	 * -- such as data entry forms -- as it immediatly places the focus on the
	 * form.
	 *
	 * @var boolean
	 */
	protected $_autofocus = false;

	/**
	 * A reference to the default control to focus when the form loads
	 *
	 * If this is not set then it defaults to the first SwatControl
	 * in the form.
	 *
	 * @var SwatControl
	 */
	protected $_defaultFocusedControl = null;

	/**
	 * A reference to the button that was clicked to submit the form,
	 * or null if the button is not set.
	 *
	 * You usually do not want to explicitly set this in your code because
	 * other parts of Swat set this property automatically.
	 *
	 * @var SwatButton
	 */
	protected $_button = null;

	/**
	 * Whether or not to use auto-complete for elements in this form
	 *
	 * Autocomplete is a browser-specific extension that is enabled by default.
	 * If the browser is autocompleting fields it shouldn't (e.g. login info
	 * on a non-login page) the behavior may be turned off by setting this to
	 * false. In Swat, this currently only solves the problem for users with
	 * JavaScript.
	 *
	 * @var boolean
	 */
	protected $_autocomplete = true;

	/**
	 * The default value to use for signature salt
	 *
	 * If this value is not null, all newly instantiated forms will call the
	 * {@link SwatForm::setSalt()} method with this value as the <i>$salt</i>
	 * parameter.
	 *
	 * @var string
	 */
	protected static $_defaultSalt = null;

	/**
	 * Hidden form fields
	 *
	 * An array of the form:
	 *    name => value
	 * where all the values are passed as hidden fields in this form.
	 *
	 * @var array
	 *
	 * @see SwatForm::addHiddenField()
	 * @see SwatForm::getHiddenField()
	 */
	protected $_hiddenFields = array();

	/**
	 * The value to use when salting serialized data signatures
	 *
	 * @var string
	 */
	protected $_salt = null;

	/**
	 * The default encoding to assume for 8-bit content submitted from clients
	 *
	 * Form data is automatically converted from this encoding to UTF-8 when the
	 * form is processed.
	 *
	 * By default, 'windows-1252' is used. This also handles ISO 8859-1 content
	 * as Windows-1252 is a superset of ISO 8859-1.
	 *
	 * @var string
	 *
	 * @see Zap_Form::setDefault8BitEncoding()
	 * @see Zap_Form::set8BitEncoding()
	 */
	protected static $_default8bitEncoding = 'windows-1252';

	/**
	 * The encoding to assume for 8-bit content submitted from clients
	 *
	 * Form data is automatically converted from this encoding to UTF-8 when the
	 * form is processed.
	 *
	 * @var string
	 *
	 * @see Zap_Form::set8BitEncoding()
	 */
	protected $_8bitEncoding = null;

	/**
	 * The default URI at which a Connection: close header may be sent to the
	 * browser
	 *
	 * The Connection: close header may be used to work around
	 * {@link https://bugs.webkit.org/show_bug.cgi?id=5760 Webkit bug #5760}.
	 *
	 * By default, no Connection: close URI is specified. In this case, no
	 * workaround is attempted when Zap_Form forms are submitted.
	 *
	 * @var string
	 *
	 * @see Zap_Form::setDefaultConnectionCloseUri()
	 * @see Zap_Form::setConnectionCloseUri()
	 */
	protected static $_defaultConnectionCloseUri = null;

	/**
	 * URI at which a Connection: close header may be sent to the browser
	 *
	 * This may be used to work around
	 * {@link https://bugs.webkit.org/show_bug.cgi?id=5760 Webkit bug #5760}.
	 *
	 * @var string
	 *
	 * @see Zap_Form::setConnectionCloseUri()
	 */
	protected $_connectionCloseUri = null;

	/**
	 * The method to use for this form
	 *
	 * Is one of Zap_Form::METHOD_* constants.
	 *
	 * @var string
	 */
	private $_method = Zap_Form::METHOD_POST;

	/**
	 * The token value used to prevent cross-site request forgeries
	 *
	 * If this value is not null, all submitted forms may be checked to see if
	 * they are authenticated with this token value.
	 *
	 * @var string
	 *
	 * @see Zap_Form::setAuthenticationToken()
	 * @see Zap_Form::clearAuthenticationToken()
	 * @see Zap_Form::isAuthenticated()
	 */
	private static $_authenticationToken = null;

	/**
	 * Creates a new form
	 *
	 * @param string $id a non-visible unique id for this widget.
	 *
	 * @see SwatWidget::__construct()
	 */
	public function __construct($id = null)
	{
		parent::__construct($id);

		if (null !== self::$_defaultSalt) {
			$this->setSalt(self::$_defaultSalt);
		}

		if (null !== self::$_default8bitEncoding) {
			$this->set8BitEncoding(self::$_default8bitEncoding);
		}

		if (null !== self::$_defaultConnectionCloseUri) {
			$this->setConnectionCloseUri(self::$_defaultConnectionCloseUri);
		}

		$this->_requiresId = true;

		$this->addJavaScript(
			'packages/swat/javascript/swat-form.js',
			Zap::PACKAGE_ID
		);
	}

	/**
	 * Sets the HTTP method this form uses to send data
	 *
	 * @param string $method a method constant. Must be one of
	 *                        Zap_Form::METHOD_* otherwise an error is thrown.
	 *
	 * @throws SwatException
	 */
	public function setMethod($method)
	{
		$validMethods = array(Zap_Form::METHOD_POST, Zap_Form::METHOD_GET);

		if (! in_array($method, $validMethods)) {
			throw new Zap_Exception("'{$method}' is not a valid form method.");
		}

		$this->_method = $method;

		return $this;
	}

	/**
	 * Gets the HTTP method this form uses to send data
	 *
	 * @return string a method constant.
	 */
	public function getMethod()
	{
		return $this->_method;
	}

	/**
	 * Displays this form
	 *
	 * Outputs the HTML form tag and calls the display() method on each child
	 * widget of this form. Then, after all the child widgets are displayed,
	 * displays all hidden fields.
	 *
	 * This method also adds a hidden field called 'process' that is given
	 * the unique identifier of this form as a value.
	 */
	public function display()
	{
		if (! $this->_visible) {
			return;
		}

		Zap_Widget::display();

		$this->addHiddenField(self::PROCESS_FIELD, $this->_id);

		$formTag = $this->getFormTag();

		$formTag->open();
		$this->_displayChildren();
		$this->_displayHiddenFields();
		$formTag->close();

		if ('' != $this->_connectionCloseUri) {
			$yui = new Zap_YUI(array('event'));
			$this->html_head_entry_set->addEntrySet(
				$yui->getHtmlHeadEntrySet());
		}

		Zap::displayInlineJavaScript($this->getInlineJavaScript());
	}

	/**
	 * Processes this form
	 *
	 * If this form has been submitted then calls the process() method on
	 * each child widget. Then processes hidden form fields.
	 *
	 * This form is only marked as processed if it was submitted by the user.
	 *
	 * @return true if this form was actually submitted, false otherwise.
	 *
	 * @see SwatContainer::process()
	 */
	public function process()
	{
		$this->_processed = $this->isSubmitted();

		if ($this->_processed) {
			$this->_processEncoding();
			$this->_processHiddenFields();

			foreach ($this->children as $child) {
				if (null !== $child && ! $child->isProcessed()) {
					$child->process();
				}
			}
		}
	}

	/**
	 * Adds a hidden form field
	 *
	 * Adds a form field to this form that is not shown to the user. Hidden
	 * form fields are outputted as <i>type="hidden"</i> input tags. Values are
	 * serialized before being output so the value can be either a primitive
	 * type or an object. Unserialization happens automatically when
	 * {@link Zap_Form::getHiddenField()} is used to retrieve the value. For
	 * non-array and non-object types, the value is also stored as an
	 * unserialized value that can be retrieved without using
	 * Zap_Form::getHiddenField().
	 *
	 * @param string $name the name of the field.
	 * @param mixed $value the value of the field, either a string or an array.
	 *
	 * @see Zap_Form::getHiddenField()
	 *
	 * @throws SwatInvalidTypeException if an attempt is made to add a value
	 *                                  of type 'resource'.
	 */
	public function addHiddenField($name, $value)
	{
		if (is_resource($value)) {
			throw new Zap_Exception_InvalidType(
				'Cannot add a hidden field of type ‘resource’ to a Zap_Form.',
				0, $value);
		}

		$this->_hiddenFields[$name] = $value;
	}

	/**
	 * Gets the value of a hidden form field
	 *
	 * @param string $name the name of the field whose value to get.
	 *
	 * @return mixed the value of the field. The type of the field is preserved
	 *                from the call to {@link Zap_Form::addHiddenField()}. If
	 *                the field does not exist, null is returned.
	 *
	 * @throws Zap_Exception_InvalidSerializedData if the serialized form data
	 *                                             does not match the signature
	 *                                             data.
	 *
	 * @see Zap_Form::addHiddenField()
	 */
	public function getHiddenField($name)
	{
		$data = null;

		// get value of a hidden field we've already unserialized after
		// processing this form
		if (isset($this->_hiddenFields[$name])) {
			$data = $this->_hiddenFields[$name];

		// otherwise, make sure this form was processed and get hidden field
		// from raw form data
		} elseif (! $this->_processed && $this->isSubmitted()) {
			$rawData = $this->getFormData();
			$serializedFieldName = self::SERIALIZED_PREFIX . $name;

			if (isset($rawData[$serializedFieldName])) {
				$data = $this->_unserializeHiddenField(
					$rawData[$serializedFieldName]
				);
			}
		}

		return $data;
	}

	/**
	 * Clears all hidden fields
	 */
	public function clearHiddenFields()
	{
		$this->_hiddenFields = array();
	}

	/**
	 * Adds a widget within a new Zap_Form
	 *
	 * This is a convenience method that does the following:
	 * - creates a new Zap_Form,
	 * - adds the widget as a child of the form field,
	 * - and then adds the Zap_Form to this form.
	 *
	 * @param SwatWidget $widget a reference to a widget to add.
	 * @param string $title the visible title of the form field.
	 */
	public function addWithField(SwatWidget $widget, $title)
	{
		$field = new Zap_FormField();
		$field->add($widget);
		$field->title = $title;
		$this->add($field);
	}

	/**
	 * Returns the super-global array with this form's data
	 *
	 * Returns a reference to the super-global array containing this
	 * form's data. The array is chosen based on this form's method.
	 *
	 * @return array a reference to the super-global array containing this
	 *                form's data.
	 */
	public function &getFormData()
	{
		$data = null;

		switch ($this->_method) {
		case Zap_Form::METHOD_POST:
			$data = &$_POST;
			break;
		case Zap_Form::METHOD_GET:
			$data = &$_GET;
			break;
		}

		return $data;
	}

	/**
	 * Whether or not this form was submitted on the previous page request
	 *
	 * This method may becalled before or after the Zap_Form::process() method.
	 * and is thus sometimes more useful than Zap_Form::isProcessed() which
	 * only returns a meaningful value after Zap_Form::process() is called.
	 *
	 * @return boolean true if this form was submitted on the previous page
	 *                  request and false if it was not.
	 */
	public function isSubmitted()
	{
		$rawData = $this->getFormData();

		return (isset($rawData[self::PROCESS_FIELD]) 
			&& $rawData[self::PROCESS_FIELD] == $this->_id);
	}

	/**
	 * Whether or not this form is authenticated
	 *
	 * This can be used to catch cross-site request forgeries if the
	 * {@link Zap_Form::setAuthenticationToken()} method was previously called.
	 *
	 * If form authentication is used, processing should only be performed on
	 * authenticated forms. An unauthenticated form may be a malicious
	 * request.
	 *
	 * @return boolean true if this form is authenticated or if this form does
	 *                  not use authentication. False if this form is
	 *                  not authenticated.
	 */
	public function isAuthenticated()
	{
		/*
		 * If this form was not submitted, consider it authenticated. Processing
		 * should be safe on forms that were not submitted.
		 */
		if (! $this->isSubmitted()) {
			return true;
		}

		$rawData = $this->getFormData();
		$token   = null;

		if (isset($raw_data[self::AUTHENTICATION_TOKEN_FIELD])) {
			$token = Zap_String::signedUnserialize(
				$raw_data[self::AUTHENTICATION_TOKEN_FIELD], 
				$this->_salt
			);
		}

		/*
		 * If this form's authentication token is set, the token in submitted
		 * data must match.
		 */
		return (null === self::$_authenticationToken 
			|| self::$_authenticationToken === $token);
	}

	/**
	 * Sets the salt value to use when salting signature data
	 *
	 * @param string $salt the value to use when salting signature data.
	 */
	public function setSalt($salt)
	{
		$this->_salt = (string) $salt;

		return $this;
	}

	/**
	 * Gets the salt value to use when salting signature data
	 *
	 * {@link SwatInputControl} widgets may want ot use this value for salting
	 * their own data. This can be done using:
	 *
	 * <code>
	 * $salt = $this->getForm()->getSalt();
	 * </code>
	 *
	 * @return string the value to use when salting signature data.
	 */
	public function getSalt()
	{
		return $this->_salt;
	}

	/**
	 * Sets the encoding to assume for 8-bit content submitted from clients
	 *
	 * Form data is automatically converted from this character encoding to
	 * UTF-8 when the form is processed.
	 *
	 * @param string $encoding the encoding to use. If null is specified, no
	 *                          character encoding conversion is performed.
	 *
	 * @see Zap_Form::setDefault8BitEncoding()
	 */
	public function set8BitEncoding($encoding)
	{
		$this->_8bitEncoding = $encoding;
	}

	/**
	 * Sets the URI at which a Connection: close header may be sent to the
	 * browser
	 *
	 * The Connection: close header may be used to work around
	 * {@link https://bugs.webkit.org/show_bug.cgi?id=5760 Webkit bug #5760}.
	 *
	 * @param string $connection_close_uri the URI to use. If null is specified,
	 *                                      no workarounds will be performed
	 *                                      for Safari.
	 *
	 * @see Zap_Form::setDefaultConnectionCloseUri()
	 */
	public function setConnectionCloseUri($connectionCloseUri)
	{
		$this->_connectionCloseUri = $connectionCloseUri;
	}

	/**
	 * Sets the default encoding to assume for 8-bit content submitted from
	 * clients
	 *
	 * Form data is automatically converted from this character encoding to
	 * UTF-8 when the form is processed.
	 *
	 * @param string $encoding the encoding to use. If null is specified, no
	 *                          character encoding conversion is performed.
	 *
	 * @see Zap_Form::set8BitEncoding()
	 */
	public static function setDefault8BitEncoding($encoding)
	{
		self::$_default8bitEncoding = $encoding;
	}

	/**
	 * Sets the default URI at which a Connection: close header may be sent to
	 * the browser
	 *
	 * The Connection: close header may be used to work around
	 * {@link https://bugs.webkit.org/show_bug.cgi?id=5760 Webkit bug #5760}.
	 *
	 * @param string $connection_close_uri the URI to use. If null is specified,
	 *                                      no workarounds will be performed
	 *                                      for Safari. The null behavior is
	 *                                      the default behavior.
	 *
	 * @see Zap_Form::setConnectionCloseUri()
	 */
	public static function setDefaultConnectionCloseUri($connectionCloseUri)
	{
		self::$_defaultConnectionCloseUri = $connectionCloseUri;
	}

	/**
	 * Sets the token value used to prevent cross-site request forgeries
	 *
	 * After the authentication token is set, when any form is processed, the
	 * the submitted form data must contain this token.
	 *
	 * For the safest results, this token should be taken from an active
	 * session. For usability reasons, the same token should be used for the
	 * same user over multiple requests. The token should be unique to a user's
	 * session and should be difficult to guess.
	 *
	 * @param string $token the value used to prevent cross-site request
	 *                       forgeries.
	 */
	public static function setAuthenticationToken($token)
	{
		self::$_authenticationToken = (string) $token;
	}

	/**
	 * Clears the token value used to prevent cross-site request forgeries
	 *
	 * After this method is called, no cross-site request forgery detection can
	 * be performed, and all forms will be considered authenticated. This is
	 * acceptable if a user's session is ending and the threat of cross-site
	 * request forgeries is gone.
	 */
	public static function clearAuthenticationToken()
	{
		self::$_authenticationToken = null;
	}

	/**
	 * Checks submitted form data for hidden fields
	 *
	 * Checks submitted form data for hidden fields. If hidden fields are
	 * found, properly re-adds them to this form.
	 *
	 * @throws SwatInvalidSerializedDataException if the serialized form data
	 *                                            does not match the signature
	 *                                            data.
	 */
	protected function processHiddenFields()
	{
		$rawData = $this->getFormData();

		$serializedFieldName = self::HIDDEN_FIELD;

		if (! isset($raw_data[$serialized_field_name])) {
			return;
		} else {
			$fields = Zap_String::signedUnserialize(
				$rawData[$serializedFieldName], 
				$this->_salt
			);
		}

		foreach ($fields as $name) {
			$serializedFieldName = self::SERIALIZED_PREFIX . $name;

			if (isset($rawData[$serializedFieldName])) {
				$this->hiddenFields[$name] = $this->_unserializeHiddenField(
					$rawData[$serializedFieldName]);
			}
		}
	}

	/**
	 * Detects 8-bit character encoding in form data and converts data to UTF-8
	 *
	 * Conversion is only performed if this form's 8-bit encoding is set. This
	 * form's 8-bit encoding may be set automatically if the Zap_Form default
	 * 8-bit encoding is set.
	 *
	 * This algorithm is adapted from a blog post at
	 * {@link http://blogs.sun.com/shankar/entry/how_to_handle_utf_8}.
	 *
	 * @throws SwatException if an 8-bit encoding is set and the form data is
	 *                       neither 8-bit nor UTF-8.
	 */
	protected function processEncoding()
	{
		$raw_data = &$this->getFormData();

		if ($this->_8bit_encoding !== null &&
			isset($raw_data[self::ENCODING_FIELD])) {

			$value = $raw_data[self::ENCODING_FIELD];

			if ($value === self::ENCODING_8BIT_VALUE) {
				// convert from our 8-bit encoding to utf-8
				foreach ($raw_data as $key => &$value) {
					$value = iconv($this->_8bit_encoding, 'utf-8', $value);
				}
				foreach ($_FILES as &$file) {
					$file['name'] = iconv($this->_8bit_encoding, 'utf-8',
						$file['name']);
				}
			} elseif ($value !== self::ENCODING_UTF8_VALUE) {
				// it's not 8-bit or UTF-8. Time to panic!
				throw new SwatInvalidCharacterEncodingException(
					"Unknown form data character encoding. Form data: \n".
					file_get_contents('php://input'));
			}
		}
	}

	// }}}
	// {{{ protected function notifyOfAdd()

	/**
	 * Notifies this widget that a widget was added
	 *
	 * If any of the widgets in the added subtree are file entry widgets then
	 * set this form's encoding accordingly.
	 *
	 * @param SwatWidget $widget the widget that has been added.
	 *
	 * @see SwatContainer::notifyOfAdd()
	 */
	protected function notifyOfAdd($widget)
	{
		if (class_exists('SwatFileEntry')) {

			if ($widget instanceof SwatFileEntry) {
				$this->encoding_type = 'multipart/form-data';
			} elseif ($widget instanceof SwatUIParent) {
				$descendants = $widget->getDescendants();
				foreach ($descendants as $sub_widget) {
					if ($sub_widget instanceof SwatFileEntry) {
						$this->encoding_type = 'multipart/form-data';
						break;
					}
				}
			}
		}
	}

	/**
	 * Displays hidden form fields
	 *
	 * Displays hiden form fields as <input type="hidden" /> XHTML elements.
	 * This method automatically handles array type values so they will be
	 * returned correctly as arrays.
	 *
	 * This methods also generates an array of hidden field names and passes
	 * them as hidden fields.
	 *
	 * If an authentication token is set on this form to prevent cross-site
	 * request forgeries, the token is displayed in a hidden field.
	 */
	protected function _displayHiddenFields()
	{
		$inputTag = new Zap_HtmlTag('input');
		$inputTag->type = 'hidden';

		echo '<div class="swat-hidden">';

		if (null !== $this->_8bitEncoding) {
			// The character encoding detection field is intentionally not using
			// SwatHtmlTag to avoid minimizing entities.
			echo '<input type="hidden" ',
				'name="', self::ENCODING_FIELD, '" ',
				'value="', self::ENCODING_ENTITY_VALUE, '" />';
		}

		foreach ($this->_hiddenFields as $name => $value) {
			// display unserialized value for primative types
			if (null !== $value && ! is_array($value) && ! is_object($value)) {

				// SwatHtmlTag uses Zap_String::minimizeEntities(), which
				// prevents double-escaping entities. For hidden form-fields,
				// we want data to be returned exactly as it was specified. This
				// necessitates double-escaping to ensure any entities that were
				// specified in the hidden field value are returned correctly.
				$escapedValue = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');

				$inputTag->name  = $name;
				$inputTag->value = $escapedValue;
				$inputTag->display();
			}

			// display serialized value
			$serializedData = $this->_serializeHiddenField($value);

			// SwatHtmlTag uses Zap_String::minimizeEntities(), which prevents
			// double-escaping entities. For hidden form-fields, we want data
			// to be returned exactly as it was specified. This  necessitates
			// double-escaping to ensure any entities that were specified in
			// the hidden field value are returned correctly.
			$escapedSerializedData = htmlspecialchars($serializedData,
				ENT_COMPAT, 'UTF-8');

			$inputTag->name  = self::SERIALIZED_PREFIX . $name;
			$inputTag->value = $escapedSerializedData;
			$inputTag->display();
		}

		// display hidden field names
		if (0 < count($this->_hiddenFields)) {
			// array of field names
			$serializedData = Zap_String::signedSerialize(
				array_keys($this->_hiddenFields), $this->_salt);

			$inputTag->name  = self::HIDDEN_FIELD;
			$inputTag->value = $serializedData;
			$inputTag->display();
		}

		// display authentication token
		if (null !== self::$_authenticationToken) {
			$serializedData = Zap_String::signedSerialize(
				self::$_authentication_token, 
				$this->_salt
			);

			$inputTag = new SwatHtmlTag('input');
			$inputTag->type  = 'hidden';
			$inputTag->name  = self::AUTHENTICATION_TOKEN_FIELD;
			$inputTag->value = $serializedData;
			$inputTag->display();
		}

		echo '</div>';
	}

	/**
	 * Gets the XHTML form tag used to display this form
	 *
	 * @return SwatHtmlTag the XHTML form tag used to display this form.
	 */
	protected function getFormTag()
	{
		$formTag = new Zap_HtmlTag('form');

		$formTag->addAttributes(
			array(
				'id'             => $this->_id,
				'method'         => $this->_method,
				'enctype'        => $this->_encodingType,
				'accept-charset' => $this->_acceptCharset,
				'action'         => $this->_action,
				'class'          => $this->_getCSSClassString(),
			)
		);

		return $formTag;
	}

	/**
	 * Gets the array of CSS classes that are applied to this form
	 *
	 * @return array the array of CSS classes that are applied to this form.
	 */
	protected function _getCSSClassNames()
	{
		$classes = array('swat-form');
		$classes = array_merge($classes, parent::_getCSSClassNames());
		return $classes;
	}

	/**
	 * Gets inline JavaScript required for this form
	 *
	 * Right now, this JavaScript focuses the first SwatControl in the form.
	 *
	 * @return string inline JavaScript required for this form.
	 */
	protected function _getInlineJavaScript()
	{
		$javaScript = sprintf(
			"var %s_obj = new %s(%s, %s);",
			$this->_id,
			$this->_getJavaScriptClass(),
			Zap_String::quoteJavaScriptString($this->_id),
			Zap_String::quoteJavaScriptString($this->_connectionCloseUri)
		);

		if ($this->_autofocus) {
			$focusable = true;

			if (null === $this->_defaultFocusedControl) {
				$control = $this->getFirstDescendant('SwatControl');

				if (null === $control) {
					$focusable = false;
				} else {
					$focusId = $control->getFocusableHtmlId();

					if ($focusId === null) {
						$focusable = false;
					}
				}
			} else {
				$focusId = $this->_defaultFocusedControl->getFocusableHtmlId();

				if (null === $focusId) {
					$focusable = false;
				}
			}

			if ($focusable) {
				$javascript .= 
					"\n{$this->_id}_obj.setDefaultFocus('{$focusId}');";
			}
		}

		if (! $this->_autocomplete) {
			$javascript .= "\n{$this->_id}_obj.setAutocomplete(false);";
		}

		return $javascript;
	}

	/**
	 * Gets the name of the JavaScript class to instantiate for this form
	 *
	 * Sub-classes of this class may want to return a sub-class of the default
	 * JavaScript form class.
	 *
	 * @return string the name of the JavaScript class to instantiate for this
	 *                 form . Defaults to 'Zap_Form'.
	 */
	protected function _getJavaScriptClass()
	{
		return 'SwatForm';
	}

	/**
	 * Serializes a hidden field value into a string safe for including in
	 * form data
	 *
	 * @param mixed $value the hidden field value to serialize.
	 *
	 * @return string the hidden field value serialized for safely including in
	 *                 form data.
	 */
	protected function _serializeHiddenField($value)
	{
		$value = Zap_String::signedSerialize($value, $this->_salt);

		// escape special characters that confuse browsers (mostly IE;
		// null characters confuse all browsers)
		$value = str_replace('\\', '\\\\', $value);
		$value = str_replace("\x00", '\x00', $value);
		$value = str_replace("\x0a", '\x0a', $value);
		$value = str_replace("\x0d", '\x0d', $value);

		return $value;
	}

	/**
	 * Unserializes a hidden field value that was serialized using
	 * {@link Zap_Form::serializeHiddenField()}
	 *
	 * @param string $value the hidden field value to unserialize.
	 *
	 * @return mixed the unserialized value.
	 *
	 * @throws SwatInvalidSerializedDataException if the serialized form data
	 *                                            does not match the signature
	 *                                            data.
	 */
	protected function unserializeHiddenField($value)
	{
		// unescape special characters (see serializeHiddenField())
		$value = str_replace('\x00', "\x00", $value);
		$value = str_replace('\x0a', "\x0a", $value);
		$value = str_replace('\x0d', "\x0d", $value);
		$value = str_replace('\\\\', '\\',   $value);

		$value = Zap_String::signedUnserialize($value, $this->_salt);

		return $value;
	}
}


