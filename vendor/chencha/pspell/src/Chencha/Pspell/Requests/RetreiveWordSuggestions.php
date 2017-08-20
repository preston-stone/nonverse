<?php
/**
 * Created by PhpStorm.
 * User: jacob
 * Date: 26/11/14
 * Time: 20:23
 */

namespace Chencha\Pspell\Requests;


class RetreiveWordSuggestions extends IsAPspellRequest
{
    function run()
    {
        $this->response = pspell_suggest(
            $this->dictionary->getDictionary(),
            $this->word
        );
    }

} 