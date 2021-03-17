<?php
/**
 * Copyright (c) 2021 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Ris\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Kount\Ris\Model\Config\Source\DeclineAction;
use Magento\Sales\Model\Order;

class SubmitAllAfter implements \Magento\Framework\Event\ObserverInterface
{
    const REVIEW_STATUSES = [
        DeclineAction::ACTION_HOLD,
        DeclineAction::ACTION_CANCEL,
        DeclineAction::ACTION_REFUND,
        Order::STATE_CANCELED
    ];

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
        $this->logger->info('checkout_submit_all_after Start');

        $event = $observer->getEvent();
        $orders = $event->getOrders() ?: [$event->getOrder()];

        foreach ($orders as $order) {
            $payment = $order->getPayment();
            if (!$this->helperWorkflow->isProcessable($order)) {
                continue;
            }
            if (!$this->condition->is($payment, $order->getStoreId())) {
                $this->logger->info("Skip for {$payment->getMethod()} payment method.");
                continue;
            }

            $workflow = $this->workflowFactory->create($this->configWorkflow->getWorkflowMode($order->getStoreId()));
            $workflow->success($order);
            $status = $order->getStatus();
            if (in_array($status, self::REVIEW_STATUSES)) {
                throw new LocalizedException(__('Order declined. Please ensure your information is correct. If the problem persists, please contact us for assistance.'));
            }
        }

        $this->logger->info('checkout_submit_all_after Done');
    }
}
