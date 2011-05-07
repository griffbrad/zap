<?php

/**
 * A container with a decorative frame and optional title
 *
 * @package   Zap
 * @copyright 2004-2010 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_Frame extends Zap_DisplayableContainer implements Zap_Titleable
{
    /**
     * A visible title for this frame, or null
     *
     * @var string
     */
    protected $_title = null;

    /**
     * An optional visible subtitle for this frame, or null
     *
     * @var string
     */
    protected $_subtitle = null;

    /**
     * An optional string to separate subtitle from the title
     *
     * @var string
     */
    protected $_titleSeparator = ': ';

    /**
     * Optional content type for the title
     *
     * Default text/plain, use text/xml for XHTML fragments.
     *
     * @var string
     */
    protected $_titleContentType = 'text/plain';

    /**
     * Optional header level for the title
     *
     * Setting this will override the automatic heading level calculation
     * based on nesting of frames.
     *
     * @var integer
     */
    protected $_headerLevel;

    /**
     * Set the title for the frame
     *
     * @param string $title Title to be displayed for the frame
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->_title = $title;

        return $this;
    }

    /**
     * Gets the title of this frame
     *
     * Implements the {@link Zap_Titleable::getTitle()} interface.
     *
     * @return string the title of this frame.
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Set the sub-title for the frame.  The sub-title is displayed after
     * the title and the title separator.
     *
     * @param string $subtitle Sub-title to be displayed for the frame
     *
     * @return $this
     */
    public function setSubtitle($subtitle)
    {
        $this->_subtitle = $subtitle;

        return $this;
    }

    /**
     * Retrieve the frame's subtitle
     *
     * @return string
     */
    public function getSubtitle()
    {
        return $this->_subtitle;
    }

    /**
     * Set the separator used to divide the title and the subtitle
     *
     * @param string $titleSeparator String used to divide title
     *
     * @return $this
     */
    public function setTitleSeparator($titleSeparator)
    {
        $this->_titleSeparator = $titleSeparator;

        return $this;
    }

    /**
     * Retreive the title separator
     *
     * @return string
     */
    public function getTitleSeparator()
    {
        return $this->_titleSeparator;
    }

    /**
     * Set the content type to use when displaying the title.  text/plain
     * to escape before display, text/xml to allow HTML.
     *
     * @param string $titleContentType The content type to use for the title
     *
     * @return $this
     */
    public function setTitleContentType($titleContentType)
    {
        $this->_titleContentType = $titleContentType;

        return $this;
    }

    /**
     * Gets the title content-type of this frame
     *
     * Implements the {@link Zap_Titleable::getTitleContentType()} interface.
     *
     * @return string the title content-type of this frame.
     */
    public function getTitleContentType()
    {
        return $this->_titleContentType;
    }

    /**
     * The header level to use when displaying the title of the frame. Should
     * be between 1 and 6 to conform to HTML standards.
     *
     * @param integer $headerLevel Level for <hX> tag in title
     *
     * @return $this
     */
    public function setHeaderLevel($headerLevel)
    {
        $this->_headerLevel = $headerLevel;

        return $this;
    }

    /**
     * Get the header level for the frame title
     *
     * @return integer
     */
    public function getHeaderLevel()
    {
        return $this->_headerLevel;
    }

    /**
     * Displays this frame
     *
     * @return null
     */
    public function display()
    {
        if (! $this->_visible) {
            return;
        }

        Zap_Widget::display();

        $outerDiv = new Zap_HtmlTag('div');
        $outerDiv->id    = $this->_id;
        $outerDiv->class = $this->_getCSSClassString();

        $outerDiv->open();
        $this->_displayTitle();
        $this->_displayContent();
        $outerDiv->close();
    }

    /**
     * Displays this frame's title
     *
     * @return null
     */
    protected function _displayTitle()
    {
        if (null !== $this->_title) {
            $headerTag = new Zap_HtmlTag('h'.$this->_getHeaderLevel());
            $headerTag->class = 'swat-frame-title';
            $headerTag->setContent($this->_title, $this->_titleContentType);

            if (null === $this->_subtitle) {
                $headerTag->display();
            } else {
                $spanTag = new Zap_HtmlTag('span');
                $spanTag->class = 'swat-frame-subtitle';
                $spanTag->setContent(
                    $this->_subtitle,
                    $this->_titleContentType
                );

                $headerTag->open();
                $headerTag->displayContent();
                echo $this->_titleSeparator;
                $spanTag->display();
                $headerTag->close();
            }
        }
    }

    /**
     * Displays this frame's content
     *
     * @return null
     */
    protected function _displayContent()
    {
        $innerDiv = new Zap_HtmlTag('div');
        $innerDiv->class = 'swat-frame-contents';
        $innerDiv->open();
        $this->_displayChildren();
        $innerDiv->close();
    }

    /**
     * Gets the array of CSS classes that are applied to this frame
     *
     * @return array the array of CSS classes that are applied to this frame.
     */
    protected function _getCSSClassNames()
    {
        $classes = array('swat-frame');
        $classes = array_merge($classes, parent::_getCSSClassNames());
        return $classes;
    }

    /**
     * Get the header level to be used when displaying the frame's title.
     * If this isn't set manually, the frame's parent widgets will be examined
     * to auto-set the header level.  If this frame is nested in another, this
     * frame's header level will be raised to reflect that hierarchy.
     *
     * @return integer The level (between 1 and 6) for the <hX> tag of the title
     */
    protected function _getHeaderLevel()
    {
        // default header level is h2
        $level = 2;

        if (null !== $this->_headerLevel) {
            $level = $this->_headerLevel;
        } else {
            $ancestor = $this->_parent;

            // get appropriate header level, limit to h6
            while (null !== $ancestor) {
                if ($ancestor instanceof Zap_Frame) {
                    $level = $ancestor->_getHeaderLevel() + 1;
                    $level = min($level, 6);
                    break;
                }

                $ancestor = $ancestor->getParent();
            }
        }

        return $level;
    }
}


