<?php
/**
 * Copyright (c) 2021 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Ris\Model\Observer\Condition;

use Magento\Sales\Model\Order\Payment;
use Kount\Ris\Model\Observer\ConditionInterface;

class Negative implements ConditionInterface
{
    /**
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param int|null $storeId
     * @return bool
     */
    public function is(Payment $payment, $storeId = null)
    {
        return false;
    }
}
