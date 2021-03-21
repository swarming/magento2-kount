<?php
/**
 * Copyright (c) 2021 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Ris\Model\Config\Source;

class PaymentMethods implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Kount\Ris\Model\Config\Backend\Scope
     */
    protected $configScope;

    /**
     * @var \Kount\Ris\Helper\Payment
     */
    protected $paymentHelper;

    /**
     * @param \Kount\Ris\Model\Config\Backend\Scope $configScope
     * @param \Kount\Ris\Helper\Payment $paymentHelper
     */
    public function __construct(
        \Kount\Ris\Model\Config\Backend\Scope $configScope,
        \Kount\Ris\Helper\Payment $paymentHelper
    ) {
        $this->configScope = $configScope;
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $paymentMethods = $this->paymentHelper->getActiveMethods(
            $this->configScope->getScope(),
            $this->configScope->getScopeValue()
        );

        $options = [
            ['value' => '', 'label' => __('None')]
        ];

        /** @var \Magento\Payment\Model\Method\AbstractMethod $method */
        foreach ($paymentMethods as $method) {
            $options[] = [
                'value' => $method->getCode(),
                'label' => $method->getTitle() . " ({$method->getCode()})"
            ];
        }

        return $options;
    }
}
