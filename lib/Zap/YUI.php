<?php

/**
 * Object for building Swat HTML head entry dependencies for Yahoo UI Library
 * components
 *
 * Most of Swat's UI objects using JavaScript make use of the Yahoo User
 * Interface Library (YUI) to abstract cross-browser event-handling, DOM
 * manipulation and CSS positioning. YUI's JavaScript is separated into
 * separate components. This class takes a list of YUI components and generates
 * a set of {@link SwatHtmlHeadEntry} objects required for the YUI component.
 * This greatly simplifies using YUI in Swat UI objects.
 *
 * YUI components are distributed in three modes:
 * - min
 * - normal
 * - debug
 *
 * The 'normal' mode is regular JavaScript and style-sheet code with full
 * documentation and whitespace formatting. The 'min' mode is the same as
 * 'normal' except the whitespace has been compressed and the comments have
 * been stripped. The 'debug' mode is the same as normal except special
 * debugging code has been added to the JavaScript.
 *
 * When using SwatYUI to generate a set of HTML head entries, you can specify
 * one of the three modes to suit your needs.
 *
 * Example usage:
 * <code>
 * $yui = new SwatYUI('dom');
 * $html_head_entries = $yui->getHtmlHeadEntrySet();
 * </code>
 *
 * @package   Zap
 * @copyright 2006-2010 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_YUI extends Zap_Object
{
	/**
	 * Package ID for YUI component HTML head entries
	 */
	const PACKAGE_ID = 'SwatYUI';

	/**
	 * Static component definitions
	 *
	 * This array is used for each instance of SwatYUI and contains component
	 * definitions and dependency information.
	 *
	 * @var array
	 * @see SwatYUI::buildComponents()
	 */
	private static $_components = array();

	/**
	 * The {@link SwatHtmlHeadEntrySet} required for this SwaYUI object
	 *
	 * @var SwatHtmlHeadEntrySet
	 */
	private $_htmlHeadEntrySet;

	/**
	 * Creates a new SwatYUI HTML head entry set building object
	 *
	 * @param array $component_ids an array of YUI component ids to build a
	 *                              HTML head entry set for.
	 * @param string $mode the YUI component mode to use. Should be one of the
	 *                      'min', 'normal' or 'debug'. The default mode is
	 *                      'normal'.
	 */
	public function __construct(array $componentIds, $mode = 'normal')
	{
		self::buildComponents();

		if (! is_array($componentIds)) {
			$componentIds = array($componentIds);
		}

		$this->_htmlHeadEntrySet =
			$this->buildHtmlHeadEntrySet($componentIds, $mode);
	}

	/**
	 * Gets the HTML head entry set required for the YUI components of this
	 * object
	 *
	 * @return SwatHtmlHeadEntrySet
	 */
	public function getHtmlHeadEntrySet()
	{
		return $this->_htmlHeadEntrySet;
	}

	/**
	 * Builds the HTML head entry set required for the YUI components of this
	 * object
	 *
	 * @param array $component_ids an array of YUI component ids to build
	 *                              HTML head entries for.
	 * @param string $mode the YUI component mode to use.
	 *
	 * @return SwatHtmlHeadEntrySet the full constructed set of HTML head
	 *                               entries.
	 */
	private function buildHtmlHeadEntrySet(array $componentIds, $mode)
	{
		$set = new Zap_HtmlHeadEntrySet();

		foreach ($componentIds as $componentId) {
			$set->addEntrySet(
				self::$_components[$componentId]->getHtmlHeadEntrySet($mode)
			);
		}

		$set->addEntry($this->_getAttributionHtmlHeadEntry());

		return $set;
	}

	private function _getAttributionHtmlHeadEntry()
	{
		$comment = "Yahoo! UI Library (YUI) is Copyright (c) 2007-2009, ".
			"Yahoo! Inc.\n\t     http://developer.yahoo.com/yui/license.html";

		return new Zap_CommentHtmlHeadEntry($comment, self::PACKAGE_ID);
	}

	/**
	 * Builds the YUI component definitions and dependency information
	 *
	 * Since this is a large data structure, the actual building is only done
	 * once and the result is stored in a static class variable.
	 */
	private static function buildComponents()
	{
		static $componentsBuilt = false;
		static $components      = array();

		if ($componentsBuilt) {
			return;
		}

		$components['animation'] = new Zap_YUIComponent('animation');
		$components['animation']->addJavaScript();

		$components['autocomplete'] = new Zap_YUIComponent('autocomplete');
		$components['autocomplete']->addJavaScript();

		$components['base'] = new Zap_YUIComponent('base');
		$components['base']->addStyleSheet();

		$components['button'] = new Zap_YUIComponent('button');
		$components['button']->addJavaScript();
		$components['button']->addStyleSheet('button/assets/skins/sam', '',
			false);

		$components['calendar'] = new Zap_YUIComponent('calendar');
		$components['calendar']->addJavaScript();

		$components['charts'] = new Zap_YUIComponent('charts');
		$components['charts']->addJavaScript();

		$components['connection'] = new Zap_YUIComponent('connection');
		$components['connection']->addJavaScript();

		$components['container'] = new Zap_YUIComponent('container');
		$components['container']->addJavaScript();
		$components['container']->addStyleSheet('container/assets', '', false);

		$components['container_core'] = new Zap_YUIComponent('container_core');
		$components['container_core']->addJavaScript('container');
		$components['container_core']->addStyleSheet('container/assets',
			'container-core', false);

		$components['datasource'] = new Zap_YUIComponent('datasource');
		$components['datasource']->addJavaScript();

		$components['datatable'] = new Zap_YUIComponent('datatable');
		$components['datatable']->addJavaScript();
		$components['datatable']->addStyleSheet('datatable/assets/skins/sam', '',
			false);

		$components['dom'] = new Zap_YUIComponent('dom');
		$components['dom']->addJavaScript();

		$components['dragdrop'] = new Zap_YUIComponent('dragdrop');
		$components['dragdrop']->addJavaScript();

		$components['editor'] = new Zap_YUIComponent('editor');
		$components['editor']->addJavaScript();
		$components['editor']->addStyleSheet('editor/assets/skins/sam', '',
			false);

		$components['simpleeditor'] = new Zap_YUIComponent('simpleeditor');
		$components['simpleeditor']->addJavaScript('editor');
		$components['simpleeditor']->addStyleSheet('editor/assets/skins/sam',
			'', false);

		$components['element'] = new Zap_YUIComponent('element');
		$components['element']->addJavaScript();

		$components['event'] = new Zap_YUIComponent('event');
		$components['event']->addJavaScript();

		$components['fonts'] = new Zap_YUIComponent('fonts');
		$components['fonts']->addStyleSheet();

		$components['grids'] = new Zap_YUIComponent('grids');
		$components['grids']->addStyleSheet();

		$components['imagecropper'] = new Zap_YUIComponent('imagecropper');
		$components['imagecropper']->addJavaScript();
		$components['imagecropper']->addStyleSheet(
			'imagecropper/assets/skins/sam', '', false);

		$components['json'] = new Zap_YUIComponent('json');
		$components['json']->addJavaScript();

		$components['logger'] = new Zap_YUIComponent('logger');
		$components['logger']->addJavaScript();

		$components['menu'] = new Zap_YUIComponent('menu');
		$components['menu']->addJavaScript();
		$components['menu']->addStyleSheet('menu/assets/skins/sam', '', false);

		$components['paginator'] = new Zap_YUIComponent('paginator');
		$components['paginator']->addJavaScript();
		$components['paginator']->addStyleSheet(
			'paginator/assets/skins/sam', '', false);

		$components['reset-fonts-grids'] = new Zap_YUIComponent('reset-fonts-grids');
		$components['reset-fonts-grids']->addStyleSheet('', '', false);

		$components['reset'] = new Zap_YUIComponent('reset');
		$components['reset']->addStyleSheet();

		$components['resize'] = new Zap_YUIComponent('resize');
		$components['resize']->addJavaScript();
		$components['resize']->addStyleSheet(
			'resize/assets/skins/sam', '', false);

		$components['slider'] = new Zap_YUIComponent('slider');
		$components['slider']->addJavaScript();

		$components['stylesheet'] = new Zap_YUIComponent('stylesheet');
		$components['stylesheet']->addJavaScript();

		$components['swf'] = new Zap_YUIComponent('swf');
		$components['swf']->addJavaScript();

		$components['tabview'] = new Zap_YUIComponent('tabview');
		$components['tabview']->addJavaScript();
		$components['tabview']->addStyleSheet(
			'tabview/assets/skins/sam', '', false);

		$components['treeview'] = new Zap_YUIComponent('treeview');
		$components['treeview']->addJavaScript();

		$components['yahoo'] = new Zap_YUIComponent('yahoo');
		$components['yahoo']->addJavaScript();

		// dependencies
		$components['animation']->addDependency($components['yahoo']);
		$components['animation']->addDependency($components['dom']);
		$components['animation']->addDependency($components['event']);

		$components['autocomplete']->addDependency($components['yahoo']);
		$components['autocomplete']->addDependency($components['dom']);
		$components['autocomplete']->addDependency($components['event']);
		$components['autocomplete']->addDependency($components['connection']);
		$components['autocomplete']->addDependency($components['animation']);
		$components['autocomplete']->addDependency($components['datasource']);

		$components['button']->addDependency($components['yahoo']);
		$components['button']->addDependency($components['dom']);
		$components['button']->addDependency($components['event']);
		$components['button']->addDependency($components['element']);
		$components['button']->addDependency($components['container_core']);
		$components['button']->addDependency($components['menu']);

		$components['calendar']->addDependency($components['yahoo']);
		$components['calendar']->addDependency($components['dom']);
		$components['calendar']->addDependency($components['event']);

		$components['charts']->addDependency($components['yahoo']);
		$components['charts']->addDependency($components['dom']);
		$components['charts']->addDependency($components['event']);
		$components['charts']->addDependency($components['element']);
		$components['charts']->addDependency($components['datasource']);
		$components['charts']->addDependency($components['json']);
		$components['charts']->addDependency($components['swf']);

		$components['connection']->addDependency($components['yahoo']);
		$components['connection']->addDependency($components['event']);

		$components['container']->addDependency($components['yahoo']);
		$components['container']->addDependency($components['dom']);
		$components['container']->addDependency($components['event']);
		$components['container']->addDependency($components['connection']);
		$components['container']->addDependency($components['animation']);

		$components['container_core']->addDependency($components['yahoo']);
		$components['container_core']->addDependency($components['dom']);
		$components['container_core']->addDependency($components['event']);
		$components['container_core']->addDependency($components['connection']);
		$components['container_core']->addDependency($components['animation']);

		$components['datatable']->addDependency($components['yahoo']);
		$components['datatable']->addDependency($components['datasource']);
		$components['datatable']->addDependency($components['dom']);
		$components['datatable']->addDependency($components['dragdrop']);
		$components['datatable']->addDependency($components['event']);
		$components['datatable']->addDependency($components['element']);
		$components['datatable']->addDependency($components['paginator']);

		$components['dom']->addDependency($components['yahoo']);

		$components['dragdrop']->addDependency($components['yahoo']);
		$components['dragdrop']->addDependency($components['dom']);
		$components['dragdrop']->addDependency($components['event']);

		$components['editor']->addDependency($components['yahoo']);
		$components['editor']->addDependency($components['dom']);
		$components['editor']->addDependency($components['event']);
		$components['editor']->addDependency($components['element']);
		$components['editor']->addDependency($components['button']);

		$components['simpleeditor']->addDependency($components['yahoo']);
		$components['simpleeditor']->addDependency($components['dom']);
		$components['simpleeditor']->addDependency($components['event']);
		$components['simpleeditor']->addDependency($components['element']);

		$components['element']->addDependency($components['yahoo']);
		$components['element']->addDependency($components['dom']);
		$components['element']->addDependency($components['event']);

		$components['event']->addDependency($components['yahoo']);

		$components['grids']->addDependency($components['fonts']);

		$components['imagecropper']->addDependency($components['yahoo']);
		$components['imagecropper']->addDependency($components['dom']);
		$components['imagecropper']->addDependency($components['event']);
		$components['imagecropper']->addDependency($components['dragdrop']);
		$components['imagecropper']->addDependency($components['element']);
		$components['imagecropper']->addDependency($components['resize']);

		$components['json']->addDependency($components['yahoo']);

		$components['logger']->addDependency($components['yahoo']);
		$components['logger']->addDependency($components['dom']);
		$components['logger']->addDependency($components['event']);
		$components['logger']->addDependency($components['dragdrop']);

		$components['menu']->addDependency($components['yahoo']);
		$components['menu']->addDependency($components['dom']);
		$components['menu']->addDependency($components['event']);
		$components['menu']->addDependency($components['container_core']);

		$components['paginator']->addDependency($components['yahoo']);
		$components['paginator']->addDependency($components['dom']);
		$components['paginator']->addDependency($components['event']);
		$components['paginator']->addDependency($components['element']);

		$components['resize']->addDependency($components['yahoo']);
		$components['resize']->addDependency($components['dom']);
		$components['resize']->addDependency($components['event']);
		$components['resize']->addDependency($components['dragdrop']);
		$components['resize']->addDependency($components['element']);

		$components['slider']->addDependency($components['yahoo']);
		$components['slider']->addDependency($components['dom']);
		$components['slider']->addDependency($components['event']);
		$components['slider']->addDependency($components['dragdrop']);

		$components['stylesheet']->addDependency($components['yahoo']);

		$components['swf']->addDependency($components['yahoo']);
		$components['swf']->addDependency($components['dom']);
		$components['swf']->addDependency($components['event']);
		$components['swf']->addDependency($components['element']);

		$components['tabview']->addDependency($components['yahoo']);
		$components['tabview']->addDependency($components['dom']);
		$components['tabview']->addDependency($components['event']);
		$components['tabview']->addDependency($components['element']);

		$components['treeview']->addDependency($components['yahoo']);

		self::$_components = $components;

		$componentsBuilt = true;
	}
}


