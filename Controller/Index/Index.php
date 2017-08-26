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

namespace WompMobile\AmpProductExample\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $request;
    protected $rawResultFactory;

    /**
     * Constructor.
     * 
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Controller\Result\RawFactory $rawResultFactory,
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\RawFactory $rawResultFactory
    )
    {
        $this->rawResultFactory = $rawResultFactory;
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * Execute view action
     * 
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $this->_view->getLayout();

        /** @var \Foo\Bar\Block\Popin\Content $block */
        $block = $layout->createBlock(\WompMobile\AmpProductExample\Block\AmpProductBlock::class);
        $block->getProductParam();
        $block->setTemplate('WompMobile_AmpProductExample::index/index.phtml');

        $result = $this->rawResultFactory->create();
        $result->setHeader('Content-Type', 'text/html');
        $result->setContents($block->toHtml());
        return $result;
    }
}
