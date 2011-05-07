<?php

require_once 'PHPUnit/Autoload.php';

require_once dirname(dirname(__FILE__)) . '/bootstrap.php';

class Zap_DisplayableContainerTest extends PHPUnit_Framework_TestCase
{
    protected $_container;

    public function setUp()
    {
        $this->_container = new Zap_DisplayableContainer();
    }

    public function tearDown()
    {
        unset($this->_container);
    }

    public function testInvisibleDisplay()
    {
        $this->_container->setVisible(false);

        ob_start();
        $this->_container->display();
        $out = trim(ob_get_clean());

        $this->assertEquals('', $out);
    }
    
    public function testDisplay()
    {
        ob_start();
        $this->_container->display();
        $out = trim(ob_get_clean());

        $this->assertContains('<div', $out);
    }

    public function testCssClasses()
    {
        $this->_container->addClass('test-class');

        ob_start();
        $this->_container->display();
        $out = trim(ob_get_clean());

        $this->assertContains('test-class', $out);
    }
}
