<?php

set_include_path(
    dirname(dirname(__FILE__)) . '/lib'
  . PATH_SEPARATOR
  . get_include_path()
);

require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Zap_');
