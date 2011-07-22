<?php

require_once dirname(__FILE__) . '/bootstrap.php';

require_once 'PHPUnit/Autoload.php';

class AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();

        $suite->addTestSuite('Zap_ObjectTest');
        $suite->addTestSuite('Zap_FrameTest');
        $suite->addTestSuite('Zap_DisplayableContainerTest');
        $suite->addTestSuite('Zap_FormFieldTest');
        $suite->addTestSuite('Zap_InputControlTest');
        $suite->addTestSuite('Zap_EntryTest');
        $suite->addTestSuite('Zap_FlydownTest');

        return $suite;
    }
}
