<?php

$lib = dirname(dirname(__FILE__)) . '/lib';
set_include_path($lib);

require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Zap_');

$form = new Zap_Form('form');

$frame = new Zap_Frame();
$frame->setTitle('Example');
$form->add($frame);

$messageDisplay = new Zap_MessageDisplay('display');
$frame->add($messageDisplay);

$field = new Zap_FormField();
$field->setTitle('Useless Field')
      ->setNote('Fill it out if you insist.  It does nothing.');
$frame->add($field);

$entry = new Zap_Entry('test');
$entry->setRequired(true);
$field->add($entry);


$field = new Zap_FormField();
$field->setTitle('Useless Flydown')
      ->setNote('Fill it out if you insist.  It does nothing.');
$frame->add($field);

$flydown = new Zap_Flydown('flydown');
$flydown->setRequired(true)
        ->addOption(1, 'Apple')
        ->addOption(2, 'Orange');
$field->add($flydown);


$field = new Zap_FormField();
$field->setTitle('Useless Radio List')
      ->setNote('This is useless, too.');
$frame->add($field);

$radioList = new Zap_RadioList('radioList');
$radioList->addOption(1, 'Tan')
          ->addOption(2, 'Bloody')
          ->addOption(3, 'White');
$field->add($radioList);


$field = new Zap_FormField();
$field->setTitle('Date Entry')
      ->setNote("It's a date picker, which uses composite widgets.");

$dateEntry = new Zap_DateEntry('date');
$field->add($dateEntry);

$frame->add($field);

$footer = new Zap_FooterFormField();
$frame->add($footer);

$button = new Zap_Button('submit');
$button->setTitle('Submit Useless Form');
$footer->add($button);

$form->process();

if ($form->isSubmitted()) {
    $message = new Zap_Message(
        'You submitted a form.  Mark that under "Who gives a shit".',
        Zap_Message::NOTIFICATION
    );

    $messageDisplay->add($message);
} else {
    $message = new Zap_Message(
        'Welcome to my form.',
        Zap_Message::NOTIFICATION
    );

    $messageDisplay->add($message);
}

ob_start();
$form->display();
$content = ob_get_clean();

$concentrator = new Concentrate_Concentrator();
$displayer    = new Zap_HtmlHeadEntrySetDisplayer($concentrator);
?>
<html>
    <head>
        <?php $displayer->display($form->getHtmlHeadEntrySet());?>
    </head>
    <body>
        <?php echo $content;?>
    </body>
</html>
