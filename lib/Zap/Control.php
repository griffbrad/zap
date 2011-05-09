<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Zap/Widget.php';
require_once 'Zap/Titleable.php';
require_once 'Zap/String.php';

/**
 * Abstract base class for control widgets (non-container)
 *
 * @package   Zap
 * @copyright 2004-2010 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
abstract class Zap_Control extends Zap_Widget
{
	/**
	 * Adds a message to this control
	 *
	 * Before the message is added, the content is updated with the name of
	 * this controls's parent title field if the parent implements the
	 * {@link SwatTitleable} interface.
	 *
	 * @param SwatMessage $message the message to add.
	 *
	 * @see SwatWidget::addMessage()
	 */
	public function addMessage(Zap_Message $message)
	{
		if (! $this->_parent instanceof Zap_Titleable) {
			$fieldTitle = '';
		} else {
			$title = $this->_parent->getTitle();

			if (null === $title) {
				$fieldTitle = '';
			} else {
				if ('text/xml' === $this->_parent->getTitleContentType()) {
					$fieldTitle =
						'<strong>'.$this->_parent->getTitle().'</strong>';
				} else {
					$fieldTitle =
						'<strong>'.
						Zap_String::minimizeEntities($this->_parent->getTitle()).
						'</strong>';
				}
			}
		}

		if ('text/plain' === $message->getContentType()) {
			$content = Zap_String::minimizeEntities($message->getPrimaryContent());
		} else {
			$content = $message->getPrimaryContent();
		}

		$message->setPrimaryContent(sprintf($content, $fieldTitle))
			    ->setContentType('text/xml');

		parent::addMessage($message);
	}

	public function printWidgetTree()
	{
		echo get_class($this), ' ', $this->id;
	}

	/**
	 * Gets an informative note of how to use this control
	 *
	 * By default, controls return null, meaning no note.
	 *
	 * @return SwatMessage an informative note of how to use this control or
	 *                      null if this control has no note.
	 */
	public function getNote()
	{
		return null;
	}
}


