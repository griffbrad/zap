<?php

require_once 'SymfonyComponents/YAML/sfYamlParser.php';
require_once 'Concentrate/Exception.php';
require_once 'Concentrate/CacheInterface.php';
require_once 'Concentrate/CacheArray.php';

/**
 * @category  Tools
 * @package   Concentrate
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Concentrate_DataProvider
{
	protected $data = array();
	protected $pendingFiles = array();
	protected $loadedFiles = array();
	protected $stat = true;
	protected $cachePrefix = '';

	public function __construct(array $options = array())
	{
		if (array_key_exists('stat', $options)) {
			$this->setStat($options['stat']);
		}
	}

	public function setStat($stat)
	{
		$stat = ($stat) ? true : false;

		if ($stat !== $this->stat) {
			$this->stat = $stat;

			// clear cache prefix
			$this->cachePrefix = '';
		}
	}

	public function loadFile($filename)
	{
		$this->pendingFiles[] = strval($filename);

		// clear cache prefix
		$this->cachePrefix = '';
	}

	public function getData()
	{
		$this->loadPendingData();
		return $this->data;
	}

	protected function loadPendingData()
	{
		while (count($this->pendingFiles) > 0) {
			$filename = array_shift($this->pendingFiles);
			if (!in_array($filename, $this->loadedFiles)) {
				$this->loadPendingFile($filename);
			}
		}
	}

	protected function loadPendingFile($filename)
	{
		if (!is_readable($filename)) {
			throw new Concentrate_FileException(
				"Data file '{$filename}' can not be read.", 0, $filename);
		}

		try {
			$data = sfYaml::load($filename);
			$this->loadedFiles[] = $filename;
		} catch (InvalidArgumentException $e) {
			throw new Concentrate_FileFormatException(
				"Data file '{$filename}' is not valid YAML.",0, $filename);
		}

		$this->data = array_merge_recursive($this->data, $data);
	}

	public function getCachePrefix()
	{
		if ($this->cachePrefix === '') {
			$files = array_merge($this->loadedFiles, $this->pendingFiles);
			if ($this->stat) {
				$statFiles = array();
				foreach ($files as $filename) {
					$mtime       = filemtime($filename);
					$statFiles[] = $filename . '=' . $mtime;
				}
				$key = md5(implode(':', $statFiles));
			} else {
				$key = md5(implode(':', $files));
			}

			$this->cachePrefix = 'concentrate:' . $key;
		}

		return $this->cachePrefix;
	}

}

?>
