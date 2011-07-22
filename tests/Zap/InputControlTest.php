<?php

require_once 'PHPUnit/Autoload.php';

require_once dirname(dirname(__FILE__)) . '/bootstrap.php';

class Zap_InputControlTest extends PHPUnit_Framework_TestCase
{
    protected $_entry;

    public function setUp()
    {
        $this->_entry = new Zap_Entry();
    }

    public function tearDown()
    {
        unset($this->_entry);
    }

    /**
     * @expectedException Zap_Exception
     */
    public function testGetNonExistentForm()
    {
        $this->_entry->getForm();  
    }
    
    /**
     * @expectedException Zap_Exception
     */
    public function testGetNonExistentFormWithContainerParent()
    {
        $container = new Zap_Container();
        $container->add($this->_entry);

        $this->_entry->getForm();  
    }
}
