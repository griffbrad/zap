<?php

/**
 * A component in the Yahoo UI Library
 *
 * This class is used internally by the {@link SwatYUI} class and is not meant
 * to be used by itself.
 *
 * @package   Zap
 * @copyright 2006-2007 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @see       SwatYUI
 */
class Zap_YUIComponent extends Zap_Object
{
	private $_id;

	private $_dependencies = array();

	private $_htmlHeadEntries = array();

	private $_beta = false;

	/**
	 * Creates a new YUI component
	 *
	 * @param string $id the identifier of this YUI component. This corresponds
	 *                    to a directory name under the <i>build</i> directory
	 *                    in the YUI distribution.
	 * @param boolean $beta optional. Whether or not this component is in beta.
	 *                       Beta components have a slightly different naming
	 *                       convention.
	 */
	public function __construct($id, $beta = false)
	{
		$this->_id   = $id;
		$this->_beta = $beta;

		$this->_htmlHeadEntrySet['normal'] = new Zap_HtmlHeadEntrySet();
		$this->_htmlHeadEntrySet['debug']  = new Zap_HtmlHeadEntrySet();
		$this->_htmlHeadEntrySet['min']    = new Zap_HtmlHeadEntrySet();
	}

	/**
	 * Adds a YUI component dependency to this YUI component
	 *
	 * @param SwatYUIComponent the YUI component this component depends on.
	 */
	public function addDependency(Zap_YUIComponent $component)
	{
		$this->_dependencies[] = $component;
	}

	/**
	 * Adds a {@link SwatJavaScriptHtmlHeadEntry} to this YUI component
	 *
	 * YUI component JavaScript is distributed in three modes:
	 * - debug
	 * - min
	 * - normal
	 *
	 * Adding JavaScript using this method creates HTML head entries for each
	 * of these three modes.
	 *
	 * @param string $component_directory optional. The YUI component directory
	 *                                     the JavaScript exists in. If the
	 *                                     directory is not specified, this
	 *                                     component's id is used.
	 * @param string $filename optional. The filename of the YUI JavaScript for
	 *                          this component. If not specified, the name of
	 *                          the component is used. Do not specify the file
	 *                          extension or the -min/-debug suffix here.
	 */
	public function addJavaScript($componentDirectory = '', $filename = '')
	{
		if ('' == $componentDirectory) {
			$componentDirectory = $this->_id;
		}

		if ('' == $filename) {
			$filename = $this->_id;
		}

		$modes = array(
			'min'    => '-min',
			'debug'  => '-debug',
			'normal' => '',
		);

		if ($this->_beta) {
			$filenameTemplate =
				'packages/yui/' . $componentDirectory . '/' . $filename . '-beta%s.js';
		} else {
			$filenameTemplate =
				'packages/yui/' . $componentDirectory . '/' . $filename . '%s.js';
		}

		foreach ($modes as $mode => $suffix) {
			$filename = sprintf($filenameTemplate, $suffix);
			$entry    = new Zap_JavaScriptHtmlHeadEntry(
				$filename, 
				Zap_YUI::PACKAGE_ID
			);

			$this->_htmlHeadEntrySet[$mode]->addEntry($entry);
		}
	}

	/**
	 * Adds a {@link SwatStyleSheetHtmlHeadEntry} to this YUI component
	 *
	 * YUI component style sheets are distributed in three modes:
	 * - min
	 * - normal
	 *
	 * Adding style sheets using this method creates HTML head entries for
	 * these two modes.
	 *
	 * @param string $component_directory optional. The YUI component directory
	 *                                     the style sheet exists in. If the
	 *                                     directory is not specified, this
	 *                                     component's id is used.
	 * @param string $filename optional. The filename of the YUI style-sheet for
	 *                          this component. If not specified, the name of
	 *                          the component is used. Do not specify the file
	 *                          extension or the -min  suffix here.
	 * @param boolean $has_min_version optional. Whether or not the style-sheet
	 *                                  for this component has a minimized
	 *                                  version in the YUI distribution.
	 *                                  Defaults to true.
	 */
	public function addStyleSheet($componentDirectory = '', $filename = '',
		$hasMinVersion = true)
	{
		if ('' == $componentDirectory) {
			$componentDirectory = $this->_id;
		}

		if ('' == $filename) {
			$filename = $this->_id;
		}

		$modes = array(
			'min'    => '-min',
			'debug'  => '',
			'normal' => '',
		);

		if (! $hasMinVersion) {
			$modes['min'] = '';
		}

		$filenameTemplate =
			'packages/yui/' . $componentDirectory . '/' . $filename.'%s.css';

		foreach ($modes as $mode => $suffix) {
			$filename = sprintf($filenameTemplate, $suffix);
			$entry    = new Zap_StyleSheetHtmlHeadEntry(
				$filename,
				Zap_YUI::PACKAGE_ID
			);

			$this->_htmlHeadEntrySet[$mode]->addEntry($entry);
		}
	}

	/**
	 * Gets the set of {@link SwatHtmlHeadEntry} objects required for this
	 * YUI component
	 *
	 * @return SwatHtmlHeadEntrySet the set of {@link SwatHtmlHeadEntry}
	 *                               objects required for this YUI component.
	 */
	public function getHtmlHeadEntrySet($mode = 'min')
	{
		$set = new Zap_HtmlHeadEntrySet();

		if (isset($this->_htmlHeadEntrySet[$mode])) {
			foreach ($this->_dependencies as $component) {
				$set->addEntrySet($component->getHtmlHeadEntrySet($mode));
			}

			$set->addEntrySet($this->_htmlHeadEntrySet[$mode]);
		}

		return $set;
	}
}


