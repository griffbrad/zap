<?php

require_once 'Zap/Exception.php';

/**
 * Thrown when an object is of the wrong class
 *
 * @package   Zap
 * @copyright 2005-2006 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class Zap_Exception_InvalidClass extends Zap_Exception
{
    /**
     * The object that is of the wrong class
     *
     * @var mixed
     */
    protected $_object = null;

    /**
     * Creates a new invalid class exception
     *
     * @param string $message the message of the exception.
     * @param integer $code the code of the exception.
     * @param mixed $object the object that is of the wrong class.
     */
    public function __construct($message = null, $code = 0, $object = null)
    {
        parent::__construct($message, $code);
        $this->_object = $object;
    }

    /**
     * Gets the object that is of the wrong class
     *
     * @return mixed the object that is of the wrong class.
     */
    public function getObject()
    {
        return $this->_object;
    }
}

