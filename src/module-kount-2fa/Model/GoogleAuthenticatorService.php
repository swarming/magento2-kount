<?php

/**
 * Created by Swarming Technology, LLC.
 * Project: Kount
 */

namespace Swarming\Kount2FA\Model;

use Endroid\QrCode\QrCode;
use Swarming\Kount2FA\Lib\PHPGangsta\GoogleAuthenticator;

class GoogleAuthenticatorService extends GoogleAuthenticator
{
    /**
     * Get QR Code Image.
     *
     * @param string $name
     * @param string $secret
     * @param null $title
     * @param array $params
     * @return string
     */
    public function getQrCodeEndroid(string $name, string $secret, $title = null, $params = [])
    {
        $size = !empty($params['size']) && (int) $params['size'] > 0 ? (int) $params['size'] : 200;

        $text = sprintf('otpauth://totp/%s?secret=%s', $name, $secret);
        if (true === is_string($title)) {
            $text = sprintf('%s&issuer=%s', $text, $title);
        }
        $qrCode = new QrCode($text);
        $qrCode->setSize($size);
        $qrCode->setWriterByName('png');
        $qrCode->setMargin(0);
        $qrCode->setEncoding('UTF-8');
        $qrCode->setSize($size);
        $qrCode->setText($text);

        return $qrCode->writeString();
    }
}
