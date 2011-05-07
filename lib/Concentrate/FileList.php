<?php

/**
 * @category  Tools
 * @package   Concentrate
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Concentrate_FileList
{
	protected $fileList = array();

	public function __construct(array $fileList = array())
	{
		$this->add($fileList);
	}

	public function add($file)
	{
		if (is_string($file)) {
			$file = array($file);
		}

		if (!is_array($file)) {
			throw new InvalidArgumentException(
				'The $file must be either a string or an array.'
			);
		}

		$this->fileList = array_merge($this->fileList, $file);
		$this->fileList = array_unique($this->fileList);
		return $this;
	}

	public function getAsArray()
	{
		return $this->fileList;
	}

	public function contains($file)
	{
		return (in_array($file, $this->fileList));
	}
}

?>
