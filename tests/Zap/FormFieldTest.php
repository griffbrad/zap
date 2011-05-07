<?php

require_once 'PHPUnit/Autoload.php';

require_once dirname(dirname(__FILE__)) . '/bootstrap.php';

class Zap_FormFieldTest extends PHPUnit_Framework_TestCase
{
    protected $_formField;

    public function setUp()
    {
        $this->_formField = new Zap_FormField();
    }

    public function tearDown()
    {
        unset($this->_formField);
    }

    public function testInvisibleDisplay()
    {
        $this->_formField->setVisible(false);

        ob_start();
        $this->_formField->display();
        $out = trim(ob_get_clean());

        $this->assertEquals('', $out);
    }

    public function testDisplayWithNoChildren()
    {
        ob_start();
        $this->_formField->display();
        $out = trim(ob_get_clean());

        $this->assertEquals('', $out);
    }
}
