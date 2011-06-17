<?php

require_once 'Zap/Object.php';
require_once 'Zap/HtmlHeadEntry.php';
require_once 'Zap/HtmlHeadEntrySet.php';
require_once 'Zap/CommentHtmlHeadEntry.php';
require_once 'Zap/JavaScriptHtmlHeadEntry.php';
require_once 'Zap/StyleSheetHtmlHeadEntry.php';

/**
 * A base class for Zap user-interface elements
 *
 * TODO: describe our conventions on how CSS classes and XHTML ids are
 * displayed.
 *
 * @package   Zap
 * @copyright 2006-2009 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
abstract class Zap_UIObject extends Zap_Object
{
    /**
     * The object which contains this object
     *
     * @var Zap_UIObject
     */
    protected $_parent = null;

    /**
     * Visible
     *
     * Whether this UI object is displayed. All UI objects should respect this.
     *
     * @var boolean
     *
     * @see Zap_UIObject::isVisible()
     */
    protected $_visible = true;

    /**
     * A user-specified array of CSS classes that are applied to this
     * user-interface object
     *
     * See the class-level documentation for ZapUIObject for details on how
     * CSS classes and XHTML ids are displayed on user-interface objects.
     *
     * @var array
     */
    protected $_classes = array();

    /**
     * A set of HTML head entries needed by this user-interface element
     *
     * Entries are stored in a data object called {@link Swat_HtmlHeadEntry}.
     * This property contains a set of such objects.
     *
     * @var Zap_tmlHeadEntrySet
     */
    protected $_htmlHeadEntrySet;

    public function __construct()
    {
        $this->_htmlHeadEntrySet = new Zap_HtmlHeadEntrySet();
    }

    public function setVisible($visible)
    {
        $this->_visible = $visible;

        return $this;
    }

    public function getVisible()
    {
        return $this->_visible;
    }

    public function setParent(Zap_UIObject $parent)
    {
        $this->_parent = $parent;

        return $this;
    }

    public function getParent()
    {
        return $this->_parent;
    }

    public function addClass($class)
    {
        $this->_classes[] = $class;

        return $this;
    }
    
    /**
     * Adds a stylesheet to the list of stylesheets needed by this
     * user-iterface element
     *
     * @param string  $stylesheet The uri of the style sheet.
     * @param integer $packageId  Unknown.
     *
     * @return void
     */
    public function addStyleSheet($stylesheet, $packageId = null)
    {
        if (null === $this->_htmlHeadEntrySet) {
            throw new Zap_Exception(
                sprintf(
                    "Child class '%s' did not ".
                    'instantiate a HTML head entry set. This should be done in  '.
                    'the constructor either by calling parent::__construct() or '.
                    'by creating a new HTML head entry set.', get_class($this)
                )
            );
        }

        $this->_htmlHeadEntrySet->addEntry(
            new Zap_StyleSheetHtmlHeadEntry($stylesheet, $packageId)
        );
    }

    /**
     * Adds a JavaScript include to the list of JavaScript includes needed
     * by this user-interface element
     *
     * @param string  $javaScript The uri of the JavaScript include.
     * @param integer $packageId  Unknown.
     *
     * @return void
     */
    public function addJavaScript($javaScript, $packageId = null)
    {
        if (null === $this->_htmlHeadEntrySet) {
            throw new Zap_Exception(
                sprintf(
                    "Child class '%s' did not ".
                    'instantiate a HTML head entry set. This should be done in  '.
                    'the constructor either by calling parent::__construct() or '.
                    'by creating a new HTML head entry set.', get_class($this)
                )
            );
        }

        $this->_htmlHeadEntrySet->addEntry(
            new Zap_JavaScriptHtmlHeadEntry($javaScript, $packageId)
        );
    }

    /**
     * Adds a comment to the list of HTML head entries needed by this user-
     * interface element
     *
     * @param string  $comment   The contents of the comment to include.
     * @param integer $packageId The package this comment belongs with.
     *
     * @return void
     */
    public function addComment($comment, $packageId = null)
    {
        if (null === $this->_htmlHeadEntrySet) {
            throw new Zap_Exception(
                sprintf(
                    "Child class '%s' did not ".
                    'instantiate a HTML head entry set. This should be done in  '.
                    'the constructor either by calling parent::__construct() or '.
                    'by creating a new HTML head entry set.', get_class($this)
                )
            );
        }

        $this->_htmlHeadEntrySet->addEntry(
            new SwatCommentHtmlHeadEntry($comment, $packageId)
        );
    }

    /**
     * Convenience method to add Tango attribution comment
     *
     * Note: The Tango icons are now public domain and no attribution is
     * needed. This method remains for backwards compatibility.
     *
     * @param integer $package_id The package the tango attribution belongs
     *                            with.
     *
     * @deprecated The Tango icons are now public domain and no attribution is
     *             needed. This method remains for backwards compatibility.
     *
     * @return void
     */
    public function addTangoAttribution($package_id = Zap::PACKAGE_ID)
    {
    }

    /**
     * Gets the first ancestor object of a specific class
     *
     * Retrieves the first ancestor object in the parent path that is a
     * descendant of the specified class name.
     *
     * @param string $class_name The class name to look for.
     *
     * @return mixed The first ancestor object or null if no matching ancestor
     *               is found.
     *
     * @see Zap_UIParent::getFirstDescendant()
     */
    public function getFirstAncestor($class_name)
    {
        if (! class_exists($class_name)) {
            return null;
        }

        if (null === $this->_parent) {
            $out = null;
        } elseif ($this->_parent instanceof $class_name) {
            $out = $this->_parent;
        } else {
            $out = $this->_parent->getFirstAncestor($class_name);
        }

        return $out;
    }

    /**
     * Gets the SwatHtmlHeadEntry objects needed by this UI object
     *
     * If this UI object is not visible, an empty set is returned to reduce
     * the number of required HTTP requests.
     *
     * @return SwatHtmlHeadEntrySet The SwatHtmlHeadEntry objects needed by
     *                              this UI object.
     */
    public function getHtmlHeadEntrySet()
    {
        if ($this->isVisible()) {
            $set = new Zap_HtmlHeadEntrySet($this->html_head_entry_set);
        } else {
            $set = new Zap_HtmlHeadEntrySet();
        }

        return $set;
    }

    /**
     * Gets whether or not this UI object is visible
     *
     * Looks at the visible property of the ancestors of this UI object to
     * determine if this UI object is visible.
     *
     * @return boolean True if this UI object is visible and false if it is not.
     *
     * @see SwatUIObject::$visible
     */
    public function isVisible()
    {
        if ($this->parent instanceof Zap_UIObject) {
            return ($this->parent->isVisible() && $this->visible);
        } else {
            return $this->visible;
        }
    }

    /**
     * Gets this object as a string
     *
     * @see SwatObject::__toString()
     * @return string This object represented as a string.
     */
    public function __toString()
    {
        // prevent recursion up the widget tree for UI objects
        $parent = $this->parent;
        $this->parent = get_class($parent);

        return parent::__toString();

        // set parent back again
        $this->parent = $parent;
    }

    /**
     * Performs a deep copy of the UI tree starting with this UI object
     *
     * To perform a shallow copy, use PHP's clone keyword.
     *
     * @param string $id_suffix  Optional. A suffix to append to copied UI
     *                           objects in the UI tree. This can be used to
     *                           ensure object ids are unique for a copied UI
     *                           tree. If not specified, UI objects in the
     *                           returned copy will have identical ids to the
     *                           original tree. This can cause problems if both
     *                           the original and copy are displayed during the
     *                           same request.
     *
     * @return SwatUIObject  A deep copy of the UI tree starting with this UI
     *                       object. The returned UI object does not have a
     *                       parent and can be inserted into another UI tree.
     */
    public function copy($id_suffix = '')
    {
        $copy = clone $this;
        $copy->parent = null;
        return $copy;
    }

    /**
     * Gets the array of CSS classes that are applied to this user-interface
     * object
     *
     * User-interface objects aggregate the list of user-specified classes and
     * may add static CSS classes of their own in this method.
     *
     * @return array  The array of CSS classes that are applied to this
     *                user-interface object.
     *
     * @see SwatUIObject::getCSSClassString()
     */
    protected function _getCSSClassNames()
    {
        return $this->_classes;
    }

    /**
     * Gets inline JavaScript used by this user-interface object
     *
     * @return string Inline JavaScript used by this user-interface object.
     */
    protected function _getInlineJavaScript()
    {
        return '';
    }

    /**
     * Gets the string representation of this user-interface object's list of
     * CSS classes
     *
     * @return string  The string representation of the CSS classes that are
     *                 applied to this user-interface object. If this object
     *                 has no CSS classes, null is returned rather than a blank
     *                 string.
     *
     * @see SwatUIObject::getCSSClassNames()
     */
    protected final function _getCSSClassString()
    {
        $classString = null;
        $classNames  = $this->_getCSSClassNames();
        
        if (0 < count($classNames)) {
            $classString = implode(' ', $classNames);
        }

        return $classString;
    }

    /**
     * Generates a unique id for this UI object
     *
     * Gets a unique id that may be used for the id property of this UI object.
     * Each time this method id called, a new unique identifier is generated so
     * you should only call this method once and set it to a property of this
     * object.
     *
     * @return string A unique identifier for this UI object.
     */
    protected final function _getUniqueId()
    {
        // Because this method is not static, this counter will start at zero
        // for each class.
        static $counter = 0;

        $counter++;

        return get_class($this).$counter;
    }
}