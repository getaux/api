<?php

declare(strict_types=1);

namespace App\Service;

use Ethereum\EcRecover;

class SignatureService
{
    /**
     * Signature could be tried here: https://app.mycrypto.com/sign-message
     */
    public function verifySignature(string $message, string $publicKey, string $signature): bool
    {
        $publicKey = strtolower($publicKey);

        $recoveredAddress = null;

        try {
            // verify signature
            $valid = EcRecover::personalVerifyEcRecover($message, $signature, $publicKey);
            if ($valid) {
                // decode address
                $recoveredAddress = EcRecover::personalEcRecover($message, $signature);
            }
        } catch (\Throwable) {
            // retry for ledger
            try {
                if (str_ends_with($signature, '00')) {
                    $signature = substr($signature, 0, -2) . '1B';
                } elseif (str_ends_with($signature, '01')) {
                    $signature = substr($signature, 0, -2) . '1C';
                }

                $valid = EcRecover::personalVerifyEcRecover($message, $signature, $publicKey);
                if ($valid) {
                    // decode address
                    $recoveredAddress = EcRecover::personalEcRecover($message, $signature);
                }
            } catch (\Throwable) {
                // another fucking weird error
            }
        }

        return $recoveredAddress === $publicKey;
    }
}