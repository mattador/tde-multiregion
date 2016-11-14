<?php

namespace Tde\MultiRegion\Console\Command;

use Magento\Catalog\Model\Product\WebsiteFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RegenerateProductUrlCommand
 * @package Tde\MultiRegion\Console\Command
 */
class RegenerateProductUrlCommand extends Command
{

    /**
     * @var ProductUrlRewriteGenerator
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var ProductRepositoryInterface
     */
    protected $collection;

    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * RegenerateProductUrlCommand constructor.
     * @param \Magento\Framework\App\State $state
     * @param Collection $collection
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        Collection $collection,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        ProductFactory $productFactory,
        WebsiteFactory $websiteFactory
    )
    {
        //$state->setAreaCode('adminhtml');
        $this->collection = $collection;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->productFactory = $productFactory;
        $this->websiteFactory = $websiteFactory;

        parent::__construct();
    }

    /**
     * Regenerate product URL's
     *
     * @see vendor/magento/module-url-rewrite/Model/Storage/DbStorage.php
     * @param InputInterface $inp
     * @param OutputInterface $out
     */
    public function execute(InputInterface $inp, OutputInterface $out)
    {
        $storeIds = [];
        $reschedule = []; //Duplicate url_keys
        $productIds = [];

        //Assign all products to all stores
        $this->websiteFactory->create()->addProducts($storeIds, $productIds);

        //Regenerate product URLs -  if necessary run `DELETE FROM url_rewrite WHERE entity_type = 'product'` at your own discretion
        foreach ($storeIds as $storeId) {
            $this->collection->addStoreFilter($storeId)->setStoreId($storeId);
            $this->collection->addIdFilter($productIds);
            $this->collection->addAttributeToSelect(['url_path', 'url_key']);
            $list = $this->collection->load();
            foreach ($list as $product) {
                //echo $product->getId() . ' - ' . $product->getSku() . PHP_EOL;
                $product->setStoreId($storeId);
                try {
                    $this->urlPersist->replace(
                        $this->productUrlRewriteGenerator->generate($product)
                    );
                } catch (\Exception $e) {
                    //Magento\UrlRewrite\Model\Storage\DbStorage::insertMultiple()
                    if ($e->getMessage() == 'URL key for specified store already exists.') {
                        $reschedule[] = $product->getId();
                    }
                    echo $e->getMessage();
                }
            }
        }
        //Deal with duplicate URL keys manually
        echo implode(',', array_unique($reschedule));

    }

    protected function configure()
    {
        $this->setName('multiregion:urls:regenerate')
            ->setDescription('Regenerate product entity URL\'s');
        return parent::configure();
    }
}