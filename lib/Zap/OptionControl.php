<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/InputControl.php';
require_once 'Zap/Option.php';

/**
 * A base class for controls using a set of options
 *
 * @package   Zap
 * @copyright 2004-2007 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
abstract class Zap_OptionControl extends Zap_InputControl
{
	/**
	 * Options
	 *
	 * An array of {@link SwatOptions}
	 *
	 * @var array
	 */
	protected $_options = array();

	/**
	 * Metadata for the options of this control
	 *
	 * An array with the object hash of the option as the key and a sub-array
	 * of name-value pairs as the metadata. For example:
	 *
	 * <code>
	 * <?php
	 * array(
	 *     spl_object_hash($option1) => array(
	 *         'classes' => array('small'),
	 *     ),
	 *     spl_object_hash($option2) => array(
	 *         'classes' => array('large'),
	 *     ),
	 * );
	 * ?>
	 * </code>
	 *
	 * Any metadata may be added to options. It is up to the control to make
	 * use of particular metadata fields. Common metadata fields are:
	 *
	 * - classes - an array of CSS classes
	 *
	 * @var array
	 *
	 * @see SwatOptionControl::addOption()
	 * @see SwatOptionControl::addOptionMetadata()
	 * @see SwatOptionControl::getOptionMetadata()
	 */
	protected $_optionMetadata = array();

	/**
	 * Whether or not to serialize option values
	 *
	 * If option values are serialized, the PHP type is remembered between
	 * page loads. This is useful if, for example, your option values are a mix
	 * of strings, integers or null values. You can also use complex objects as
	 * option values if this property is set to <i>true</i>.
	 *
	 * If this property is set to <i>false</i>, the values are always converted
	 * to strings. This is most useful for SwatForms using the GET method but
	 * could be applicable in other circumstances.
	 *
	 * @var boolean
	 */
	protected $_serializeValues = true;

	/**
	 * Adds an option to this option control
	 *
	 * This method has a number of signatures. You can add an existing option
	 * object, or create a new option object from a value and title:
	 *
	 * <code>
	 * <?php
	 * // 1. add a new option from value and title
	 * $control->addOption(123, 'Option Title');
	 *
	 * // 2. add an existing option object
	 * $option = new SwatOption(123, 'Option Title');
	 * $control->addOption($option);
	 *
	 * // 3. add an option with metadata
	 * $option = new SwatOption(123, 'Option Title');
	 * $control->addOption($option, array('classes' => array('large')));
	 * ?>
	 * </code>
	 *
	 * @param mixed|SwatOption $value either a value for the option, or a
	 *                                 {@link SwatOption} object. If a
	 *                                 SwatOption is used, the
	 *                                 <i>$content_type</i> parameter of
	 *                                 this method call is ignored and the
	 *                                 <i>$title</i> parameter may be used to
	 *                                 specify option metadata.
	 * @param array|string $title optional. Either a string containing the
	 *                             title of the added option, or an array
	 *                             containing metadata for the SwatOption
	 *                             specified in the <i>$value</i> parameter.
	 * @param string $content_type optional. The content type of the title. If
	 *                              not specified, defaults to 'text/plain'.
	 *                              Ignored if the <i>$value</i> parameter is
	 *                              a SwatOption object.
	 *
	 * @see SwatOptionControl::$options
	 * @see SwatOptionControl::addOptionMetadata()
	 */
	public function addOption($value, $title = '', $contentType = 'text/plain')
	{
		if ($value instanceof Zap_Option) {
			$option = $value;
		} else {
			$option = new Zap_Option($value, $title, $contentType);
		}

		$this->_options[] = $option;

		// initialize metadata
		$key = $this->_getOptionMetadataKey($option);

		if (! isset($this->_optionMetadata[$key])) {
			// use isset so we don't erase the metadata if an option is added
			// twice
			$this->_optionMetadata[$key] = array();
		}

		if ($value instanceof Zap_Option && is_array($title)) {
			$this->addOptionMetadata($option, $title);
		} else {
			$this->addOptionMetadata($option, array());
		}

		return $this;
	}

	/**
	 * Sets the metadata for an option
	 *
	 * Any metadata may be added to options. It is up to the control to make
	 * use of particular metadata fields. Common metadata fields are:
	 *
	 * - classes - an array of CSS classes
	 *
	 * @param SwatOption $option the option for which to set the metadata.
	 * @param array|string $metadata either an array of metadata to add to the
	 *                                option, or a string specifying the name
	 *                                of the metadata field to add.
	 * @param mixed $value optional. If the <i>$metadata</i> parameter is a
	 *                      string, this is the metadata value to set for the
	 *                      option. Otherwise, this parameter is ignored.
	 *
	 * @see SwatOptionControl::addOption()
	 * @see SwatOptionControl::getOptionMetadata()
	 */
	public function addOptionMetadata(Zap_Option $option, $metadata,
		$value = null)
	{
		$key = $this->_getOptionMetadataKey($option);

		if (is_array($metadata)) {
			$this->_optionMetadata[$key] = array_merge(
				$this->_optionMetadata[$key], $metadata);
		} else {
			$this->_optionMetadata[$key][$metadata] = $value;
		}
	}

	/**
	 * Gets the metadata for an option
	 *
	 * Any metadata may be added to options. It is up to the control to make
	 * use of particular metadata fields. Common metadata fields are:
	 *
	 * - classes - an array of CSS classes
	 *
	 * @param SwatOption $option the option for which to get the metadata.
	 * @param string $metadata optional. An optional metadata property to get.
	 *                          If not specified, all available metadata for
	 *                          the option is returned.
	 *
	 * @returns array|mixed an array of the metadata for this option, or a
	 *                      specific metadata value if the <i>$metadata</i>
	 *                      parameter is specified. If <i>$metadata</i> is
	 *                      specified and no such metadata field exists for the
	 *                      specified option, null is returned.
	 *
	 * @see SwatOptionControl::addOptionMetadata()
	 */
	public function getOptionMetadata(Zap_Option $option, $metadata = null)
	{
		$key = $this->_getOptionMetadataKey($option);

		if (null === $metadata) {
			if (isset($this->_optionMetadata[$key])) {
				$metadata = $this->_optionMetadata[$key];
			} else {
				$metadata = array();
			}
		} else {
			if (isset($this->_optionMetadata[$key]) &&
				isset($this->_optionMetadata[$key][$metadata])) {
				$metadata = $this->_optionMetadata[$key][$metadata];
			} else {
				$metadata = null;
			}
		}

		return $metadata;
	}

	/**
	 * Removes an option from this option control
	 *
	 * @param SwatOption $option the option to remove.
	 *
	 * @return SwatOption the removed option or null if no option was removed.
	 */
	public function removeOption(Zap_Option $option)
	{
		$removedOption = null;

		foreach ($this->_options as $key => $controlOption) {
			if ($controlOption === $option) {
				$removedOption = $controlOption;

				// remove from options list
				unset($this->_options[$key]);

				// remove metadata
				$key = $this->_getOptionMetadataKey($controlOption);
				unset($this->_optionMetadata[$key]);
			}
		}

		return $removedOption;
	}

	/**
	 * Removes options from this option control by their value
	 *
	 * @param mixed $value the value of the option or options to remove.
	 *
	 * @return array an array of removed SwatOption objects or an empty array
	 *                if no options are removed.
	 */
	public function removeOptionsByValue($value)
	{
		$removedOptions = array();

		foreach ($this->_options as $key => $controlOption) {
			if ($controlOption->getValue() === $value) {
				$removedOptions[] = $controlOption;

				// remove from options list
				unset($this->_options[$key]);

				// remove metadata
				$metadataKey = $this->_getOptionMetadataKey($controlOption);
				unset($this->_optionMetadata[$metadataKey]);
			}
		}

		return $removedOptions;
	}

	/**
	 * Adds options to this option control using an associative array
	 *
	 * @param array $options an associative array of options. Keys are option
	 *                        values. Values are option titles.
	 * @param string $content_type optional. The content type of the option
	 *                              titles. If not specified, defaults to
	 *                              'text/plain'.
	 */
	public function addOptionsByArray(array $options,
		$contentType = 'text/plain')
	{
		foreach ($options as $value => $title) {
			$this->addOption($value, $title, $contentType);
		}
	}

	/**
	 * Gets options from this option control by their value
	 *
	 * @param mixed $value the value of the option or options to get.
	 *
	 * @return array an array of SwatOption objects or an empty array if no
	 *                options with the given value exist within this option
	 *                control.
	 */
	public function getOptionsByValue($value)
	{
		$options = array();

		foreach ($this->_options as $option) {
			if ($option->getValue() === $value) {
				$options[] = $option;
			}
		}

		return $options;
	}

	/**
	 * Gets a reference to the array of options
	 *
	 * Subclasses may want to override this method.
	 *
	 * @return array a reference to the array of options.
	 */
	public function &getOptions()
	{
		return $this->_options;
	}

	/**
	 * Gets an option within this option control
	 *
	 * @param integer $index the ordinal position of the option within this
	 *                        option control.
	 *
	 * @return SwatOption a reference to the option, or null if no such option
	 *                     exists within this option control.
	 */
	protected function _getOption($index)
	{
		$option = null;

		if (array_key_exists($index, $this->_options)) {
			$option = $this->_options[$index];
		}

		return $option;
	}

	/**
	 * Gets the key used to load and store metadata for an option
	 *
	 * @param SwatOption $option the option for which to get the key.
	 *
	 * @return string the key used to load and store metadata for the specified
	 *                option.
	 */
	protected function _getOptionMetadataKey(Zap_Option $option)
	{
		return spl_object_hash($option);
	}
}

