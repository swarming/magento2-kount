<?php
/**
 * Copyright (c) 2021 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Ris\Observer;

use Magento\Framework\Event\Observer;

class QuoteSubmitFailure implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Kount\Ris\Helper\Workflow
     */
    protected $helperWorkflow;

    /**
     * @var \Kount\Ris\Model\Config\Workflow
     */
    protected $configWorkflow;

    /**
     * @var \Kount\Ris\Model\WorkflowFactory
     */
    protected $workflowFactory;

    /**
     * @var \Kount\Ris\Model\Observer\ConditionInterface
     */
    protected $condition;

    /**
     * @var \Kount\Ris\Model\Logger
     */
    protected $logger;

    /**
     * @param \Kount\Ris\Helper\Workflow $helperWorkflow
     * @param \Kount\Ris\Model\Config\Workflow $configWorkflow
     * @param \Kount\Ris\Model\WorkflowFactory $workflowFactory
     * @param \Kount\Ris\Model\Observer\ConditionInterface $condition
     * @param \Kount\Ris\Model\Logger $logger
     */
    public function __construct(
        \Kount\Ris\Helper\Workflow $helperWorkflow,
        \Kount\Ris\Model\Config\Workflow $configWorkflow,
        \Kount\Ris\Model\WorkflowFactory $workflowFactory,
        \Kount\Ris\Model\Observer\ConditionInterface $condition,
        \Kount\Ris\Model\Logger $logger
    ) {
        $this->helperWorkflow = $helperWorkflow;
        $this->configWorkflow = $configWorkflow;
        $this->workflowFactory = $workflowFactory;
        $this->condition = $condition;
        $this->logger = $logger;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->logger->info('sales_model_service_quote_submit_failure Start');

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getData('order');
        $payment = $order->getPayment();

        if (!$this->helperWorkflow->isProcessable($order)) {
            return;
        }

        if (!$this->condition->is($payment, $order->getStoreId())) {
            $this->logger->info("Skip for {$payment->getMethod()} payment method.");
            return;
        }

        $workflow = $this->workflowFactory->create($this->configWorkflow->getWorkflowMode($order->getStoreId()));
        $workflow->failure($order);

        $this->logger->info('sales_model_service_quote_submit_failure Done');
    }
}
