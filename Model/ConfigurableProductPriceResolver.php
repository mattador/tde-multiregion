<?php

namespace Tde\MultiRegion\Model;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurablePriceResolver;
use Magento\ConfigurableProduct\Pricing\Price\FinalPriceResolver;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Tde\MultiRegion\Helper\Config;

class ConfigurableProductPriceResolver extends ConfigurablePriceResolver
{
    /** @var PriceResolverInterface */
    protected $priceResolver;

    /** @var PriceCurrencyInterface */
    protected $priceCurrency;

    /** @var Configurable */
    protected $configurable;

    /**
     * @var Config
     */
    private $config;

    /**
     * ConfigurableProductPriceResolver constructor.
     *
     * @param FinalPriceResolver $priceResolver
     * @param Configurable $configurable
     * @param PriceCurrencyInterface $priceCurrency
     * @param Config $config
     */
    public function __construct(
        FinalPriceResolver $priceResolver, //FinalPriceResolver is forcefully injected
        Configurable $configurable,
        PriceCurrencyInterface $priceCurrency,
        Config $config
    )
    {
        $this->priceResolver = $priceResolver;
        $this->configurable = $configurable;
        $this->priceCurrency = $priceCurrency;
        $this->config = $config;
    }

    /**
     * Instead of looking for the lowest price of all simples attached to config, as Magento does normally,
     * I attempt to make an exact match and extract the mostx appropriate simple price.
     *
     * @see README.md for more context
     *
     * @param \Magento\Framework\Pricing\SaleableInterface|\Magento\Catalog\Model\Product $product
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resolvePrice(\Magento\Framework\Pricing\SaleableInterface $product)
    {
        if (!$this->config->isEnabled()) {
            return parent::resolvePrice($product);
        }
        //Only apply logic if configurable belongs to Monogrammable or Alphabet attribute sets
        $attributeSetId = $product->getAttributeSetId();
        if (!in_array($attributeSetId, [11 /*Monogrammable*/, 12 /*Alphabet*/])) {
            return parent::resolvePrice($product);
        }
        $price = null;
        foreach ($this->configurable->getUsedProducts($product) as $subProduct) {
            $productPrice = $this->priceResolver->resolvePrice($subProduct);
            if ($attributeSetId == 11) { /*Monogrammable*/
                if ($product->getColor() == $product->getColor()) {
                    $price = $productPrice;
                    break;
                }
            } elseif ($attributeSetId == 12) { /*Monogrammable*/
                $letter = substr($product->getSku(), strlen($product->getSku()) - 1);
                if ($letter == substr($subProduct->getSku(), 0, 1)) {
                    $price = $productPrice;
                    break;
                }
            }
        }
        //no appropriate simple to config match found, so fall back on core logic
        if ($price === null) {
            return parent::resolvePrice($product);
        }
        return (float)$price;
    }


}
