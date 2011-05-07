<?php

require_once 'Console/CommandLine.php';
require_once 'Concentrate/Concentrator.php';
require_once 'Concentrate/Packer.php';
require_once 'Concentrate/DataProvider.php';
require_once 'Concentrate/DataProvider/FileFinderPear.php';
require_once 'Concentrate/MinifierYuiCompressor.php';

/**
 * @category  Tools
 * @package   Concentrate
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Concentrate_CLI
{
	const VERBOSITY_NONE     = 0;
	const VERBOSITY_MESSAGES = 1;
	const VERBOSITY_DETAILS  = 2;

	const FILENAME_FLAG_COMBINED = '.concentrate-combined';
	const FILENAME_FLAG_MINIFIED = '.concentrate-minified';

	/**
	 * @var Console_CommandLine
	 */
	protected $parser = null;

	/**
	 * @var Concentrate_Concentrator
	 */
	protected $concentrator = null;

	/**
	 * @var string
	 */
	protected $pearrc = null;

	/**
	 * @var boolean
	 */
	protected $minify = false;

	/**
	 * @var boolean
	 */
	protected $combine = false;

	/**
	 * @var string
	 */
	protected $webroot = './';

	/**
	 * @var string
	 */
	protected $directory = '';

	/**
	 * @var string
	 */
	protected $verbosity = self::VERBOSITY_NONE;

	public function run()
	{
		$this->concentrator = new Concentrate_Concentrator();

		$this->parser = Console_CommandLine::fromXmlFile($this->getUiXml());

		try {
			$result = $this->parser->parse();

			$this->setOptions($result->options);
			$this->setWebRoot($result->args['webroot']);
			$this->loadDataFiles();

			if ($this->combine) {
				$this->writeCombinedFiles();
				$this->writeCombinedFlagFile();
			}

			if ($this->minify) {
				$this->writeMinifiedFiles();
				$this->writeMinifiedFlagFile();
			}

		} catch (Console_CommandLine_Exception $e) {
			$this->displayError($e->getMessage() . PHP_EOL);
		} catch (Exception $e) {
			$this->displayError($e->getMessage() . PHP_EOL, false);
			$this->displayError($e->getTraceAsString() . PHP_EOL);
		}
	}

	protected function setWebRoot($webroot)
	{
		if ($this->verbosity >= self::VERBOSITY_MESSAGES) {
			$this->display(PHP_EOL . 'Web root:' . PHP_EOL);
		}

		$this->webroot = strval($webroot);
		$this->webroot = rtrim($this->webroot, DIRECTORY_SEPARATOR);

		if ($this->verbosity >= self::VERBOSITY_MESSAGES) {
			$this->display('=> set to "' . $this->webroot . '"' . PHP_EOL);
		}

		return $this;
	}

	protected function setOptions(array $options)
	{
		if (   array_key_exists('verbose', $options)
			&& $options['verbose'] !== null
		) {
			$this->verbosity = intval($options['verbose']);
		}

		if (   array_key_exists('pearrc', $options)
			&& $options['pearrc'] !== null
		) {
			$this->pearrc = strval($options['pearrc']);
		}

		if (   array_key_exists('combine', $options)
			&& $options['combine'] !== null
		) {
			$this->combine = ($options['combine']) ? true : false;
		}

		if (   array_key_exists('minify', $options)
			&& $options['minify'] !== null
		) {
			$this->minify = ($options['minify']) ? true : false;
		}

		if (   array_key_exists('directory', $options)
			&& $options['directory'] !== null
		) {
			$this->directory = strval($options['directory']);
			$this->directory = rtrim($this->directory, DIRECTORY_SEPARATOR);
		}

		if ($this->verbosity >= self::VERBOSITY_MESSAGES) {
			$this->display(PHP_EOL . 'Options:' . PHP_EOL);
			$this->display('=> pearrc    : ' . $this->pearrc . PHP_EOL);
			$this->display('=> directory : ' . $this->directory . PHP_EOL);
			$this->display(
				sprintf(
					'=> combine   : %s' . PHP_EOL,
					($this->combine) ? 'yes' : 'no'
				)
			);
			$this->display(
				sprintf(
					'=> minify    : %s' . PHP_EOL,
					($this->minify) ? 'yes' : 'no'
				)
			);
		}

		return $this;
	}

	protected function loadDataFiles()
	{
		// load data files from pear
		$fileFinder = new Concentrate_DataProvider_FileFinderPear(
			$this->pearrc
		);
		$this->concentrator->loadDataFiles(
			$fileFinder->getDataFiles()
		);

		// load data files from optional directory
		if ($this->directory != '') {
			$fileFinder = new Concentrate_DataProvider_FileFinderDirectory(
				$this->directory
			);
			$this->concentrator->loadDataFiles(
				$fileFinder->getDataFiles()
			);
		}

		return $this;
	}

	protected function writeCombinedFiles()
	{
		if ($this->verbosity >= self::VERBOSITY_MESSAGES) {
			$this->display(PHP_EOL . 'Writing combined files:' . PHP_EOL);
		}

		$packer       = new Concentrate_Packer();
		$combinesInfo = $this->concentrator->getCombinesInfo();

		if (   count($combinesInfo) === 0
			&& $this->verbosity >= self::VERBOSITY_MESSAGES
		) {
			$this->display('=> no combined files to write.' . PHP_EOL);
		}

		foreach ($combinesInfo as $combine => $info) {
			if ($this->verbosity >= self::VERBOSITY_MESSAGES) {
				$filename = $this->webroot . DIRECTORY_SEPARATOR . $combine;
				$this->display('=> writing "' . $filename . '"' . PHP_EOL);
			}

			$files = $info['Includes'];

			$this->checkForConflicts(array_keys($files));
			uksort($files, array($this->concentrator, 'compareFiles'));

			if ($this->verbosity >= self::VERBOSITY_DETAILS) {
				foreach ($files as $file => $info) {
					$string = ' * ' . $file;
					if (!$info['explicit']) {
						$string .= ' [IMPLICIT]';
					}
					$string .= PHP_EOL;
					$this->display($string);
				}
				$this->display(PHP_EOL);
			}

			$packer->pack($this->webroot, array_keys($files), $combine);
		}
	}

	protected function writeMinifiedFiles()
	{
		if ($this->verbosity >= self::VERBOSITY_MESSAGES) {
			$this->display(PHP_EOL . 'Writing minified files:' . PHP_EOL);
		}

		$minifier = new Concentrate_MinifierYuiCompressor();

		$fileInfo = $this->concentrator->getFileInfo();
		foreach ($fileInfo as $file => $info) {
			$fromFilename = $this->webroot
				. DIRECTORY_SEPARATOR . $file;

			// if source file does not exist, skip it
			if (!file_exists($fromFilename)) {
				continue;
			}

			// if file specifies that is should not be minified, skip it
			if (!$info['Minify']) {
				continue;
			}

			// only minify JavaScript
			if (substr($fromFilename, -3) !== '.js') {
				continue;
			}

			$toFilename = $this->webroot
				. DIRECTORY_SEPARATOR . 'min'
				. DIRECTORY_SEPARATOR . $file;

			if ($this->verbosity >= self::VERBOSITY_DETAILS) {
				$this->display(' * ' . $file . PHP_EOL);
			}

			$this->writeMinifiedFile(
				$minifier,
				$fromFilename,
				$toFilename,
				'js'
			);
		}

		if ($this->combine) {
			$combinesInfo = $this->concentrator->getCombinesInfo();
			foreach ($combinesInfo as $combine => $info) {
				$fromFilename = $this->webroot
					. DIRECTORY_SEPARATOR . $combine;

				// if source file does not exist, skip it
				if (!file_exists($fromFilename)) {
					continue;
				}

				// if file specifies that is should not be minified, skip it
				if (!$info['Minify']) {
					continue;
				}

				// only minify JavaScript
				if (substr($fromFilename, -3) !== '.js') {
					continue;
				}

				$toFilename = $this->webroot
					. DIRECTORY_SEPARATOR . 'min'
					. DIRECTORY_SEPARATOR . $combine;

				if ($this->verbosity >= self::VERBOSITY_DETAILS) {
					$this->display(' * ' . $combine . PHP_EOL);
				}

				$this->writeMinifiedFile(
					$minifier,
					$fromFilename,
					$toFilename,
					'js'
				);
			}
		}
	}

	protected function writeMinifiedFile(
		Concentrate_MinifierAbstract $minifier,
		$fromFilename,
		$toFilename,
		$type
	) {
		$md5 = md5_file($fromFilename);
		$dir = $this->getMinifiedCacheDir();

		$cacheFilename = $dir . DIRECTORY_SEPARATOR . $md5;

		if (file_exists($cacheFilename) && is_readable($cacheFilename)) {
			// use cache file
			if (!file_exists(dirname($toFilename))) {
				mkdir(dirname($toFilename), 0770, true);
			}
			copy($cacheFilename, $toFilename);
			if ($this->verbosity >= self::VERBOSITY_DETAILS) {
				$this->display(' * used cached version' . PHP_EOL);
			}
		} else {
			// minify
			$minifier->minifyFile($fromFilename, $toFilename, $type);

			// write cache file
			if (!is_dir($dir) && is_writable(dirname($dir))) {
				mkdir($dir, 0770, true);
			}

			if (is_dir($dir) && is_writable($dir)) {
				copy($toFilename, $cacheFilename);
				if ($this->verbosity >= self::VERBOSITY_DETAILS) {
					$this->display(' * wrote cached version' . PHP_EOL);
				}
			} else {
				if ($this->verbosity >= self::VERBOSITY_DETAILS) {
					$this->display(
						' * count not write cached version' . PHP_EOL
					);
				}
			}
		}
	}

	protected function writeCombinedFlagFile()
	{
		$this->writeFlagFile(self::FILENAME_FLAG_COMBINED);
	}

	protected function writeMinifiedFlagFile()
	{
		$this->writeFlagFile(self::FILENAME_FLAG_MINIFIED);
	}

	protected function writeFlagFile($filename)
	{
		$filename = $this->webroot
			. DIRECTORY_SEPARATOR . $filename;

		if ($this->verbosity >= self::VERBOSITY_MESSAGES) {
			$this->display(PHP_EOL . "Writing flag file '{$filename}':"
				. PHP_EOL);
		}

		if (   (!file_exists($filename) && !is_writable($this->webroot))
			|| (file_exists($filename) && !is_writable($filename))
		) {
			throw new Concentrate_FileException(
				"The flag file '{$filename}' could not be written."
			);
		}

		file_put_contents($filename, time());

		if ($this->verbosity >= self::VERBOSITY_MESSAGES) {
			$this->display('=> written' . PHP_EOL);
		}
	}

	protected function getMinifiedCacheDir()
	{
		$dir = '@data-dir@' . DIRECTORY_SEPARATOR . '@package-name@';

		if ($dir[0] == '@') {
			$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..';
		}

		return $dir . DIRECTORY_SEPARATOR . 'minified-cache';
	}

	protected function getUiXml()
	{
		$dir = '@data-dir@' . DIRECTORY_SEPARATOR
			. '@package-name@' . DIRECTORY_SEPARATOR . 'data';

		if ($dir[0] == '@') {
			$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'
				. DIRECTORY_SEPARATOR . 'data';
		}

		return $dir . DIRECTORY_SEPARATOR . 'cli.xml';
	}

	protected function display($string)
	{
		$this->parser->outputter->stdout($string);
	}

	protected function displayError($string, $exit = true, $code = 1)
	{
		$this->parser->outputter->stderr($string);
		if ($exit) {
			exit($code);
		}
	}

	/**
	 * @param array $files
	 *
	 * @throws Exception if one or more conflicts are present.
	 */
	protected function checkForConflicts(array $files)
	{
		$conflicts = $this->concentrator->getConflicts($files);
		if (count($conflicts) > 0) {
			$conflictList = '';
			$count = 0;
			foreach ($conflicts as $file => $conflict) {
				$conflictList.= sprintf(
					"\n- %s conflicts with %s",
					$file,
					implode(', ', $conflict)
				);
				$count++;
			}
			throw new Exception(
				'The following conflicts were detected: ' . $conflictList
			);
		}
	}

}

?>
