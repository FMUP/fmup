<?php
namespace FMUP\Config;

/**
 * Class RequiredTrait
 * @package FMUP\Config
 */
trait RequiredTrait
{
    private $config;

    /**
     * Define config
     * @param ConfigInterface|null $configInterface
     * @return $this
     */
    public function setConfig(ConfigInterface $configInterface)
    {
        $this->config = $configInterface;
        return $this;
    }

    /**
     * Retrieve defined config
     * @return ConfigInterface
     * @throws Exception if config is not defined
     */
    public function getConfig()
    {
        if (!$this->hasConfig()) {
            throw new Exception('Config must be defined');
        }
        return $this->config;
    }

    /**
     * Check if config exists
     * @return bool
     */
    public function hasConfig()
    {
        return (bool)$this->config;
    }
}
