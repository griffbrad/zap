<?php

require_once 'Concentrate/Exception.php';

/**
 * @category  Tools
 * @package   Concentrate
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
abstract class Concentrate_MinifierAbstract
{
	abstract public function minify($content, $type);

	public function minifyFile($fromFilename, $toFilename, $type)
	{
		if (!is_readable($fromFilename)) {
			throw new Concentrate_FileException(
				"Could not read {$fromFilename} for minification.",
				0,
				$fromFilename
			);
		}

		$this->writeDirectory($toFilename);

		$content = file_get_contents($fromFilename);
		$content = $this->minify($content, $type);
		file_put_contents($toFilename, $content);
	}

	protected function writeDirectory($toFilename)
	{
		$toDirectory = dirname($toFilename);
		if (!file_exists($toDirectory)) {
			mkdir($toDirectory, 0770, true);
		}
		if (!is_dir($toDirectory)) {
			throw new Concentrate_FileException(
				"Could not write to directory {$toDirectory} because it " .
				"is not a directory.",
				0,
				$toDirectory
			);
		}
	}
}

?>
