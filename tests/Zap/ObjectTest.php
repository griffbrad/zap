<?php

require_once 'PHPUnit/Autoload.php';

require_once dirname(dirname(__FILE__)) . '/bootstrap.php';

class Zap_ObjectTest extends PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $object = new Zap_Object();

        ob_start();
        echo $object;
        $out = ob_get_clean();

        $this->assertContains('object(Zap_Object)', $out);
    }
}
