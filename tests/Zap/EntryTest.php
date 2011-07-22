<?php

require_once 'PHPUnit/Autoload.php';

require_once dirname(dirname(__FILE__)) . '/bootstrap.php';

class Zap_EntryTest extends PHPUnit_Framework_TestCase
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

    public function testDisplayInvisibleEntry()
    {
        $this->_entry->setVisible(false);

        ob_start();
        $this->_entry->display();  
        $out = ob_get_clean();

        $this->assertEquals('', $out);
    }
    
    public function testDisplayWithNoAutocomplete()
    {
        $this->_entry->setAutocomplete(false)
                     ->setId('foo');

        ob_start();
        $this->_entry->display();  
        $out = ob_get_clean();

        $this->assertContains('type="hidden"', $out);
        $this->assertNotContains('id="foo"', $out);
    }
    
    public function testGetInvisibleFocusId()
    {
        $this->_entry->setVisible(false);

        $this->assertNull($this->_entry->getFocusableHtmlId());
    }

    public function testDisplayReadOnly()
    {
        $this->_entry->setReadOnly(true)
                     ->setId('foo');

        ob_start();
        $this->_entry->display();  
        $out = ob_get_clean();

        $this->assertContains('readonly="readonly"', $out);
    }
}
