<?php
/**
 * Created by PhpStorm.
 * User: jacob
 * Date: 26/11/14
 * Time: 20:37
 */

namespace Chencha\Pspell;


use Chencha\Pspell\Mapping\LoadMapping;
use Chencha\Pspell\Mapping\PspellLoadMapping;
use Chencha\Pspell\Mapping\ConfigurationMapping;
use Chencha\Pspell\Mapping\PspellConfigurationMapping;
use Chencha\Pspell\Dictionary\Dictionary;
use Chencha\Pspell\Dictionary\PspellDictionary;

class Container
{

    /**
     * @var \DI\Container
     */
    public $container;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var Dictionary
     */
    protected $dictionary;

    public function __construct(Config $config)
    {

        $builder = new \DI\ContainerBuilder();
        $this->container = $builder->build();
        $this->config = $config;
        $this->_setConfig();
        $this->_setConfigurationMapping();
        $this->_setLoadMapping();
        $this->_setDictionary();
    }


    /**
     * @return \DI\Container
     */
    public function getContainer()
    {
        return $this->container;

    }

    protected function _setConfig()
    {
        $this->container->set(Config::class, $this->config);
    }

    protected function _setConfigurationMapping()
    {
        $this->container->set(
            ConfigurationMapping::class,
            new PspellConfigurationMapping()
        );
    }

    protected function _setLoadMapping()
    {
        $this->container->set(
            LoadMapping::class,
            $this->container->make(PspellLoadMapping::class)
        );
    }

    protected function _setDictionary()
    {

        $this->dictionary = $this->container->make(PspellDictionary::class);

    }

    /**
     * @return mixed
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }


}