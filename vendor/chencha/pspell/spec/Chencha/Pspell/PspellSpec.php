<?php

namespace spec\Chencha\Pspell;

use Chencha\Pspell\Config;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PspellSpec extends ObjectBehavior
{
    function let(){
        $config=new Config();
        $this->beConstructedWith($config);


    }
    function it_is_initializable()
    {
        $this->shouldHaveType('Chencha\Pspell\Pspell');
    }
    function it_gives_suggestions(){
        $word="good";
        $this->getSuggestions($word);
    }
    function it_checks_valid_word(){
       $this->check("good")->shouldReturn(true);
    }
    function it_checks_invalid_word(){
        $this->check("goaz")->shouldReturn(false);
    }
}
