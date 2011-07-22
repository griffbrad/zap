<?php

require_once 'PHPUnit/Autoload.php';

require_once dirname(dirname(__FILE__)) . '/bootstrap.php';

class Zap_FlydownTest extends PHPUnit_Framework_TestCase
{
    protected $_flydown;

    public function setUp()
    {
        $this->_flydown = new Zap_Flydown();
    }

    public function tearDown()
    {
        unset($this->_flydown);
    }

    public function testAddOptionsByArray()
    {
        $options = array(
            1 => 'Apple',
            2 => 'Orange'
        );

        $this->_flydown->addOptionsByArray($options);

        $this->assertEquals(2, count($this->_flydown->getOptions()));
    }
}
