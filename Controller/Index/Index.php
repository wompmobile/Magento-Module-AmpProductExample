<?php
/**
 * Product controller.
 *
 * Copyright c 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace AlanKent\AmpExample\Controller\Index;

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
        $block = $layout->createBlock(\AlanKent\AmpExample\Block\AmpProductBlock::class);
        $block->getProductParam();
        $block->setTemplate('AlanKent_AmpExample::index/index.phtml');

        $result = $this->rawResultFactory->create();
        $result->setHeader('Content-Type', 'text/html');
        $result->setContents($block->toHtml());
        return $result;
    }
}
