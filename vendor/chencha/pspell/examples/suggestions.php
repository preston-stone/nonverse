<?php
require_once "../vendor/autoload.php" ;

$pspell= new Chencha\Pspell\Pspell();
$word="Somer";
$suggestions=$pspell->getSuggestions($word);
print_r($suggestions);