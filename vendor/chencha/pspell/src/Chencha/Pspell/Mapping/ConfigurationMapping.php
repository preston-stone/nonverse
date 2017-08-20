<?php
/**
 * Created by PhpStorm.
 * User: jacob
 * Date: 26/11/14
 * Time: 20:33
 */

namespace Chencha\Pspell\Mapping;


use Illuminate\Support\Collection;

interface ConfigurationMapping
{
    /**
     * @return Collection
     */
    function getMapping();

} 