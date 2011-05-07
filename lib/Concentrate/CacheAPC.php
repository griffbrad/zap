<?php

require_once 'Concentrate/CacheHierarchyAbstract.php';
require_once 'Concentrate/CacheArray.php';

/**
 * @category  Tools
 * @package   Concentrate
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Concentrate_CacheAPC extends Concentrate_CacheHierarchyAbstract
{
	protected $extraPrefix = '';
	protected $prefix = '';
	protected $hasAPC = false;

	public function __construct($extraPrefix = '')
	{
		$this->hasAPC      = extension_loaded('apc');
		$this->extraPrefix = strval($extraPrefix);
	}

	public function setPrefix($prefix)
	{
		$this->prefix = strval($prefix);
	}

	protected function setSelf($key, $value)
	{
		$result = false;

		if ($this->hasAPC) {
			$result = apc_store($this->getAPCKey($key), $value);
		}

		return $result;
	}

	protected function getSelf($key)
	{
		$value = false;

		if ($this->hasAPC) {
			$value = apc_fetch($this->getAPCKey($key));
		}

		return $value;
	}

	protected function deleteSelf($key)
	{
		$response = false;

		if ($this->hasAPC) {
			$response = apc_delete($this->getAPCKey(key));
		}

		return $response;
	}

	protected function getAPCKey($key)
	{
		if ($this->prefix != '') {
			$key = $this->prefix . ':' . $key;
		}

		if ($this->extraPrefix != '') {
			$key = $this->extraPrefix . ':' . $key;
		}

		return $key;
	}
}

?>
