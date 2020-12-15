<?php

/**
 * Created by Swarming Technology, LLC.
 * Project: Kount
 */

namespace Swarming\KountControl\Exception;

class PositiveApiResponse extends \Magento\Framework\Exception\LocalizedException
{
    const PHRASE = 'Positive API response';

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
