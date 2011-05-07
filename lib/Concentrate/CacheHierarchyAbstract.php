<?php

require_once 'Concentrate/CacheInterface.php';

/**
 * @category  Tools
 * @package   Concentrate
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
abstract class Concentrate_CacheHierarchyAbstract
	implements Concentrate_CacheInterface
{
	protected $subcache = null;

	public function setSubcache(Concentrate_CacheInterface $cache)
	{
		$this->subcache = $cache;
		return $cache;
	}

	public function set($key, $value)
	{
		$response = $this->setSelf($key, $value);

		if ($this->subcache !== null) {
			$this->subcache->set($key, $value);
		}

		return $response;
	}

	public function get($key)
	{
		$value = $this->getSelf($key);

		if ($value === false && $this->subcache !== null) {
			$value = $this->subcache->get($key);
			if ($value !== false) {
				$this->setSelf($key, $value);
			}
		}

		return $value;
	}

	public function delete($key)
	{
		$response = $this->deleteSelf($key);

		if ($this->subcache !== null) {
			$this->subcache->delete($key);
		}

		return $response;
	}

	abstract protected function setSelf($key, $value);
	abstract protected function getSelf($key);
	abstract protected function deleteSelf($key);
}

?>
