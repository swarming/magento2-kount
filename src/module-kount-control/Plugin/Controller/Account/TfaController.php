<?php

/**
 * Created by Swarming Technology, LLC.
 * Project: Kount
 */

namespace Swarming\KountControl\Plugin\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\Redirect;

class TfaController
{
    /**
     * @var \Swarming\Kount\Model\Logger
     */
    private $logger;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Swarming\KountControl\Helper\Config
     */
    private $kountControlConfig;

    /**
     * @var \Swarming\KountControl\Model\Customer2FA
     */
    private $customer2FA;

    /**
     * @var LoginPost
     */
    private $loginPost;

    /**
     * @param \Swarming\Kount\Model\Logger $logger
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Swarming\KountControl\Helper\Config $kountControlConfig
     * @param \Swarming\KountControl\Model\Customer2FA $customer2FA
     * @param LoginPost $loginPost
     */
    public function __construct(
        \Swarming\Kount\Model\Logger $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Swarming\KountControl\Helper\Config $kountControlConfig,
        \Swarming\KountControl\Model\Customer2FA $customer2FA,
        \Swarming\KountControl\Plugin\Controller\Account\LoginPost $loginPost
    ) {
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->kountControlConfig = $kountControlConfig;
        $this->customer2FA = $customer2FA;
        $this->loginPost = $loginPost;
    }

    /**
     * Checks if 2FA succeeded after /setup or /authenticate controller's call of Kount2FA module
     */
    public function afterExecute()
    {
        try {
            if ($this->customerSession->get2faSuccessful()) {
                $this->customer2FA->twoFactorAuthenticate(1);
            } elseif ($this->customerSession->get2faAttemptCount()
                >= $this->kountControlConfig->get2faFailedAttemptsAmount()) {
                $this->customer2FA->twoFactorAuthenticate(0);
            }
        } catch (
            \Swarming\KountControl\Exception\ConfigException
            | \Swarming\KountControl\Exception\PositiveApiResponse $e
        ) {
            $this->logger->info($e->getMessage());
        } catch (
            \Swarming\KountControl\Exception\ParamsException
            | \Swarming\KountControl\Exception\NegativeApiResponse $e
        ) {
            $this->loginPost->logoutCustomer();
            $this->logger->warning($e->getMessage());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->error(__('KountControl: ' . $e->getMessage()));
        }
    }
}
