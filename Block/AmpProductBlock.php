<?php

namespace AlanKent\AmpExample\Block;

use Magento\Framework\App\Filesystem\DirectoryList;

class AmpProductBlock extends \Magento\Framework\View\Element\Template
{
    /** @var \Magento\Catalog\Model\Category */
    protected $categoryModel;

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
     * @param \Magento\Catalog\Model\Category $categoryModel
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepo
     */
    public function __construct(
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Category $categoryModel,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepo
    ) {
        $this->formKey = $formKey;
        $this->_escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
        $this->categoryModel = $categoryModel;
        $this->productRepo = $productRepo;
    }

    public function getProductParam() {
        $sku = $_GET["sku"];
        if ($sku === "") {
            throw new \Exception("Product SKU missing.");
        }

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

    private function getCategoriesInfo()
    {
        $category = $this->categoryModel;
        $tree = $category->getTreeModel()->load();
        $ids = $tree->getCollection()->getAllIds();

        $categoriesInfo = array();
        $keys = array('id', 'name', 'url', 'level', 'children');

        foreach ($ids as $id) {
            if ($id == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                continue;
            }

            $category->load($id);
            $level = $category->getLevel();

            $values = array();
            $values[] = $category->getId();
            $values[] = $category->getName();
            $values[] = $level > 1 ? $category->getCategoryIdUrl() : "";
            $values[] = $level;

            // Why does getAllChildren() include the id of self?
            $children = $category->getAllChildren(true);
            unset($children[array_search($category->getId(), $children)]);
            $values[] = $children;

            $categoriesInfo[] = array_combine($keys, $values);
        }

        return $categoriesInfo;
    }

    private function generateChildCategoriesHTML($categoriesInfo, $children, $level)
    {
        if (!$children) {
            return "";
        }

        $html = "";

        foreach ($categoriesInfo as $categoryInfo) {
            if (in_array($categoryInfo['id'], $children) && $categoryInfo['level'] == $level) {
                $html .= '<ul class="categories">' .
                         '<li class="category">' .
                         '<a class="category-link" href="' . $categoryInfo['url']  . '">' . $categoryInfo['name'] . '</a>' .
                         $this->generateChildCategoriesHTML($categoriesInfo, $categoryInfo['children'], $level+1) .
                         '</li>' .
                         '</ul>';
            }
        }

        return $html;
    }

    // TODO: This is not efficient
    public function generateCategoriesHTML()
    {
        $html = "";
        $categoriesInfo = $this->getCategoriesInfo();

        foreach ($categoriesInfo as $categoryInfo) {
            if ($categoryInfo['level'] == 1) {
                $html .= '<ul class="categories">' .
                         '<li class="category">' .
                         $categoryInfo['name'] .
                         $this->generateChildCategoriesHTML($categoriesInfo, $categoryInfo['children'], 2) .
                         '</li>' .
                         '</ul>';
            }
        }

        return $html;
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
