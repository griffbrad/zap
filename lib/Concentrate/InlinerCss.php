<?php

require_once 'Concentrate/Inliner.php';

/**
 * @category  Tools
 * @package   Concentrate
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Concentrate_InlinerCss extends Concentrate_Inliner
{
	public function getInlineContent()
	{
		$content = parent::getInlineContent();
		$content = $this->inlineImports($content);
		$content = $this->updateUris($content);

		$content = "\n/* inlined file \"{$this->sourceFilename}\" */\n" .
			$content;

		return $content;
	}

	protected function updateUris($content)
	{
		if ($this->sourceDirectory != $this->destinationDirectory) {
			$content = preg_replace_callback(
				'/url\((.*?)\)/ui',
				array($this, 'updateUrisCallback'),
				$content
			);
		}
		return $content;
	}

	protected function updateUrisCallback(array $matches)
	{
		$uri    = $matches[1];
		$quoted = false;

		// check if the URI is quoted
		if (preg_match('/^[\'"].*[\'"]$/', $matches[1]) === 1) {
			$quoted = true;
			$uri = trim($matches[1], '\'"');
		}

		// check if it is a relative URI; if so, rewrite it
		if (!$this->isAbsolute($uri)) {

			// get path relative to root
			$directory = $this->sourceDirectory . '/' . dirname($uri);

			// add relative paths back to the root from the destination
			foreach (explode('/', $this->destinationDirectory) as $segment) {
				$directory = '../' . $directory;
			}

			// evaluate relative paths
			$directory = $this->evaluatePath($directory);

			$uri = $directory . '/' . basename($uri);
		}

		// if quoted, re-add quotation marks
		if ($quoted) {
			$uri = '"' . str_replace('"', '%22', $uri) . '"';
		}

		// re-add CSS URI syntax
		return 'url(' . $uri . ')';
	}

	protected function inlineImports($content)
	{
		$content = preg_replace_callback(
			'/@import\s+url\((.*?)\);/ui',
			array($this, 'inlineImportCallback'),
			$content
		);
		return $content;
	}

	protected function inlineImportCallback($matches)
	{
		$replacement = '';
		$uri         = trim($matches[1], '\'"');

		// modify relative path to be relative to root, rather than
		// directory of CSS file
		if (!$this->isAbsolute($uri)) {
			$uri = $this->sourceDirectory . '/' . $uri;
			$uri = $this->evaluatePath($uri);
		}

		if (!$this->inlinedFiles->contains($uri)) {

			// recursively inline the import
			$inliner = new self(
				$this->root,
				$uri,
				$this->destinationFilename,
				$this->inlinedFiles
			);

			$content      = $inliner->load($inliner->filename);
			$replacement  = "\n/* at-import inlined file \"{$uri}\" */\n";
			$replacement .= $inliner->inlineImports($content);

			$this->inlinedFiles->add($uri);
		}

		return $replacement;
	}
}

?>
