<?php
namespace Tde\MultiRegion\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreResolver\ReaderList;
use Tde\MultiRegion\Helper\Config;

class StoreResolver extends \Magento\Store\Model\StoreResolver
{


    /**
     * @var \Tde\MultiRegion\Model\Config
     */
    protected $config;

    /** @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager */
    protected $cookieMetadataManager;

    /**
     * @var Country
     */
    protected $maxMindCountry;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * StoreResolver constructor.
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param RequestInterface $request
     * @param FrontendInterface $cache
     * @param ReaderList $readerList
     * @param string $runMode
     * @param null $scopeCode
     * @param Config $config
     * @param Country $maxMindCountry
     */
    public function __construct(
        StoreRepositoryInterface $storeRepository,
        StoreCookieManagerInterface $storeCookieManager,
        RequestInterface $request,
        FrontendInterface $cache,
        ReaderList $readerList,
        $runMode = ScopeInterface::SCOPE_STORE,
        $scopeCode,
        Config $config
    )
    {
        if (is_null($runMode)) {
            $runMode = ScopeInterface::SCOPE_STORE;
        }
        parent::__construct(
            $storeRepository,
            $storeCookieManager,
            $request,
            $cache,
            $readerList,
            $runMode,
            $scopeCode
        );
        $this->config = $config;
    }

    /**
     * @return int|null|string
     */
    public function getCurrentStoreId()
    {
        $storeId = parent::getCurrentStoreId();
        //when debugging debugging
        //return $storeId;

        if (!$this->config->useMultiRegion()) {
            return $storeId;
        }
        $newStoreId = $this->config->resolveWebsiteStoreId();
        if (!is_int($newStoreId)) {
            return $storeId;
        }
        if ($storeId != $newStoreId) {
            //$this->storeManager->reinitStores();
        }
        return $newStoreId;
    }
}