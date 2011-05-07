<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/HtmlHeadEntry.php';
require_once 'Zap/HtmlTag.php';

/**
 * Stores and outputs an HTML head entry for an XHTML link element
 *
 * @package   Zap
 * @copyright 2008 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_LinkHtmlHeadEntry extends Zap_HtmlHeadEntry
{
	// {{{ protected properties

	/**
	 * The URI linked to by this link
	 *
	 * @var string
	 */
	protected $link_uri;

	/**
	 * The title of this link
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * How this link relates to the containing document
	 *
	 * @var string
	 */
	protected $relationship;

	/**
	 * The media type of the content linked to by this link
	 *
	 * @var string
	 */
	protected $type;

	// }}}
	// {{{ public function __construct()

	/**
	 * Creates a new link HTML head entry
	 *
	 * @param string $uri the URI linked to by this link.
	 * @param string $relationship optional. How this link relates to the
	 *                              containing document.
	 * @param string $type optional. The media type of the content linked to by
	 *                      this link.
	 * @param string $title optional. The title of this link.
	 *
	 * @param integer $package_id the package id of the package this HTML head
	 *                             entry belongs to.
	 */
	public function __construct($uri, $relationship = null, $type = null,
		$title = null, $package_id = null)
	{
		$hash = md5($uri.$relationship.$type.$title);
		parent::__construct($hash, $package_id);

		$this->link_uri = $uri;
		$this->relationship = $relationship;
		$this->type = $type;
		$this->title = $title;
	}

	// }}}
	// {{{ public function display()

	public function display($uri_prefix = '', $tag = null)
	{
		$link = new SwatHtmlTag('link');
		$link->title = $this->title;
		$link->rel = $this->relationship;
		$link->type = $this->type;
		$link->href = $this->link_uri;
		$link->display();
	}

	// }}}
	// {{{ public function displayInline()

	public function displayInline($path)
	{
		$this->display();
	}

	// }}}
}


