<?php
/**
 * Created by PhpStorm.
 * User: jacob
 * Date: 13/10/14
 * Time: 13:00
 */

namespace Chencha\Pspell\Validators;


use Chencha\Pspell\Exceptions\InvalidWordProvided;

class CheckValidWord
{

    function __construct($word)
    {
        if (!ctype_alnum($word)) {
            throw new InvalidWordProvided($word);
        }

    }
}