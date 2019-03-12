<?php

namespace MageSuite\ProductPositiveIndicators\Block\ExpectedDelivery;

class Product extends \Magento\Framework\View\Element\Template
{

    const CACHE_KEY = 'indicator_expected_delivery_%s_%s_%s';

    protected $_template = 'expecteddelivery/product.phtml';

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var \MageSuite\ProductPositiveIndicators\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \MageSuite\ProductPositiveIndicators\Helper\Configuration\ExpectedDelivery
     */
    protected $configuration;

    /**
     * @var \MageSuite\ProductPositiveIndicators\Service\DataProvider\ExpectedDelivery
     */
    protected $expectedDeliveryDataProvider;

    protected $deliveryData = null;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \MageSuite\ProductPositiveIndicators\Helper\Product $productHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\ProductPositiveIndicators\Helper\Configuration\ExpectedDelivery $configuration,
        \MageSuite\ProductPositiveIndicators\Service\DataProvider\ExpectedDelivery $expectedDeliveryDataProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->productHelper = $productHelper;
        $this->storeManager = $storeManager;
        $this->configuration = $configuration;
        $this->expectedDeliveryDataProvider = $expectedDeliveryDataProvider;
    }

    public function isEnabled()
    {
        return $this->configuration->isEnabled();
    }

    public function getMaxTimeToday()
    {
        return $this->getDeliveryDataByKey('max_time_today');
    }

    public function getShipDayTime()
    {
        return $this->getDeliveryDataByKey('ship_day_time');
    }

    public function getShipDayName()
    {
        return $this->getDeliveryDataByKey('ship_day_name');
    }

    public function getNextShipDayTime()
    {
        return $this->getDeliveryDataByKey('next_ship_day_time');
    }

    public function getNextShipDayName()
    {
        return $this->getDeliveryDataByKey('next_ship_day_name');
    }

    public function getUtcOffset()
    {
        return $this->getDeliveryDataByKey('utc_offset');
    }

    protected function getDeliveryDataByKey($key)
    {
        $deliveryData = $this->getDeliveryData();

        if(empty($deliveryData)){
            return null;
        }

        return $deliveryData->getData($key);
    }

    protected function getDeliveryData()
    {
        if(!$this->configuration->isEnabled() or !$this->configuration->getDeliveryTodayTime()){
            return false;
        }

        if($this->deliveryData === null){
            $product = $this->productHelper->getProduct();

            if(!$product){
                return $this->deliveryData;
            }

            $cacheKey = $this->getCacheKeyForProductId($product->getId());

            $deliveryData = unserialize($this->cache->load($cacheKey));

            if(!$deliveryData){
                $deliveryData = $this->expectedDeliveryDataProvider->getDeliveryData($product);
                $this->cache->save(serialize($deliveryData), $cacheKey);
            }

            $this->deliveryData = $deliveryData;
        }

        return $this->deliveryData;
    }

    protected function getCacheKeyForProductId(int $productId)
    {
        return sprintf(
            self::CACHE_KEY,
            $productId,
            $this->storeManager->getStore()->getId(),
            date('d')
        );
    }

}
