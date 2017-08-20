<?php
require_once "../vendor/autoload.php" ;
$config= new \Chencha\Pspell\Config(null,"en","en",1);
$pspell= new Chencha\Pspell\Pspell();
$word="Som";
$suggestions=$pspell->getSuggestions($word);
print_r($suggestions);