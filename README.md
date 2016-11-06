TDE MultiRegion module
======================

This module switches to an appropriate pre-mapped stores dynamically, depending on the remote IP country of origin. This achieved by doing a quick look up on a copy of the free Maxmind Geolite Country database, which is available here:

- http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz

In order to parse this format, it is necessary to include the following composer dependency in Magento.

- `php composer.phar require geoip2/geoip2:~2.0`

Currently, the country code to store mapping is static. This will be added to the admin in a future release.

Additionally, I've included a "switch" controller which essentially stores a preselected country in a cookie session, and overrides the IP country lookup on each request.

I also added a list of well known bots which when detected invalidate the functionality of this module entirely, causing Magento to fall back onto the default store. This is to avoid indexing duplicate content. The list was harvested from the following location:

- http://www.searchenginedictionary.com/spider-names.shtml

The module assumes that all store views have the same base URL, and that the store/website is not set through Magento 2's front controller, as explained here:
 
- http://devdocs.magento.com/guides/v2.0/config-guide/multi-site/ms_apache.html

- Tip: For testing purposes locally, I manipulated the IP output of Magento\Framework\HTTP\PhpEnvironment\Request::getClientIp()