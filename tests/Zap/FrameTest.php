<?php

require_once 'PHPUnit/Autoload.php';

require_once dirname(dirname(__FILE__)) . '/bootstrap.php';

class Zap_FrameTest extends PHPUnit_Framework_TestCase
{
    protected $_frame;

    public function setUp()
    {
        $this->_frame = new Zap_Frame();
    }

    public function tearDown()
    {
        unset($this->_frame);
    }

    public function testSetTitle()
    {
        $this->_frame->setTitle('Test');

        $this->assertEquals('Test', $this->_frame->getTitle());
    }
    
    public function testSetSubtitle()
    {
        $this->_frame->setSubtitle('Test');

        $this->assertEquals('Test', $this->_frame->getSubtitle());
    }

    public function testSetTitleSeparator()
    {
        $this->_frame->setTitleSeparator('%');

        $this->assertEquals('%', $this->_frame->getTitleSeparator());
    }

    public function testSetTitleContentType()
    {
        $this->_frame->setTitleContentType('text/xml');

        $this->assertEquals('text/xml', $this->_frame->getTitleContentType());
    }
    
    public function testSetHeaderLevel()
    {
        $this->_frame->setHeaderLevel(6);

        $this->assertEquals(6, $this->_frame->getHeaderLevel());
    }

    public function testDisplayInvisible()
    {
        $this->_frame->setVisible(false);

        ob_start();
        $this->_frame->display();
        $out = trim(ob_get_clean());

        $this->assertEmpty($out);
    }

    public function testDisplayNoSubtitle()
    {
        $this->_frame->setTitle('Foo')
                     ->setTitleSeparator('%%%');

        ob_start();
        $this->_frame->display();
        $out = ob_get_clean();

        $this->assertNotContains(
            $this->_frame->getTitleSeparator(),
            $out
        );
    }

    public function testDisplayWithSubtitle()
    {
        $this->_frame->setTitle('Foo')
                     ->setTitleSeparator('%%%')
                     ->setSubtitle('Bar');

        ob_start();
        $this->_frame->display();
        $out = ob_get_clean();

        $this->assertContains(
            $this->_frame->getTitleSeparator(),
            $out
        );
    }

    public function testDefaultHeaderLevel()
    {
        $this->_frame->setTitle('Foo');

        ob_start();
        $this->_frame->display();
        $out = ob_get_clean();

        $this->assertContains(
            '</h2>',
            $out
        );
    }
    
    public function testCustomHeaderLevel()
    {
        $this->_frame->setTitle('Foo')
                     ->setHeaderLevel(5);

        ob_start();
        $this->_frame->display();
        $out = ob_get_clean();

        $this->assertContains(
            '</h5>',
            $out
        );
    }

    public function testShallowNestedHeaderLevel()
    {
        $outer = new Zap_Frame();
        $outer->setTitle('Foo')
              ->addChild($this->_frame);

        $this->_frame->setTitle('Bar');

        ob_start();
        $this->_frame->display();
        $out = ob_get_clean();

        $this->assertContains('</h3>', $out);
    }
    
    public function testDeepNestedHeaderLevel()
    {
        $inner = new Zap_Frame();
        $inner->setTitle('Bar')
              ->addChild($this->_frame);

        $outer = new Zap_Frame();
        $outer->setTitle('Foo')
              ->addChild($inner);

        $this->_frame->setTitle('Baz');

        ob_start();
        $this->_frame->display();
        $out = ob_get_clean();

        $this->assertContains('</h4>', $out);
    }
    
    public function testDeepNestedHeaderLevelWithContainer()
    {
        $inner = new Zap_Container();
        $inner->addChild($this->_frame);

        $outer = new Zap_Frame();
        $outer->setTitle('Foo')
              ->addChild($inner);

        $this->_frame->setTitle('Baz');

        ob_start();
        $this->_frame->display();
        $out = ob_get_clean();

        $this->assertContains('</h3>', $out);
    }
    
    public function testDeeperNestedHeaderLevel()
    {
        $inner = new Zap_Frame();
        $inner->setTitle('Bar')
              ->addChild($this->_frame);

        $mid = new Zap_Frame();
        $mid->setTitle('Gah')
            ->addChild($inner);

        $outer = new Zap_Frame();
        $outer->setTitle('Foo')
              ->addChild($mid);

        $this->_frame->setTitle('Baz');

        ob_start();
        $this->_frame->display();
        $out = ob_get_clean();

        $this->assertContains('</h5>', $out);
    }
}
