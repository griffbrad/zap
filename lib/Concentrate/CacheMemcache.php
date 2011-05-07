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
class Concentrate_CacheMemcache extends Concentrate_CacheHierarchyAbstract
{
	protected $extraPrefix = '';
	protected $prefix = '';
	protected $memcache = null;

	public function __construct(Memcached $memcache, $extraPrefix = '')
	{
		$this->extraPrefix = strval($extraPrefix);
		$this->memcache    = $memcache;
	}

	public function setPrefix($prefix)
	{
		$this->prefix = strval($prefix);
	}

	protected function setSelf($key, $value)
	{
		return $this->memcache->set($this->getMemcacheKey($key), $value);
	}

	protected function getSelf($key)
	{
		return $this->memcache->get($this->getMemcacheKey($key));
	}

	protected function deleteSelf($key)
	{
		return $this->memcache->delete($this->getMemcacheKey(key));
	}

	protected function getMemcacheKey($key)
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
