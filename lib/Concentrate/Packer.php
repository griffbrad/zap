<?php

require_once 'Concentrate/Exception.php';
require_once 'Concentrate/FileList.php';
require_once 'Concentrate/Inliner.php';

/**
 * @category  Tools
 * @package   Concentrate
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Concentrate_Packer
{
	public function pack($root, array $sourceFiles, $destinationFile)
	{
		$packedFiles = new Concentrate_FileList();

		$content = '';
		foreach ($sourceFiles as $sourceFile) {
			$inliner = Concentrate_Inliner::factory(
				$root,
				$sourceFile,
				$destinationFile,
				$packedFiles
			);

			$content .= $inliner->getInlineContent();
		}

		$filename = $root . DIRECTORY_SEPARATOR . $destinationFile;

		if (   (!file_exists($filename) && !is_writable($root))
			|| (file_exists($filename) && !is_writable($filename))
		) {
			throw new Concentrate_FileException(
				"The file '{$filename}' could not be written."
			);
		}

		file_put_contents($filename, $content);

		return $this;
	}
}

?>
