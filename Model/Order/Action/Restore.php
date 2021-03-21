<?php
/**
 * Copyright (c) 2021 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Ris\Model\Order\Action;

use Kount\Ris\Model\Order\ActionInterface;

class Restore implements ActionInterface
{
    /**
     * @var \Kount\Ris\Model\Logger
     */
    protected $logger;

    /**
     * @param \Kount\Ris\Model\Logger $logger
     */
    public function __construct(
        \Kount\Ris\Model\Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function process($order)
    {
        $this->logger->info('Restore order status/state by ENS Kount request.');

        $order->setState($order->getHoldBeforeState());
        $order->addStatusToHistory($order->getHoldBeforeStatus(), __('Order status updated from Kount.'), false);

        $order->setHoldBeforeState(null);
        $order->setHoldBeforeStatus(null);
    }
}
