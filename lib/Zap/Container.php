<?php

require_once 'Zap/Widget.php';
require_once 'Zap/UIParent.php';

/**
 * Zap container widget
 *
 * Used as a base class for widgets which contain other widgets.
 *
 * @package   Zap
 * @copyright 2004-2006 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_Container extends Zap_Widget implements Zap_UIParent
{
    /**
     * Children widgets
     *
     * An array containing the widgets that belong to this container.
     *
     * @var array
     */
    protected $_children = array();

    /**
     * Children widgets indexed by id
     *
     * An array containing widgets indexed by their id.  This array only
     * contains widgets that have a non-null id.
     *
     * @var array
     */
    protected $_children_by_id = array();

    /**
     * Initializes this widget
     *
     * Recursively initializes children widgets.
     *
     * @see Zap_Widget::init()
     */
    public function init()
    {
        parent::init();

        foreach($this->_children as &$childWidget) {
            $childWidget->init();
        }
    }

    /**
     * Adds a widget
     *
     * Adds a widget as a child of this container. The widget must not have
     * a parent already. The parent of the added widget is set to
     * reference this container.
     *
     * @param Zap_Widget $widget a reference to the widget to add.
     */
    public function add(Zap_Widget $widget)
    {
        $this->packEnd($widget);
    }

    /**
     * Replace a widget
     *
     * Replaces a child widget in this container. The parent of the removed
     * widget is set to null.
     *
     * @param Zap_Widget $widget a reference to the widget to be replaced.
     * @param Zap_Widget $widget a reference to the new widget.
     *
     * @return Zap_Widget a reference to the removed widget, or null if the
     *                     widget is not found.
     */
    public function replace(Zap_Widget $widget, Zap_Widget $newWidget)
    {
        foreach ($this->_children as $key => $childWidget) {
            if ($childWidget === $widget) {
                $this->_children[$key] = $newWidget;

                $newWidget->setParent($this);
                $widget->setParent(null);

                if (null !== $widget->getId()) {
                    unset($this->childrenById[$widget->getId()]);
                }

                if (null !== $newWidget->getId()) {
                    $this->childrenById[$newWidget->getId()] = $newWidget;
                }

                return $widget;
            }
        }

        return null;
    }

    /**
     * Removes a widget
     *
     * Removes a child widget from this container. The parent of the widget is
     * set to null.
     *
     * @param Zap_Widget $widget a reference to the widget to remove.
     *
     * @return Zap_Widget a reference to the removed widget, or null if the
     *                     widget is not found.
     */
    public function remove(Zap_Widget $widget)
    {
        foreach ($this->_children as $key => $childWidget) {
            if ($childWidget === $widget) {
                unset($this->_children[$key]);

                $widget->setParent(null);

                if (null !== $widget->getId()) {
                    unset($this->childrenById[$widget->getId()]);
                }

                return $widget;
            }
        }

        return null;
    }

    /**
     * Adds a widget to start
     *
     * Adds a widget to the start of the list of widgets in this container.
     *
     * @param Zap_Widget $widget a reference to the widget to add.
     */
    public function packStart(Zap_Widget $widget)
    {
        if (null !== $widget->parent) {
            throw new Zap_Exception('Attempting to add a widget that already '.
                'has a parent.');
        }

        array_unshift($this->_children, $widget);
        $widget->setParent($this);

        if (null !== $widget->getId()) {
            $this->childrenById[$widget->getId()] = $widget;
        }

        $this->sendAddNotifySignal($widget);
    }

    /**
     * Adds a widget to end
     *
     * Adds a widget to the end of the list of widgets in this container.
     *
     * @param Zap_Widget $widget a reference to the widget to add.
     */
    public function packEnd(Zap_Widget $widget)
    {
        if (null !== $widget->getParent()) {
            throw new Zap_Exception('Attempting to add a widget that already ' .
                'has a parent.');
        }

        $this->_children[] = $widget;
        $widget->setParent($this);

        if (null !== $widget->getId()) {
            $this->_childrenById[$widget->getId()] = $widget;
        }

        $this->sendAddNotifySignal($widget);
    }

    /**
     * Gets a child widget
     *
     * Retrieves a widget from the list of widgets in the container based on
     * the unique identifier of the widget.
     *
     * @param string $id the unique id of the widget to look for.
     *
     * @return Zap_Widget the found widget or null if not found.
     */
    public function getChild($id)
    {
        if (array_key_exists($id, $this->_childrenById)) {
            return $this->_childrenById[$id];
        } else {
            return null;
        }
    }

    /**
     * Gets the first child widget
     *
     * Retrieves the first child widget from the list of widgets in the
     * container.
     *
     * @return Zap_Widget the first widget in this container or null if there
     *                    are no widgets in this container.
     */
    public function getFirst()
    {
        if (! count($this->_children)) {
            return null;
        } else {
            reset($this->_children);
            return current($this->_children);
        }
    }

    /**
     * Gets all child widgets
     *
     * Retrieves an array of all widgets directly contained by this container.
     *
     * @param string $class_name optional class name. If set, only widgets that
     *                            are instances of <code>$class_name</code> are
     *                            returned.
     *
     * @return array the child widgets of this container.
     */
    public function getChildren($className = null)
    {
        if (null === $className) {
            return $this->_children;
        }

        $out = array();

        foreach($this->_children as $childWidget) {
            if ($childWidget instanceof $className) {
                $out[] = $childWidget;
            }
        }

        return $out;
    }

    /**
     * Gets descendant UI-objects
     *
     * @param string $class_name optional class name. If set, only UI-objects
     *                            that are instances of <code>$class_name</code>
     *                            are returned.
     *
     * @return array the descendant UI-objects of this container. If descendant
     *                objects have identifiers, the identifier is used as the
     *                array key.
     *
     * @see Zap_UIParent::getDescendants()
     */
    public function getDescendants($className = null)
    {
        if (
            ! (null === $className ||
            class_exists($class_name) || 
            interface_exists($class_name))
        ) {

            return array();
        }

        $out = array();

        foreach ($this->_children as $childWidget) {
            if (null === $className || $childWidget instanceof $className) {
                if (null === $childWidget->getId()) {
                    $out[] = $childWidget;
                } else {
                    $out[$childWidget->getId()] = $childWidget;
                }
            }

            if ($childWidget instanceof Zap_UIParent) {
                $out = array_merge(
                    $out,
                    $childWidget->getDescendants($className)
                );
            }
        }

        return $out;
    }

    /**
     * Gets the first descendant UI-object of a specific class
     *
     * @param string $class_name class name to look for.
     *
     * @return Zap_UIObject the first descendant UI-object or null if no
     *                       matching descendant is found.
     *
     * @see Zap_UIParent::getFirstDescendant()
     */
    public function getFirstDescendant($className)
    {
        if (! class_exists($className) && ! interface_exists($className)) {
            return null;
        }

        $out = null;

        foreach ($this->_children as $childWidget) {
            if ($childWidget instanceof $className) {
                $out = $childWidget;
                break;
            }

            if ($childWidget instanceof Zap_UIParent) {
                $out = $childWidget->getFirstDescendant($className);
                
                if (null !== $out) {
                    break;
                }
            }
        }

        return $out;
    }

    /**
     * Gets descendant states
     *
     * Retrieves an array of states of all stateful UI-objects in the widget
     * subtree below this container.
     *
     * @return array an array of UI-object states with UI-object identifiers as
     *                array keys.
     */
    public function getDescendantStates()
    {
        $states = array();

        foreach ($this->getDescendants('Zap_State') as $id => $object) {
            $states[$id] = $object->getState();
        }

        return $states;
    }

    /**
     * Sets descendant states
     *
     * Sets states on all stateful UI-objects in the widget subtree below this
     * container.
     *
     * @param array $states an array of UI-object states with UI-object
     *                       identifiers as array keys.
     */
    public function setDescendantStates(array $states)
    {
        foreach ($this->getDescendants('Zap_State') as $id => $object) {
            if (isset($states[$id])) {
                $object->setState($states[$id]);
            }
        }
    }

    /**
     * Processes this container by calling {@link Zap_Widget::process()} on all
     * children
     */
    public function process()
    {
        foreach ($this->_children as $child) {
            if (null !== $child && ! $child->isProcessed()) {
                $child->process();
            }
        }
    }

    /**
     * Displays this container by calling {@link Zap_Widget::display()} on all
     * children
     */
    public function display()
    {
        if (! $this->_visible) {
            return;
        }

        parent::display();

        $this->_displayChildren();
    }

    /**
     * Gets all messages
     *
     * @return array an array of gathered {@link Zap_Message} objects.
     *
     * @see Zap_Widget::getMessages()
     */
    public function getMessages()
    {
        $messages = parent::getMessages();

        foreach ($this->_children as $child) {
            $messages = array_merge($messages, $child->getMessages());
        }

        return $messages;
    }

    /**
     * Checks for the presence of messages
     *
     * @return boolean true if this container or the subtree below this
     *                  container has one or more messages.
     *
     * @see Zap_Widget::hasMessage()
     */
    public function hasMessage()
    {
        $hasMessage = parent::hasMessage();

        if (! $hasMessage) {
            foreach ($this->_children as &$child) {
                if ($child->hasMessage()) {
                    $hasMessage = true;
                    break;
                }
            }
        }

        return $hasMessage;
    }

    /**
     * Adds a child object
     *
     * This method fulfills the {@link Zap_UIParent} interface. It is used
     * by {@link Zap_UI} when building a widget tree and should not need to be
     * called elsewhere. To add a widget to a container use
     * {@link Zap_Container::add()}.
     *
     * @param Zap_Widget $child a reference to the child object to add.
     *
     * @throws Zap_InvalidClassException
     */
    public function addChild(Zap_Object $child)
    {
        if ($child instanceof Zap_Widget) {
            $this->add($child);
        } else {
            $className = get_class($child);
            throw new Zap_Exception_InvalidClass(
                'Only Zap_Widget objects may be nested within Zap_Container. '.
                "Attempting to add '{$className}'.", 0, $child);
        }
    }

    /**
     * Gets the Zap_HtmlHeadEntry objects needed by this container
     *
     * @return Zap_HtmlHeadEntrySet the {@link Zap_HtmlHeadEntry} objects
     *                              needed by this container.
     *
     * @see Zap_UIObject::getHtmlHeadEntrySet()
     */
    public function getHtmlHeadEntrySet()
    {
        $set = parent::getHtmlHeadEntrySet();

        foreach ($this->_children as $childWidget) {
            $set->addEntrySet($childWidget->getHtmlHeadEntrySet());
        }

        return $set;
    }

    /**
     * Gets the id attribute of the XHTML element displayed by this widget
     * that should receive focus
     *
     * @return string the id attribute of the XHTML element displayed by this
     *                 widget that should receive focus or null if there is
     *                 no such element.
     *
     * @see Zap_Widget::getFocusableHtmlId()
     */
    public function getFocusableHtmlId()
    {
        $focusId  = null;
        $children = $this->getChildren();

        foreach ($children as $child) {
            $childFocusId = $child->getFocusableHtmlId();

            if (null !== $childFocusId) {
                $focusId = $childFocusId;
                break;
            }
        }

        return $focusId;
    }

    public function printWidgetTree()
    {
        echo get_class($this), ' ', $this->_id;

        $children = $this->getChildren();

        if (count($children) > 0) {
            echo '<ul>';
            foreach ($children as $child) {
                echo '<li>';
                $child->printWidgetTree();
                echo '</li>';
            }
            echo '</ul>';
        }
    }

    /**
     * Performs a deep copy of the UI tree starting with this UI object
     *
     * @param string $id_suffix optional. A suffix to append to copied UI
     *                           objects in the UI tree.
     *
     * @return Zap_UIObject a deep copy of the UI tree starting with this UI
     *                       object.
     *
     * @see Zap_UIObject::copy()
     */
    public function copy($id_suffix = '')
    {
        $copy = parent::copy($id_suffix);
        $copy->children_by_id = array();

        foreach ($this->children as $key => $child_widget) {
            $copy_child = $child_widget->copy($id_suffix);
            $copy_child->parent = $copy;
            $copy->children[$key] = $copy_child;
            if ($copy_child->id !== null) {
                $copy->children_by_id[$copy_child->id] = $copy_child;
            }
        }

        return $copy;
    }

    /**
     * Displays the child widgets of this container
     *
     * Subclasses that override the display method will typically call this
     * method to display child widgets.
     */
    protected function _displayChildren()
    {
        foreach ($this->_children as $child) {
            $child->display();
        }
    }

    /**
     * Notifies this widget that a widget was added
     *
     * This widget may want to adjust itself based on the widget added or
     * any of the widgets children.
     *
     * @param Zap_Widget $widget the widget that has been added.
     */
    protected function _notifyOfAdd($widget)
    {
    }

    /**
     * Sends the notification signal up the widget tree
     *
     * This container is notified of the added widget and then this
     * method is called on the container parent.
     *
     * @param Zap_Widget $widget the widget that has been added.
     */
    protected function _sendAddNotifySignal($widget)
    {
        $this->notifyOfAdd($widget);

        if (
            null !== $this->_parent 
            && $this->_parent instanceof Zap_Container
        ) {
            $this->_parent->sendAddNotifySignal($widget);
        }
    }
}


