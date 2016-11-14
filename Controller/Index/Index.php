<?php
namespace Tde\MultiRegion\Controller\Index;

use Magento\Catalog\Model\Product\WebsiteFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\Controller\ResultFactory;
use Tde\MultiRegion\Helper\Config;
use Tde\MultiRegion\Model\Maxmind\Country;

class Index extends Action
{

    /**
     * @var Config
     */
    private $config;

    /**
     * Index constructor.
     * @param ActionContext $context
     * @param Config $config
     */
    public function __construct(
        ActionContext $context,
        Config $config
    )
    {
        parent::__construct($context);
        $this->config = $config;
    }

    /**
     * Manually allow user to set country code
     * @return void
     */
    public function execute()
    {
        $countryCode = strtoupper($this->getRequest()->getParam('region', Country::DEFAULT_COUNTRY_CODE));
        //echo $this->config->resolveWebsiteStoreId($countryCode); exit;
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
