<?php

set_include_path(
    dirname(dirname(__FILE__)) . '/lib'
);

require_once 'Zap.php';

require_once 'Zap/Frame.php';
$frame = new Zap_Frame('test');
$frame->title = 'Test';

require_once 'Zap/FormField.php';
$field = new Zap_FormField();
$field->title = 'Example Entry';
$frame->add($field);

require_once 'Zap/Entry.php';
$entry = new Zap_Entry('entry');
$entry->required = true;
$field->add($entry);

require_once 'Zap/HtmlHeadEntrySetDisplayer.php';

ob_start();
$frame->display();
$contents = ob_get_clean();

$concentrator = new Concentrate_Concentrator();
$displayer    = new Zap_HtmlHeadEntrySetDisplayer($concentrator);

?>
<html>
    <head>
        <?php $displayer->display($frame->getHtmlHeadEntrySet()) ?>
    </head>
    <body>
        <?php echo $contents ?>

        <pre>
        <?php var_dump(get_included_files()) ?>
        </pre>
    </body>
</html>

