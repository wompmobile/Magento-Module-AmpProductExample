<?php
/**
 * Copyright 2017 WompMobile, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace WompMobile\AmpProductExample\Block;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Module\Dir;

class AmpProductBlock extends \Magento\Framework\View\Element\Template
{
    /** @var \Magento\Catalog\Api\Data\ProductInterface */
    private $product;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface */
    protected $productRepo;

    /** @var \Magento\Catalog\Helper\Product */
    protected $productHelper;

    /** @var \Magento\Catalog\Model\Category */
    protected $categoryModel;

    /** @var \Magento\Framework\Data\Form\FormKey */
    protected $formKey;

    /** @var \Magento\Framework\Escaper */
    protected $escaper;

    /** @var \Magento\Framework\Module\Dir\Reader */
    protected $moduleReader;

    /** @var \Magento\Framework\UrlInterface */
    protected $urlBuilder;

    /**
     * Constructor
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepo
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\Category $categoryModel
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Element\Template\Context $context
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepo,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\Category $categoryModel,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->productRepo = $productRepo;
        $this->productHelper = $productHelper;
        $this->categoryModel = $categoryModel;
        $this->formKey = $formKey;
        $this->escaper = $escaper;
        $this->moduleReader = $moduleReader;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    /**
     * Get product SKU from URL
     * Example: http://domain.com/amp/?sku=abc will retrieve the product with SKU 'abc'
     *
     * @return string
     */
    public function getProductParam()
    {
        $sku = $_GET["sku"];
        if ($sku === "") {
            throw new \Exception("Product SKU missing.");
        }

        $this->product = $this->productRepo->get($sku);
        if ($this->product === null) {
            throw new \Exception("Failed to fetch product with SKU '$sku'.");
        }
    }

    /**
     * Get product
     *
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Product id
     *
     * @return int|null
     */
    public function getProductId()
    {
        return $this->product->getId();
    }

    /**
     * Product price (formatted number)
     * Example: float 1111.55555 becomes 1111.56
     *
     * @return string
     */
    public function getProductPrice()
    {
        return number_format($this->product->getPrice(), 2, null, '');
    }

    /**
     * Canonical URL to product
     * Reference: https://www.ampproject.org/docs/guides/discovery
     *
     * @return string|bool
     */
    public function getProductCanonicalUrl()
    {
        return $this->productHelper->getProductUrl($this->product);
    }

    /**
     * Retrieve base image url
     *
     * @return string|bool
     */
    public function getProductImageUrl()
    {
        return $this->productHelper->getImageUrl($this->product);
    }

    /**
     * Get basic information about all categories in an associative array:
     *  int 'id', string 'name', string 'url', int 'level', array 'children'
     *
     * @return array
     */
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

    /**
     * Generate AMP HTML markup for all children categories of a parent category
     *
     * @param array $categoriesInfo
     * @param array $children
     * @param int $level
     * @return array
     */
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

    /**
     * Generate AMP HTML markup for all categories
     * TODO: This is not efficient
     *
     * @return string
     */
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

    /**
     * Escape html entities
     *
     * @param  string|array $data
     * @param  array $allowedTags
     * @return string|array
     */
    public function escapeHtml($html, $allowedTags = NULL)
    {
        return $this->escaper->escapeHtml($html, $allowedTags);
    }

    /**
     * Get basic information about product media gallery images in an associative array:
     *  string 'url', int 'width', int 'height'
     *
     * @return array
     */
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
            $values[] = $this->getViewFileUrl('Magento_Catalog::images/product/placeholder/image.jpg');

            $modulePath = $this->moduleReader->getModuleDir(Dir::MODULE_VIEW_DIR, "Magento_Catalog");
            $filePath = $modulePath . '/base/web/images/product/placeholder/image.jpg';
            $imageDimensions = getimagesize($filePath);
            $values[] = $imageDimensions[0];
            $values[] = $imageDimensions[1];

            $galleryInfo[] = array_combine($keys, $values);
        }

        return $galleryInfo;
    }

    /**
     * Determine whether a product media gallery entry is an image
     *
     * @param  \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface $galleryEntry
     * @return bool
     */
    public function isImage($galleryEntry)
    {
        $mediaDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);

        $relativePath = 'catalog/product' . $galleryEntry->getFile();
        $absolutePath = $mediaDir->getAbsolutePath($relativePath);

        return @is_array(getimagesize($absolutePath));
    }

    /**
     * Get URL to image in product media gallery
     *
     * @param  \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface $galleryEntry
     * @return string
     */
    public function getImageUrl($galleryEntry)
    {
        $mediaUrl = $this->urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);
        $mediaUrl .= 'catalog/product';

        return $mediaUrl . $galleryEntry->getFile();
    }

    /**
     * Get dimensions of image in product media gallery
     * Example: [width, height]
     *
     * @param  \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface $galleryEntry
     * @return array
     */
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

    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getFormKey() {
        return $this->formKey->getFormKey();
    }

}
