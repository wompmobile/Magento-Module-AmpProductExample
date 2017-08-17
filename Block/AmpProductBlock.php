<?php

namespace AlanKent\AmpExample\Block;

class AmpProductBlock extends \Magento\Framework\View\Element\Template
{
    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    private $productRepo;

    /** @var \Magento\Catalog\Api\Data\ProductInterface */
    private $product;

    /**
     * Constructor
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepo 
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepo
    ) {
        parent::__construct($context);
        $this->productRepo = $productRepo;
    }

    public function loadProductWithSku($sku) {
        $this->product = $this->productRepo->get($sku);
        if ($this->product === null) {
            throw new \Exception("Failed to fetch product with SKU '$sku'.");
        }
    }

    public function getProduct() {
        return $this->product;
    }

    public function getImageUrl($product)
    {
        $url = "";
        $attribute = $product->getResource()->getAttribute('image');
        if ($product->getImage() && $attribute) {
            $url = $attribute->getFrontend()->getUrl($product);
        }
        return $url;
    }
}
