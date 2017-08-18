<?php

namespace AlanKent\AmpExample\Block;

use Magento\Framework\App\Filesystem\DirectoryList;

class AmpProductBlock extends \Magento\Framework\View\Element\Template
{
    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    private $productRepo;

    /** @var \Magento\Catalog\Api\Data\ProductInterface */
    private $product;

    /** @var \Magento\Framework\Escaper */
    protected $_escaper;

    /** @var \Magento\Framework\UrlInterface */
    protected $urlBuilder;

    /**
     * Constructor
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepo
     */
    public function __construct(
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepo
    ) {
        $this->_escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
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

    public function getPrice() {
        return number_format($this->product->getPrice(), 2, null, '');
    }

    public function getBaseImageUrl($product)
    {
        $url = "";
        $attribute = $product->getResource()->getAttribute('image');
        if ($product->getImage() && $attribute) {
            $url = $attribute->getFrontend()->getUrl($product);
        }
        return $url;
    }

    public function escapeHtml($html, $allowedTags = NULL)
    {
        return $this->_escaper->escapeHtml($html, $allowedTags);
    }

    public function getGalleryInfo() {
        $galleryEntries = $this->product->getMediaGalleryEntries();
        if ($galleryEntries === null) {
            return array();
        }

        $galleryInfo = array();
        $keys = array('url', 'height', 'width');

        foreach ($galleryEntries as $galleryEntry) {
            $values = array();
            if (!$galleryEntry->isDisabled()) {
                $values[] = $this->getImageUrl($galleryEntry);

                $imageDimensions = $this->getImageDimensions($galleryEntry);
                $values[] = $imageDimensions[0];
                $values[] = $imageDimensions[1];

                $galleryInfo[] = array_combine($keys, $values);
            }
        }

        return $galleryInfo;
    }

    public function getImageUrl($galleryEntry)
    {
        $mediaUrl = $this->urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);
        $mediaUrl .= 'catalog/product';

        return $mediaUrl . $galleryEntry->getFile();
    }

    public function getImageDimensions($galleryEntry)
    {
        $mediaDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);

        $relativePath = 'catalog/product' . $galleryEntry->getFile();
        $absolutePath = $mediaDir->getAbsolutePath($relativePath);

        if ($mediaDir->isFile($relativePath)) {
            return getimagesize($absolutePath);
        }

        return [0, 0];
    }

}
