<?php

namespace Tde\MultiRegion\Model\Maxmind;

use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface as Logger;

class Country
{

    const DEFAULT_COUNTRY_CODE = 'AU';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var Reader
     */
    private $reader;

    /**
     * Country constructor
     *
     * @param Request $request
     * @param Logger $logger
     */
    public function __construct(Request $request, Logger $logger, Reader $reader, ObjectManagerInterface $objectManager)
    {
        $this->request = $request;
        $this->logger = $logger;
        //@todo The database should be updated regularly as described in README.md
        $this->reader = $objectManager->create(
            'GeoIp2\Database\Reader', [
            'filename' => $reader->getModuleDir('', 'Tde_MultiRegion') . '/countries.mmdb'
        ]);
    }

    /**
     * Returns country ISO 2 char code
     *
     * @return bool|\GeoIp2\Model\Country
     */
    public function getCountryCode()
    {
        $country = self::DEFAULT_COUNTRY_CODE;
        try {
            /** @var string $ip */
            $ip = $this->request->getClientIp();
            if(strlen(trim($ip))){
                /** @var \GeoIp2\Model\Country $country */
                $country = $this->reader->country($ip)->country->isoCode;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $country;
    }

}


