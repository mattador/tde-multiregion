<?php

namespace Tde\MultiRegion\Model\Crawlers;

use \Magento\Framework\HTTP\PhpEnvironment\Request;

class Verify
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * Incomplete list of common scrapper bots
     *
     * @var array
     */
    protected $agents = [
        'Arachnoidea',
        'Googlebot',
        'Gigabot',
        'Gulper',
        'ia_archiver',
        'MantraAgent',
        'MSN',
        'Scrubby',
        'Slurp',
        'Bingbot',
        'BingPreview',
        'DuckDuckBot'
    ];

    /**
     * Verify constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Soft checks if user agent is bot
     *
     * @return bool
     */
    public function isBot()
    {
        preg_match('/' . implode('|', $this->agents) . '/i', $this->request->getServerValue('HTTP_USER_AGENT'), $matches);
        return count($matches) > 0;
    }


}