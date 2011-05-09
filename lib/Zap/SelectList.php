<?php

require_once 'Zap/CheckboxList.php';
require_once 'Zap/HtmlTag.php';

/**
 * List of selectable options
 *
 * @package   Zap
 * @copyright 2007 silverorange, 2011 Delta Systems
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_SelectList extends Zap_CheckboxList
{
	// {{{ public properties

	/**
	 * Optional number of rows in the select list
	 *
	 * @var integer
	 */
	public $size;

	// }}}
	// {{{ public function display()

	/**
	 * Displays this select list
	 */
	public function display()
	{
		$options = $this->getOptions();

		if (!$this->visible || count($options) == 0)
			return;

		SwatWidget::display();

		$this->getForm()->addHiddenField($this->id.'_submitted', 1);

		$select_tag = new SwatHtmlTag('select');
		$select_tag->id = $this->id;
		$select_tag->name = $this->id.'[]';
		$select_tag->class = 'swat-select-list';
		$select_tag->multiple = 'multiple';
		$select_tag->size = $this->size;
		$select_tag->open();

		foreach ($options as $key => $option) {

			$option_tag = new SwatHtmlTag('option');
			$option_tag->value = (string)$option->value;
			$option_tag->id = $this->id.'_'.$key.'_'.$option_tag->value;
			$option_tag->selected = null;
			if (in_array($option->value, $this->values))
				$option_tag->selected = 'selected';

			$option_tag->setContent($option->title, $option->content_type);
			$option_tag->display();
		}

		$select_tag->close();
	}

	// }}}
	// {{{ public function getNote()

	/**
	 * Gets a note letting the user know the select list can select multiple
	 * options
	 *
	 * @return SwatMessage a note letting the user know the select list can
	 *                      select multiple options.
	 *
	 * @see SwatControl::getNote()
	 */
	public function getNote()
	{
		$message = Zap::_(
			'Multiple items can be selected by holding down the Ctrl key.');

		return new SwatMessage($message);
	}

	// }}}
}


