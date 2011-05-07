<?php

require_once 'Concentrate/MinifierAbstract.php';
require_once 'Concentrate/Exception.php';

/**
 * @category  Tools
 * @package   Concentrate
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Concentrate_MinifierYuiCompressor extends Concentrate_MinifierAbstract
{
	const DEFAULT_JAR_NAME = '/yuicompressor-[0-9]\.[0-9]\.[0-9]\.jar/';

	protected $javaBin = 'java';
	protected $jarFile = '';

	public function __construct(array $options = array())
	{
		if (array_key_exists('javaBin', $options)) {
			$this->setJavaBin($options['javaBin']);
		} elseif (array_key_exists('java_bin', $options)) {
			$this->setJavaBin($options['java_bin']);
		}

		if (array_key_exists('jarFile', $options)) {
			$this->setJavaBin($options['jarFile']);
		} elseif (array_key_exists('jar_file', $options)) {
			$this->setJavaBin($options['jar_file']);
		}
	}

	public function setJavaBin($javaBin)
	{
		$this->javaBin = $javaBin;
		return $this;
	}

	public function setJarFile($jarFile)
	{
		$this->jarFile = $jarFile;
		return $this;
	}

	public function minify($content, $type)
	{
		return $this->minifyInternal($content, false, null, $type);
	}

	public function minifyFile($fromFilename, $toFilename, $type)
	{
		$this->writeDirectory($toFilename);
		return $this->minifyInternal($fromFilename, true, $toFilename, $type);
	}

	protected function minifyInternal($data, $isFile, $outputFile, $type)
	{
		// default args
		$args = array(
			'--nomunge',
			'--preserve-semi',
		);

		// type
		switch ($type) {
		case 'css':
			$args[] = '--type css';
			break;
		case 'js':
		default:
			$args[] = '--type js';
			break;
		}

		// output
		if ($outputFile !== null) {
			$args[] = '-o ' . escapeshellarg($outputFile);
		}

		// filename
		if ($isFile) {
			$filename = $data;
		} else {
			$filename = $this->writeTempFile($content);
		}
		$args[] = escapeshellarg($filename);

		// build command
		$command = sprintf(
			'%s -jar %s %s',
			$this->javaBin,
			escapeshellarg($this->getJarFile()),
			implode(' ', $args)
		);

		// run command
		$output = shell_exec($command);

		// remove temp file
		if (!$isFile) {
			unlink($filename);
		}

		$errorExpression = '/^Unable to access jarfile/';
		if (preg_match($errorExpression, $output) === 1) {
			throw new Concentrate_FileException(
				"The JAR file '{$this->jarFile}' does not exist.",
				0,
				$this->jarFile
			);
		}

		return $output;
	}

	protected function writeTempFile($content)
	{
		$filename = tempnam(sys_get_temp_dir(), 'concentrate-');
		file_put_contents($filename, $content);
		return $filename;
	}

	protected function getJarFile()
	{
		if ($this->jarFile == '') {
			$this->jarFile = $this->findJarFile();
		}

		return $this->jarFile;
	}

	protected function findJarFile()
	{
		$jarFile = '';

		$paths = explode(PATH_SEPARATOR, get_include_path());
		foreach ($paths as $path) {
			$dir = dir($path);
			while (false !== ($entry = $dir->read())) {
				if (preg_match(self::DEFAULT_JAR_NAME, $entry) === 1) {
					$jarFile = $path . DIRECTORY_SEPARATOR . $entry;
					break 2;
				}
			}
		}

		return $jarFile;
	}
}

?>
