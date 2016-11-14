<?php

namespace Tde\MultiRegion\Plugins\Directory;

use Magento\Directory\Model\Currency\Interceptor;
use Tde\MultiRegion\Helper\Config;

class Currency
{

    /**
     * @var Config
     */
    private $config;

    /**
     * Currency constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Adds currency code to price box
     *
     * @param Interceptor $subject
     * @param $priceRendered
     * @return mixed
     */
    public function afterformatPrecision(Interceptor $subject, $priceRendered)
    {
        if (!$this->config->isEnabled()) {
            return $priceRendered;
        }
        return str_replace(
            '<span class="price">',
            '<span class="price"><span class="tde-currency-code" style="vertical-align: super; font-size:8px">' . $subject->getCode() . '</span> '
            , $priceRendered
        );
    }

}