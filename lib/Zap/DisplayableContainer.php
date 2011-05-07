<?php

/**
 * Base class for containers that display an XHTML element
 *
 * @package   Zap
 * @copyright 2006 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_DisplayableContainer extends Zap_Container
{
    /**
     * Displays this container
     *
     * @return null
     */
    public function display()
    {
        if (! $this->_visible) {
            return;
        }

        Zap_Widget::display();

        $div = new Zap_HtmlTag('div');
        $div->id    = $this->_id;
        $div->class = $this->_getCSSClassString();

        $div->open();
        $this->_displayChildren();
        $div->close();
    }

    /**
     * Gets the array of CSS classes that are applied to this displayable
     * container
     *
     * @return array the array of CSS classes that are applied to this
     *                displayable container.
     */
    protected function _getCSSClassNames()
    {
        $classes = array('swat-displayable-container');
        $classes = array_merge($classes, parent::_getCSSClassNames());
        return $classes;
    }
}

