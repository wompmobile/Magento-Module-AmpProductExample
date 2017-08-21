<?php

namespace AlanKent\AmpExample\Block;

use Magento\Framework\App\Filesystem\DirectoryList;

class AmpProductBlock extends \Magento\Framework\View\Element\Template
{
    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    private $productRepo;

    /** @var \Magento\Catalog\Api\Data\ProductInterface */
    private $product;

    /** @var \Magento\Framework\Data\Form\FormKey */
    protected $formKey;

    /** @var \Magento\Framework\Escaper */
    protected $_escaper;

    /** @var \Magento\Framework\UrlInterface */
    protected $urlBuilder;

    /**
     * Constructor
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepo
     */
    public function __construct(
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepo
    ) {
        $this->formKey = $formKey;
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

    public function getProductId() {
        return $this->product->getId();
    }

    public function getProductPrice() {
        return number_format($this->product->getPrice(), 2, null, '');
    }

    public function getProductImageUrl()
    {
        $url = "";
        $attribute = $this->product->getResource()->getAttribute('image');
        if ($this->product->getImage() && $attribute) {
            $url = $attribute->getFrontend()->getUrl($this->product);
        }
        return $url;
    }

    public function escapeHtml($html, $allowedTags = NULL)
    {
        return $this->_escaper->escapeHtml($html, $allowedTags);
    }

    public function getProductGalleryInfo()
    {
        $galleryEntries = $this->product->getMediaGalleryEntries();
        if ($galleryEntries === null) {
            return array();
        }

        $galleryInfo = array();
        $keys = array('url', 'width', 'height');

        // Build array of product images
        foreach ($galleryEntries as $galleryEntry) {
            $values = array();
            if (!$galleryEntry->isDisabled() && $this->isImage($galleryEntry)) {
                $values[] = $this->getImageUrl($galleryEntry);

                $imageDimensions = $this->getImageDimensions($galleryEntry);
                $values[] = $imageDimensions[0];
                $values[] = $imageDimensions[1];

                $galleryInfo[] = array_combine($keys, $values);
            }
        }

        // Provide placeholder image if no gallery images
        if (count($galleryInfo) < 1) {
            $values = array();
            $values[] = $this->getViewFileUrl('AlanKent_AmpExample::img/placeholder.jpg');

            $imageDimensions = $this->getImageDimensions($galleryEntry);
            $values[] = $imageDimensions[0];
            $values[] = $imageDimensions[1];

            $galleryInfo[] = array_combine($keys, $values);
        }

        return $galleryInfo;
    }

    public function isImage($galleryEntry)
    {
        $mediaDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);

        $relativePath = 'catalog/product' . $galleryEntry->getFile();
        $absolutePath = $mediaDir->getAbsolutePath($relativePath);

        return @is_array(getimagesize($absolutePath));
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

    public function getFormKey() {
        return $this->formKey->getFormKey();
    }

}
