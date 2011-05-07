<?php

require_once 'Concentrate/Exception.php';
require_once 'Concentrate/FileList.php';

/**
 * @category  Tools
 * @package   Concentrate
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Concentrate_Inliner
{
	protected $inlinedFiles = null;
	protected $root = '';
	protected $destinationFilename = '';
	protected $sourceFilename = '';
	protected $sourceDirectory= '.';
	protected $destinationDirectory = '.';
	protected $content = '';

	public function __construct(
		$root,
		$sourceFilename,
		$destinationFilename,
		Concentrate_FileList $inlinedFiles
	) {
		$this->root = $root;

		$this->sourceFilename      = $sourceFilename;
		$this->destinationFilename = $destinationFilename;

		$this->sourceDirectory      = dirname($sourceFilename);
		$this->destinationDirectory = dirname($destinationFilename);

		$this->filename = $root . DIRECTORY_SEPARATOR . $sourceFilename;

		$this->inlinedFiles = $inlinedFiles;
	}

	public static function factory(
		$root,
		$sourceFilename,
		$destinationFilename,
		Concentrate_FileList $inlinedFiles
	) {
		$extension = pathinfo($sourceFilename, PATHINFO_EXTENSION);

		switch (strtolower($extension)) {
		case 'css':
			include_once 'Concentrate/InlinerCss.php';
			$class = 'Concentrate_InlinerCss';
			break;
		default:
			$class = __CLASS__;
			break;
		}

		return new $class(
			$root,
			$sourceFilename,
			$destinationFilename,
			$inlinedFiles
		);
	}

	public function getInlineContent()
	{
		$this->inlinedFiles->add($this->sourceFilename);
		return $this->load($this->filename);
	}

	protected function load($filename)
	{
		$content = '';

		// only load it if has not already been inlined
		if (!$this->inlinedFiles->contains($filename)) {
			if (!is_readable($filename)) {
				throw new Concentrate_FileException(
					"The file '{$filename}' could not be read."
				);
			}

			$content = file_get_contents($filename);
		}

		return $content;
	}

	protected function evaluatePath($path)
	{
		$postPath = array();
		$prePath = array();

		$path = rtrim($path, '/');
		$pathSegments = explode('/', $path);
		foreach ($pathSegments as $segment) {
			if ($segment == '..') {
				if (count($postPath) > 0) {
					array_pop($postPath);
				} else {
					// we've gone past the start of the relative path
					array_push($prePath, '..');
				}
			} else if ($segment == '.') {
				// no-op
			} else {
				array_push($postPath, $segment);
			}
		}

		return implode(
			'/',
			array_merge(
				$prePath,
				$postPath
			)
		);
	}

	protected function isAbsolute($uri)
	{
		return (preg_match('!^(https?:|ftp:)//!', $uri) === 1);
	}
}

?>
