<?php
/**
 * Created by PhpStorm.
 * User: jacob
 * Date: 10/10/14
 * Time: 02:04
 */

namespace Chencha\Pspell\Mapping;

use Chencha\Pspell\Config;
use Chencha\Pspell\Dictionary;

class PspellLoadMapping implements LoadMapping
{
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var Dictionary
     */
    protected $dictionary;
    /**
     * @var ConfigurationMapping
     */
    protected $mapping;

    /**
     * @Inject
     * @param ConfigurationMapping $mapping
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * @Inject
     *
     * @param Config $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }


    public function doMapping($dictionary)
    {
        $config = $this->config;
        $mapping = $this->mapping->getMapping();
        $mapping->map(function ($item, $key) use ($dictionary, $config) {
            if (!is_null($this->config->get($key))) {
                $item($dictionary, $this->config->get($key));
            }
        });
    }
} 