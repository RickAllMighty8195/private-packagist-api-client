<?php

/**
 * (c) Packagist Conductors GmbH <contact@packagist.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrivatePackagist\ApiClient;

class WebhookSignature
{
    /** @var string */
    private $secret;

    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    /**
     * @param string $signature
     * @param ?string $payload
     * @return bool
     */
    public function validate($signature, $payload)
    {
        $payloadSignature = 'sha1='.hash_hmac('sha1', $payload ?? '', $this->secret);

        return hash_equals($payloadSignature, (string) $signature);
    }
}
