<?php

require_once 'Zap/Object.php';
require_once 'Zap/String.php';

/**
 * Stores and outputs an HTML tag
 *
 * @package   Zap
 * @copyright 2004-2006 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_HtmlTag extends Zap_Object
{

    /**
     * The name of the HTML tag
     *
     * @var string
     */
    private $_tagName;

    /**
     * Atribute array
     *
     * Array containing attributes of the HTML tag in the form:
     *     attribute_name => value
     *
     * @var array
     */
    private $_attributes = array();

    /**
     * Optional content for the body of the XHTML tag
     *
     * @var string
     */
    private $_content = null;

    /**
     * Optional content type for the body of the XHTML tag
     *     default text/plain
     *     use text/xml for XHTML fragments
     *
     * @var string
     */
    private $_content_type = 'text/plain';

    /**
     * Creates a new HTML tag
     *
     * @param string $tagName    The name of the HTML tag.
     * @param array  $attributes An optional array of attributes in the form:
     *                           attribute => value
     */
    public function __construct($tagName, $attributes = null)
    {
        $this->_tagName = $tagName;

        if (is_array($attributes)) {
            $this->_attributes = $attributes;
        }
    }

    /**
     * Set content for the body of the XHTML tag
     *
     * This property is a UTF-8 encoded XHTML fragment. It is not escaped
     * before display so the user of Zap_HtmlTag is responsible for any
     * escaping that must occur.
     *
     * When this value is set {@link Zap_HtmlTag::display()} displays this
     * content after displaying the opening tag. Then it displays an explicit
     * closing tag.
     *
     * @param string $content Content for the body of the XHTML tag
     * @param string $type    Mime type of the content.  Default is 'text/plain',
     *                        use 'text/xml' for XHTML fragments.
     * 
     * @return void
     */
    public function setContent($content, $type = 'text/plain')
    {
        $this->_content = $content;
        $this->_content_type = $type;
    }

    /**
     * Adds an array of attributes to this XHTML tag
     *
     * If entries in the attributes array coincide with existing attributes of
     * this XHTML tag, the attributes in the array overwrite the existing
     * attributes.
     *
     * @param array $attributes An array of attribute-value pairs of the form
     *                          'attribute' => 'value'.
     * 
     * @return void
     */
    public function addAttributes($attributes)
    {
        if (is_array($attributes)) {
            $this->_attributes = array_merge($this->_attributes, $attributes);
        }
    }

    /**
     * Removes an attribute
     *
     * Removes a previously assigned attribute. Useful when one tag object is
     * displayed multiple times with different attributes.
     *
     * @param string $attribute The name of attribute to remove.
     *
     * @return void
     */
    public function removeAttribute($attribute)
    {
        unset($this->_attributes[$attribute]);
    }

    /**
     * Displays this tag
     *
     * Output the opening tag including all its attributes and implicitly
     * close the tag. If explicit closing is desired, use
     * {@link Zap_HtmlTag::open()} and {@link Zap_HtmlTag::close()} instead.
     * If {@link Zap_HtmlTag::content} is set then the content is displayed
     * between an opening and closing tag, otherwise a self-closing tag is
     * displayed.
     *
     * @see Zap_HtmlTag::open()
     *
     * @return void
     */
    public function display()
    {
        if ($this->_content === null) {
            $this->_openInternal(true);
        } else {
            $this->_openInternal(false);
            $this->displayContent();
            $this->close();
        }
    }

    /**
     * Displays the content of this tag
     *
     * If {@link Zap_HtmlTag::content} is set then the content is displayed.
     *
     * @see Zap_HtmlTag::display()
     *
     * @return void
     */
    public function displayContent()
    {
        if ($this->_content !== null) {
            if ($this->_content_type === 'text/plain') {
                echo Zap_String::minimizeEntities($this->_content);
            } else {
                echo $this->_content;
            }
        }
    }

    /**
     * Opens this tag
     *
     * Outputs the opening tag including all its attributes. Should be paired
     * with a call to {@link Zap_HtmlTag::close()}. If implicit closing
     * is desired, use {@link Zap_HtmlTag::display()} instead.
     *
     * @see Zap_HtmlTag::close()
     *
     * @return void
     */
    public function open()
    {
        $this->_openInternal(false);
    }

    /**
     * Closes this tag
     *
     * Outputs the closing tag. Should be paired with a call to
     * {@link Zap_HtmlTag::open()}.
     *
     * @see Zap_HtmlTag::open()
     *
     * @return void
     */
    public function close()
    {
        echo '</', $this->_tagName, '>';
    }

    /**
     * Gets this tag as a string
     *
     * The string is the same as the displayed content of
     * {@link Zap_HtmlString::display()}. It is not possible to get paired tags
     * as strings as this object has no knowledge of what is displayed between
     * the opening and closing tags.
     *
     * @see Zap_HtmlTag::display()
     *
     * @return string this tag as a string.
     */
    public function toString()
    {
        ob_start();
        $this->display();
        return ob_get_clean();
    }

    /**
     * Magic __get method
     *
     * This should never be called directly, but is invoked indirectly when
     * accessing properties of a tag object.
     *
     * @param string $attribute the name of attribute to get.
     *
     * @return mixed The value of the attribute. Null is returned if the
     *               attribute is not set.
     */
    public function __get($attribute)
    {
        if (isset($this->_attributes[$attribute])) {
            return $this->_attributes[$attribute];
        } else {
            return null;
        }
    }

    /**
     * Magic __set method
     *
     * This should never be called directly, but is invoked indirectly when
     * setting properties of a tag object.
     *
     * @param string $attribute The name of attribute.
     * @param mixed  $value     The value of attribute.
     *
     * @return void
     */
    public function __set($attribute, $value)
    {
        $this->_attributes[$attribute]
            = ($value === null) ? null : (string)$value;
    }

    /**
     * Gets this tag as a string
     *
     * This is a magic method that is called by PHP when this object is used
     * in string context. For example:
     *
     * <code>
     * $img = new Zap_HtmlTag('img');
     * $img->alt = 'example image';
     * $img->src = 'http://example.com/example.png';
     * echo $img;
     * </code>
     *
     * Note: It is more efficient to simply call {@link Zap_HtmlTag::display()}
     * instead of using <code>echo $tag;</code>.
     *
     * @return string This tag as a string.
     *
     * @see Zap_HtmlTag::toString()
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Outputs opening tag and all attributes
     *
     * This is a helper method that does the attribute displaying when opening
     * this tag. This method can also display self-closing XHTML tags.
     *
     * @param boolean $self_closing Whether this tag should be displayed as a
     *                              self-closing tag.
     *
     * @return void
     */
    private function _openInternal($self_closing = false)
    {
        echo '<', $this->_tagName;

        foreach ($this->_attributes as $attribute => $value) {
            if ($value !== null) {
                echo ' ', $attribute, '="',
                    Zap_String::minimizeEntities($value), '"';
            }
        }

        if ($self_closing) {
            echo ' />';
        } else {
            echo '>';
        }
    }
}