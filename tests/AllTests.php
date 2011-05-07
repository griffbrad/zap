<?php

require_once dirname(__FILE__) . '/bootstrap.php';

require_once 'PHPUnit/Framework.php';

class AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();

        $suite->addTestSuite('Zap_ObjectTest');
        $suite->addTestSuite('Zap_FrameTest');

        return $suite;
    }
}
