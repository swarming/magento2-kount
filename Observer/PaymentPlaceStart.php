<?php
/**
 * Copyright (c) 2021 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Ris\Observer;

use Magento\Framework\Event\Observer;

class PaymentPlaceStart implements \Magento\Framework\Event\ObserverInterface
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
     * @var \Kount\Ris\Model\Session
     */
    protected $kountSession;

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
     * @param \Kount\Ris\Model\Session $kountSession
     * @param \Kount\Ris\Model\Observer\ConditionInterface $condition
     * @param \Kount\Ris\Model\Logger $logger
     */
    public function __construct(
        \Kount\Ris\Helper\Workflow $helperWorkflow,
        \Kount\Ris\Model\Config\Workflow $configWorkflow,
        \Kount\Ris\Model\WorkflowFactory $workflowFactory,
        \Kount\Ris\Model\Session $kountSession,
        \Kount\Ris\Model\Observer\ConditionInterface $condition,
        \Kount\Ris\Model\Logger $logger
    ) {
        $this->helperWorkflow = $helperWorkflow;
        $this->configWorkflow = $configWorkflow;
        $this->workflowFactory = $workflowFactory;
        $this->kountSession = $kountSession;
        $this->condition = $condition;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->logger->info('sales_order_payment_place_start Start');

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $observer->getData('payment');
        $order = $payment->getOrder();

        if (!$this->helperWorkflow->isProcessable($order)) {
            return;
        }

        if (!$this->condition->is($payment, $order->getStoreId())) {
            $this->logger->info("Skip for {$payment->getMethod()} payment method.");
            return;
        }

        if ($this->helperWorkflow->isBackendArea($payment->getOrder())) {
            $this->kountSession->incrementKountSessionId();
        }

        $workflow = $this->workflowFactory->create($this->configWorkflow->getWorkflowMode($order->getStoreId()));
        $workflow->start($payment);

        $this->logger->info('sales_order_payment_place_start Done');
    }
}
