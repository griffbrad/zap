<?php

require_once 'Concentrate/DataProvider/FileFinderInterface.php';
require_once 'Concentrate/DataProvider/FileFinderDirectory.php';
require_once 'PEAR/Config.php';

/**
 * @category  Tools
 * @package   Concentrate
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Concentrate_DataProvider_FileFinderPear
	implements Concentrate_DataProvider_FileFinderInterface
{
	protected $pearConfig = null;

	public function __construct($pearrc = null)
	{
		$this->setPearRc($pearrc);
	}

	public function setPearRc($pearrc = null)
	{
		$this->pearConfig = PEAR_Config::singleton($pearrc);
		return $this;
	}

	public function getDataFiles()
	{
		$files = array();

		$dataDir = $this->pearConfig->get('data_dir');
		if (is_dir($dataDir)) {
			// check each package sub-directory in the data directory
			$dataDirObject = dir($dataDir);
			while (false !== ($subDir = $dataDirObject->read())) {

				$dependencyDir = $dataDir .
					DIRECTORY_SEPARATOR . $subDir .
					DIRECTORY_SEPARATOR . 'dependencies';

				$finder = new Concentrate_DataProvider_FileFinderDirectory(
					$dependencyDir
				);

				$files = array_merge($files, $finder->getDataFiles());

			}
			$dataDirObject->close();
		}

		return $files;
	}
}

?>
