<?php

namespace Tde\MultiRegion\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Tde\MultiRegion\Model\Crawlers\Verify;
use Tde\MultiRegion\Model\Maxmind\Country;

//use Magento\Framework\App\Area;

class Config
{

    const PATH_STATUS_IS_ACTIVE = 'tde_multiregion/general/active';
    const MULTI_REGION_COOKIE = 'multi-region-country-code';

    /**
     * @var Verify
     */
    protected $bots;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var Country
     */
    protected $maxMindCountry;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param State $state
     * @param Verify $bots
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        State $state,
        Verify $bots,
        Country $maxMindCountry
    )
    {
        $this->state = $state;
        $this->config = $scopeConfig;
        $this->bots = $bots;
        $this->maxMindCountry = $maxMindCountry;
    }

    /**
     * Map of country code to respective store id (see store_website resource entity)
     *
     * @todo - Store the mapping in the admin (see core_config_data)
     * @deprecated
     * @var array
     */
    public $staticWebsiteMap = [
        'AU' => 1,
        'NZ' => 2,
        'US' => 3
    ];

    /**
     * @return bool
     */
    public function useMultiRegion()
    {
        $active = (bool)(int)$this->config->getValue(self::PATH_STATUS_IS_ACTIVE);
        if (!$active) {
            return false;
        }
        //@todo - I ran out of time working out a better way to handle knowing if user is currently in admin area, since at this point the area code is not set.
        if (@isset($_COOKIE['admin'])) {
            return false;
        }
        /*if ($this->state->getAreaCode() != Area::AREA_FRONTEND) {
            return false;
        }*/
        if ($this->bots->isBot()) {
            return false;
        }
        return true;
    }

    /**
     * Static mapping of country codes to relevant store Id's
     *
     * @todo - Leverage native magento cookie mangement (I ran into issues, so I'ved used native PHP controls for now...)
     *
     * @return int
     */
    public function resolveWebsiteStoreId($manualCountryCode = null)
    {
        //first check for custom cookie containing country code
        $countryCode = null;

        if (!is_null($manualCountryCode) && array_key_exists($_COOKIE[Config::MULTI_REGION_COOKIE], $this->staticWebsiteMap)) {
            $countryCode = $manualCountryCode;
        } elseif (isset($_COOKIE[Config::MULTI_REGION_COOKIE]) && array_key_exists($_COOKIE[Config::MULTI_REGION_COOKIE], $this->staticWebsiteMap)) {
            $countryCode = $_COOKIE[Config::MULTI_REGION_COOKIE];
        }
        //No cookie found or invalid cookie value falls back on actual IP look up
        if (!$countryCode) {
            //Should 100% always return a country code...
            $countryCode = $this->maxMindCountry->getCountryCode();
        }
        //set cookie for speeding up next request from user
        //if (!isset($_COOKIE[Config::MULTI_REGION_COOKIE])) {
        \setcookie(Config::MULTI_REGION_COOKIE, $countryCode, time() + (3600 * 12), '/');
        //}
        return $this->staticWebsiteMap[$countryCode];
    }

}
