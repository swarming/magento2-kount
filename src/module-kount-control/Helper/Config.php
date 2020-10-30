<?php

/**
 * Created by Swarming Technology, LLC.
 * Project: Kount
 */

namespace Swarming\KountControl\Helper;

class Config extends \Swarming\Kount\Model\Config\Account
{
    const XML_IS_LOGIN_SERVICE_ENABLED = 'swarming_kount_control/general/login_enabled';
    const XML_IS_TRUSTED_DEVICE_ENABLED = 'swarming_kount_control/general/trusted_device_enabled';
    const XML_IS_SIGNUP_ENABLED = 'swarming_kount_control/general/sign_up_enabled';
    const XML_API_KEY = 'swarming_kount_control/general/api_key';

    /**
     * @return bool
     */
    public function isLoginServiceEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_IS_LOGIN_SERVICE_ENABLED);
    }

    /**
     * @return bool
     */
    public function isTrustedDeviceEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_IS_TRUSTED_DEVICE_ENABLED);
    }

    /**
     * @return bool
     */
    public function isSignupEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_IS_SIGNUP_ENABLED);
    }

    /**
     * @return string
     */
    public function getControlApiKey()
    {
        return $this->scopeConfig->getValue(self::XML_API_KEY);
    }
}
