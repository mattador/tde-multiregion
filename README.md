TDE MultiRegion module
======================

This module switches to an appropriate pre-mapped store dynamically, depending on the remote IP country of origin. This is achieved by doing a look up on a free copy of Maxmind's Geolite 2 Country database:

- http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz

In order to parse the *.mmdb format, it is necessary to also include MaxMind's composer dependency for PHP.

- `php composer.phar require geoip2/geoip2:~2.0`

For this release, I've hard coded the country code to store mapping relationship. This will be added to the admin configuration in a future release. 

Additionally, I've included a "switch" controller which stores a preselected country in a cookie session. If found this cookie value overrides the IP country lookup in an attempt to speed up the store resolution process. I felt that Magento's cookie management abstraction layer added unecessary overhead to what should be a very simple check, hence I've used standard PHP cookie notation instead.

I've also added a list of well known bots which when detected disable the functionality of this module entirely, causing Magento to fall back onto the default store. This was done to avoid indexing duplicate content. The list of bots was harvested from the following location:

- http://www.searchenginedictionary.com/spider-names.shtml

The module assumes that all store views have the same base URL, and that the store/website is not being set through Magento 2's front controller, as described here:
 
- http://devdocs.magento.com/guides/v2.0/config-guide/multi-site/ms_apache.html

Developer Notes and Quirks
==========================

- During development I found it useful to manipulate the IP output of Magento\Framework\HTTP\PhpEnvironment\Request::getClientIp()
- Magento 2's configurable price resolver iterates over sub products (simples) looking for the lowest price of a simple attached to the parent config. This can cause issues if one or more simples have a different pricing scheme (either due to human error or on purpose).
Given our current 1 config per simple setup which we currently use, I have make the necessary adjustments in: app/code/Tde/MultiRegion/Model/ConfigurableProductPriceResolver.php
- In order to get the currency code showing up on product view pages, I found the easiest solution was to make a small change to: app/design/frontend/*/*/Magento_Catalog/web/js/price-box.js