<?php

/**
 * A container to use around control widgets in a form
 *
 * Adds a label and space to output messages.
 *
 * @package   Zap
 * @copyright 2004-2010 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_FormField extends Zap_DisplayableContainer implements Zap_Titleable
{
    const DISPLAY_REQUIRED = 1;

    const DISPLAY_OPTIONAL = 2;
    
    /**
     * The visible name for this field, or null
     *
     * @var string
     */
    protected $_title = null;

    /**
     * Optional content type for the title
     *
     * Default text/plain, use text/xml for XHTML fragments.
     *
     * @var string
     */
    protected $_titleContentType = 'text/plain';

    /**
     * Display a visible indication that this field is required
     *
     * @var boolean
     */
    protected $_required = false;

    /**
     * Display the required status of this field
     *
     * Bitwise display options:
     * Zap_FormField::DISPLAY_REQUIRED = display "required" if this field
     *                                   is required
     * Zap_FormField::DISPLAY_OPTIONAL = display "optional" if this field
     *                                   is not required
     *
     * @var integer
     */
    protected $_requiredStatusDisplay = self::DISPLAY_REQUIRED;

    /**
     * Optional note of text to display with the field
     *
     * @var string
     */
    protected $_note = null;

    /**
     * Optional content type for the note
     *
     * Default text/plain, use text/xml for XHTML fragments.
     *
     * @var string
     */
    protected $_noteContentType = 'text/plain';

    /**
     * Access key
     *
     * Sets an access key for the label of this form field, if one exists.
     *
     * @var string
     */
    protected $_accessKey = null;

    /**
     * Whether or not to show a colon after the title of this form field
     *
     * By default, a colon is shown.
     *
     * @var boolean
     */
    protected $_showColon = true;

    /*
     * Display the title of the form field after the widget code
     *
     * This is automatically set for some widget types, but defaults to null
     * (which we treat the same as false) to allow the value to be manually set
     * for said widgets.
     *
     * @var boolean
     */
    protected $_titleReversed = null;

    /**
     * Whether or not to display validation messages in this form field
     *
     * Defaults to true. Set to false to prevent the displaying of messages in
     * this form field.
     *
     * @var boolean
     */
    protected $_displayMessages = true;

    /**
     * Container tag to use
     *
     * Subclasses can change this to change their appearance.
     *
     * @var string
     */
    protected $_containerTag = 'div';

    /**
     * Contents tag to use
     *
     * Subclasses can change this to change their appearance.
     *
     * @var string
     */
    protected $_contentsTag = 'div';

    /**
     * A CSS class name set by the subwidgets in this form field
     *
     * @var string
     *
     * @see Zap_FormField::notifyOfAdd()
     */
    protected $_widgetClass;

    /**
     * Creates a new form field
     *
     * @param string $id a non-visible unique id for this widget.
     *
     * @see Zap_Widget::__construct()
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->addStyleSheet(
            'packages/swat/styles/swat-message.css',
            Zap::PACKAGE_ID
        );
    }

    public function setRequired($required)
    {
        $this->_required = true;

        return $this;
    }

    public function setTitle($title)
    {
        $this->_title = $title;

        return $this;
    }

    /**
     * Gets the title of this form field
     *
     * Satisfies the {Zap_Titleable::getTitle()} interface.
     *
     * @return string the title of this form field.
     */
    public function getTitle()
    {
        return $this->_title;
    }

    public function setNote($note)
    {
        $this->_note = $note;

        return $this;
    }

    public function getNote()
    {
        return $this->_note;
    }

    public function setTitleReversed($titleReversed)
    {
        $this->_titleReversed = $titleReversed;

        return $this;
    }

    public function getTitleReversed()
    {
        return $this->_titleReversed;
    }

    public function setRequiredStatusDisplay($status)
    {
        $this->_requiredStatusDisplay = $status;

        return $this;
    }

    public function setShowColon($showColon)
    {
        $this->_showColon = $showColon;

        return $this;
    }

    public function getShowColon()
    {
        return $this->_showColon;
    }

    /**
     * Gets the title content-type of this form field
     *
     * Implements the {@link Zap_Titleable::getTitleContentType()} interface.
     *
     * @return string the title content-type of this form field.
     */
    public function getTitleContentType()
    {
        return $this->title_content_type;
    }

    /**
     * Displays this form field
     *
     * Associates a label with the first widget of this container.
     *
     * @return null
     */
    public function display()
    {
        if (! $this->_visible) {
            return;
        }

        if (null === $this->getFirst()) {
            return;
        }

        Zap_Widget::display();

        $containerTag = new Zap_HtmlTag($this->_containerTag);
        $containerTag->id = $this->_id;
        $containerTag->class = $this->_getCSSClassString();

        $containerTag->open();

        if (true === $this->_titleReversed) {
            $this->_displayContent();
            $this->_displayTitle();
        } else {
            $this->_displayTitle();
            $this->_displayContent();
        }

        $this->_displayMessages();
        $this->_displayNotes();

        $containerTag->close();
    }

    /**
     * Display the title of this form field, if available.
     *
     * @return null
     */
    protected function _displayTitle()
    {
        if (null === $this->_title && null === $this->_accessKey) {
            return;
        }

        $titleTag = $this->_getTitleTag();
        $titleTag->open();
        $titleTag->displayContent();
        $this->_displayRequiredStatus();
        $titleTag->close();
    }

    /**
     * Display whether this field is required or optional, depending on the
     * value of the $_requiredStatusDisplay property.
     *
     * @return null
     */
    protected function _displayRequiredStatus()
    {
        if ($this->_required 
            && $this->_requiredStatusDisplay & self::DISPLAY_REQUIRED
        ) {
            $spanTag = new Zap_HtmlTag('span');
            $spanTag->class = 'swat-required';
            $spanTag->setContent(sprintf(' (%s)', Zap::_('required')));
            $spanTag->display();
        } elseif (
            ! $this->_required &&
            $this->_requiredStatusDisplay & self::DISPLAY_OPTIONAL
        ) {
            $spanTag = new Zap_HtmlTag('span');
            $spanTag->class = 'swat-optional';
            $spanTag->setContent(sprintf(' (%s)', Zap::_('optional')));
            $spanTag->display();
        }
    }

    /**
     * Display this field's children in the contents area of the field.
     *
     * @return null
     */
    protected function _displayContent()
    {
        $contentsTag = new Zap_HtmlTag($this->_contentsTag);
        $contentsTag->class = 'swat-form-field-contents';

        $contentsTag->open();
        $this->_displayChildren();
        $contentsTag->close();
    }

    /**
     * Display any messages associated with this field and the controls 
     * contained in it.
     *
     * @return null
     */
    protected function _displayMessages()
    {
        if (! $this->_displayMessages || ! $this->hasMessage()) {
            return;
        }

        $messages = $this->getMessages();

        $messageUl = new Zap_HtmlTag('ul');
        $messageUl->class = 'swat-form-field-messages';
        $messageLi = new Zap_HtmlTag('li');

        $messageUl->open();

        foreach ($messages as $message) {
            $messageLi->class = $message->getCSSClassString();
            $messageLi->setContent(
                $message->getPrimaryContent(),
                $message->getContentType()
            );

            if (null === $message->getSecondaryContent()) {
                $messageLi->display();
            } else {
                $secondarySpan = new Zap_HtmlTag('span');
                $secondarySpan->setContent(
                    $message->getSecondaryContent(),
                    $message->getContentType()
                );

                $messageLi->open();
                $messageLi->displayContent();
                echo ' ';
                $secondarySpan->display();
                $messageLi->close();
            }
        }

        $messageUl->close();
    }

    /**
     * Display the notes associated with this field and any of the controls
     * contained in it.
     *
     * @return null
     */
    protected function _displayNotes()
    {
        $notes = array();

        if (null !== $this->_note) {
            $note = new Zap_Message($this->_note);
            $note->setContentType($this->_noteContentType);
            $notes[] = $note;
        }

        $control = $this->getFirstDescendant('Zap_Control');

        if (null !== $control) {
            $note = $control->getNote();

            if (null !== $note) {
                $notes[] = $note;
            }
        }

        if (1 === count($notes)) {
            $note = reset($notes);
            $noteDiv = new Zap_HtmlTag('div');
            $noteDiv->class = 'swat-note';
            $noteDiv->setContent(
                $note->getPrimaryContent(), 
                $note->getContentType()
            );
            $noteDiv->display();
        } elseif (1 < count($notes)) {
            $noteList = new Zap_HtmlTag('ul');
            $noteList->class = 'swat-note';
            $noteList->open();

            $liTag = new Zap_HtmlTag('li');

            foreach ($notes as $note) {
                $liTag->setContent(
                    $note->getPrimaryContent(),
                    $note->getContentType()
                );

                $liTag->display();
            }

            $noteList->close();
        }
    }

    /**
     * Gets the array of CSS classes that are applied to this form field
     *
     * @return array the array of CSS classes that are applied to this form
     *                field.
     */
    protected function _getCSSClassNames()
    {
        $classes = array('swat-form-field');

        if (null !== $this->_widgetClass) {
            $classes[] = $this->_widgetClass;
        }

        if ($this->_displayMessages && $this->hasMessage()) {
            $classes[] = 'swat-form-field-with-messages';
        }

        if ($this->_required) {
            $classes[] = 'swat-required';
        }

        $classes = array_merge($classes, parent::_getCSSClassNames());

        return $classes;
    }

    /**
     * Get a Zap_HtmlTag to display the title
     *
     * Subclasses can change this to change their appearance.
     *
     * @return Zap_HtmlTag a tag object containing the title.
     */
    protected function _getTitleTag()
    {
        $labelTag = new Zap_HtmlTag('label');

        if (null !== $this->_title) {
            if (! $this->_showColon) {
                $labelTag->setContent($this->_title, $this->_titleContentType);
            } else {
                $labelTag->setContent(
                    sprintf(Zap::_('%s: '), $this->_title),
                    $this->_titleContentType
                );
            }
        }

        $labelTag->for = $this->getFocusableHtmlId();
        $labelTag->accesskey = $this->_accessKey;

        return $labelTag;
    }

    /**
     * Notifies this widget that a widget was added
     *
     * This sets class propertes on this form field when certain classes of
     * widgets are added.
     *
     * @param Zap_Widget $widget the widget that has been added.
     *
     * @see Zap_Container::notifyOfAdd()
     *
     * @return null
     */
    protected function _notifyOfAdd($widget)
    {
        if (class_exists('Zap_Checkbox') && $widget instanceof Zap_Checkbox) {
            $this->_widgetClass = 'swat-form-field-checkbox';

            // Don't set these properties if title_reversed is explicitly set
            if (null  === $this->_titleReversed) {
                $this->_titleReversed = true;
                $this->_showColon     = false;
            }
        } elseif (
            class_exists('Zap_SearchEntry') &&
            $widget instanceof Zap_SearchEntry
        ) {
            $this->_showColon = false;
        }
    }
}


