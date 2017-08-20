<?php
/**
 * Created by PhpStorm.
 * User: jacob
 * Date: 26/11/14
 * Time: 20:23
 */

namespace Chencha\Pspell\Requests;

use Chencha\Pspell\Validators\CheckValidWord;
use Chencha\Pspell\Dictionary\Dictionary;

abstract class IsAPspellRequest
{
    protected $response;
    protected $word;

    /**
     * @var Dictionary
     */
    protected $dictionary;

    /**
     * @param $word
     * @param Dictionary $dictionary
     */
    function __construct($word, Dictionary $dictionary)
    {
        $this->dictionary = $dictionary;
        new CheckValidWord($word);
        $this->word = $word;
        $this->run();
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }


    abstract function run();

} 