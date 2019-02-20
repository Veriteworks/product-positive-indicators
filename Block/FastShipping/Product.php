<?php

namespace MageSuite\ProductPositiveIndicators\Block\FastShipping;

class Product extends \Magento\Framework\View\Element\Template
{
    const XML_PATH_CONFIGURATION_KEY = 'fast_shipping';
    const CACHE_LIFETIME = 300;
    const CACHE_KEY = 'indicator_fast_shipping';

    protected $_template = 'MageSuite_ProductPositiveIndicators::fastshipping/product.phtml';

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var \MageSuite\ProductPositiveIndicators\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\ProductPositiveIndicators\Service\DataProvider\FastShipping
     */
    protected $fastShippingDataProvider;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \MageSuite\ProductPositiveIndicators\Helper\Configuration $configuration,
        \MageSuite\ProductPositiveIndicators\Service\DataProvider\FastShipping $fastShippingDataProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->configuration = $configuration;
        $this->fastShippingDataProvider = $fastShippingDataProvider;
    }

    public function getDeliveryData()
    {
        $config = $this->configuration->getConfig(self::XML_PATH_CONFIGURATION_KEY);

        if(!$config['active'] or !$config['delivery_today_time']){
            return false;
        }

        $deliveryData = unserialize($this->cache->load(self::CACHE_KEY));
        $clearCache = $this->_request->getParam('clear', false);

        if($clearCache or !$deliveryData){
            $this->setCacheLifetime(null);
            $this->setClearCache(true);

            $deliveryData = $this->fastShippingDataProvider->getDeliveryData($config);

            $this->cache->save(serialize($deliveryData), self::CACHE_KEY, [], self::CACHE_LIFETIME);
        }

        return $this->serializer->serialize($deliveryData);
    }
}
