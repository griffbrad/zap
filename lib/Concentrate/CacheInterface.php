<?php

/**
 * @category  Tools
 * @package   Concentrate
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
interface Concentrate_CacheInterface
{
	public function setPrefix($prefix);
	public function set($key, $value);
	public function get($key);
	public function delete($key);
}

?>
