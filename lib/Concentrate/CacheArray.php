<?php

require_once 'Concentrate/CacheHierarchyAbstract.php';

/**
 * @category  Tools
 * @package   Concentrate
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Concentrate_CacheArray extends Concentrate_CacheHierarchyAbstract
{
	protected $data = array();

	public function setPrefix($prefix)
	{
		// do nothing since array is not saved between requests
	}

	protected function setSelf($key, $value)
	{
		$this->data[$key] = $value;
		return true;
	}

	protected function getSelf($key)
	{
		$value = false;

		if (isset($this->data[$key])) {
			$value = $this->data[$key];
		}

		return $value;
	}

	protected function deleteSelf($key)
	{
		unset($this->data[$key]);
		return true;
	}
}

?>
