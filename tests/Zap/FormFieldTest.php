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

    public function testDisplayWithEntry()
    {
        $entry = new Zap_Entry('foo');

        $this->_formField->add($entry);

        ob_start();
        $this->_formField->display();
        $out = ob_get_clean();

        $this->assertContains('swat-entry', $out);
    }
    
    public function testDisplayWithRequiredEntry()
    {
        $entry = new Zap_Entry('foo');
        $entry->setRequired(true);

        $this->_formField->setTitle('Foobar')
                         ->add($entry);

        ob_start();
        $this->_formField->display();
        $out = ob_get_clean();

        $this->assertContains('swat-required', $out);
    }
    
    public function testDisplayWithOptionalEntry()
    {
        $entry = new Zap_Entry('foo');

        $this->_formField->setTitle('Foobar')
                         ->setRequiredStatusDisplay(Zap_FormField::DISPLAY_OPTIONAL)
                         ->add($entry);

        ob_start();
        $this->_formField->display();
        $out = ob_get_clean();

        $this->assertContains('swat-optional', $out);
    }

    public function testSetTitle()
    {
        $this->_formField->setTitle('Test');

        $this->assertEquals('Test', $this->_formField->getTitle());
    }

    public function testDisplayNote()
    {
        $entry = new Zap_Entry('foo');

        $this->_formField->setNote('NOTETEST')
                         ->add($entry);
        
        ob_start();
        $this->_formField->display();
        $out = ob_get_clean();

        $this->assertContains('swat-note', $out);
        $this->assertContains('NOTETEST', $out);
    }
    
    public function testDisplayTitleReversed()
    {
        $entry = new Zap_Entry('foo');

        $this->_formField->setTitle('TITLETEST')
                         ->add($entry);
        
        ob_start();
        $this->_formField->display();
        $normal = ob_get_clean();

        $this->_formField->setTitleReversed(true);

        ob_start();
        $this->_formField->display();
        $reversed = ob_get_clean();
        
        $this->assertNotEquals($normal, $reversed);
    }

    public function testControlWithNote()
    {
        $entry = $this->getMock('Zap_Entry');

        $entry->expects($this->any())
              ->method('getNote')
              ->will($this->returnValue(new Zap_Message('TESTCONTROLNOTE')));

        $this->_formField->setTitle('TITLETEST')
                         ->setNote('TESTFIELDNOTE')
                         ->add($entry);
        
        ob_start();
        $this->_formField->display();
        $out = ob_get_clean();

        $this->assertContains('TESTFIELDNOTE', $out);
        $this->assertContains('TESTCONTROLNOTE', $out);
    }

    public function testSetShowColon()
    {
        $this->_formField->setShowColon(false);

        $this->assertFalse($this->_formField->getShowColon());
    }

    public function testDislayNoColon()
    {
        $entry = new Zap_Entry();

        $this->_formField->setTitle('TITLETEST')
                         ->setShowColon(false)
                         ->add($entry);
        
        ob_start();
        $this->_formField->display();
        $out = ob_get_clean();

        $this->assertNotContains(':', $out);
    }

    public function testSetNote()
    {
        $this->_formField->setNote('NOTE');

        $this->assertEquals('NOTE', $this->_formField->getNote());
    }
    
    public function testSetTitleReversed()
    {
        $this->_formField->setTitleReversed(true);

        $this->assertTrue($this->_formField->getTitleReversed());
    }
    
    public function testAddCheckbox()
    {
        $checkbox = new Zap_Checkbox();

        $this->_formField->add($checkbox);

        $this->assertTrue($this->_formField->getTitleReversed());
        $this->assertFalse($this->_formField->getShowColon());
    }
    
    public function testMessages()
    {
        $form = new Zap_Form();
        $form->add($this->_formField);

        $checkbox = new Zap_Checkbox();
        $checkbox->addMessage(
            new Zap_Message('CHECKBOXMESSAGE')
        );

        $this->_formField->add($checkbox);

        $this->_formField->process();

        ob_start();
        $form->display();
        $out = ob_get_clean();

        $this->assertContains('CHECKBOXMESSAGE', $out);
    }
    
    public function testMessageWithNoTitle()
    {
        $form = new Zap_Form();
        $form->add($this->_formField);

        $checkbox = new Zap_Checkbox();

        $this->_formField->add($checkbox);
        
        $checkbox->addMessage(
            new Zap_Message('CHECKBOXMESSAGE')
        );

        $this->_formField->process();

        ob_start();
        $form->display();
        $out = ob_get_clean();

        $this->assertContains('CHECKBOXMESSAGE', $out);
    }
    
    public function testMessageWithXmlTitle()
    {
        $form = new Zap_Form();
        $form->add($this->_formField);

        $checkbox = new Zap_Checkbox();

        $this->_formField->setTitle('<em>Test</em>')
                         ->setTitleContentType('text/xml')
                         ->add($checkbox);
        
        $checkbox->addMessage(
            new Zap_Message('CHECKBOXMESSAGE')
        );

        $this->_formField->process();

        ob_start();
        $form->display();
        $out = ob_get_clean();

        $this->assertContains('CHECKBOXMESSAGE', $out);

        $this->assertContains('<em>Test</em>', $out);
    }
    
    public function testMessageWithSecondaryContent()
    {
        $form = new Zap_Form();
        $form->add($this->_formField);

        $message = new Zap_Message('CHECKBOXMESSAGE');
        $message->setSecondaryContent('SECONDARY');

        $checkbox = new Zap_Checkbox();
        $this->_formField->setTitle('TESTFIELD')
                         ->add($checkbox);
        $checkbox->addMessage($message);

        $this->_formField->process();

        ob_start();
        $form->display();
        $out = ob_get_clean();

        $this->assertContains('CHECKBOXMESSAGE', $out);
        $this->assertContains('SECONDARY', $out);
    }

    public function testSetTitleContentType()
    {
        $this->_formField->setTitleContentType('text/xml');

        $this->assertEquals('text/xml', $this->_formField->getTitleContentType());
    }
    
    public function testSearchEntry()
    {
        $entry = new Zap_SearchEntry('search');
        $this->_formField->add($entry);

        $this->assertFalse($this->_formField->getShowColon());
    }
}
