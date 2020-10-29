<?php

/**
 * Created by Swarming Technology, LLC.
 * Project: Kount
 */

namespace Swarming\KountControl\Exception;

class ConfigException extends \Magento\Framework\Exception\LocalizedException
{
    const PHRASE = 'Login control is disabled in Config';

    /**
     * @param \Magento\Framework\Phrase|null $phrase
     * @param \Exception|null $cause
     * @param int $code
     */
    public function __construct(
        \Magento\Framework\Phrase $phrase = null,
        \Exception $cause = null,
        $code = 0
    ) {
        if ($phrase === null) {
            $phrase = new \Magento\Framework\Phrase(self::PHRASE);
        }
        parent::__construct($phrase, $cause, $code);
    }
}
