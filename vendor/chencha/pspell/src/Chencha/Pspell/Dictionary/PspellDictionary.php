<?php
/**
 * Created by PhpStorm.
 * User: jacob
 * Date: 10/10/14
 * Time: 01:25
 */

namespace Chencha\Pspell\Dictionary;

use Chencha\Pspell\Config;
use Chencha\Pspell\Mapping\LoadMapping;

class PspellDictionary implements Dictionary
{
    protected $dictionary;
    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     */
    /**
     * @var LoadMapping
     */
    protected $mapping;
    protected $dictionary_config;


    /**
     * @Inject
     * @param LoadMapping $mapping
     * @param Config $config
     */
    function __construct(LoadMapping $mapping, Config $config)
    {
        $this->mapping = $mapping;
        $this->config = $config;
        $this->_run();
    }

    protected function _run()
    {
        $this->_loadDictionary();
        $this->_loadConfiguration();
    }

    /**
     * @return mixed
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }

    protected function _loadDictionary()
    {
        $this->dictionary_config = pspell_config_create(
            $this->config->get('language')

        );
        $this->dictionary = pspell_new_config($this->dictionary_config);
    }

    protected function _loadConfiguration()
    {

        $this->mapping->doMapping($this->dictionary_config);
    }


}