<?php
require_once "../vendor/autoload.php" ;

$pspell= new Chencha\Pspell\Pspell();
$fk_word="wrword";
print_r($pspell->check($fk_word));
print "\n";
$r_word="word";
print_r($pspell->check($r_word));