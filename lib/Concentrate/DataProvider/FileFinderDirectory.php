<?php

require_once 'Concentrate/DataProvider/FileFinderInterface.php';

/**
 * @category  Tools
 * @package   Concentrate
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Concentrate_DataProvider_FileFinderDirectory
	implements Concentrate_DataProvider_FileFinderInterface
{
	protected $directory = null;

	public function __construct($directory)
	{
		$this->directory = $directory;
	}

	public function getDataFiles()
	{
		$files = array();

		if (!file_exists($this->directory) || !is_dir($this->directory)) {
			return $files;
		}

		$dirObject = dir($this->directory);
		while (false !== ($file = $dirObject->read())) {

			// if it is a YAML file, add it to the list
			if (preg_match('/\.yaml$/i', $file) === 1) {
				$files[] = $this->directory .
					DIRECTORY_SEPARATOR . $file;
			}

		}
		$dirObject->close();

		return $files;
	}
}

?>
