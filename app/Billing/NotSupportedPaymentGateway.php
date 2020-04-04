<?php

namespace App\Billing;

class NotSupportedPaymentGateway implements PaymentGatewayContract
{
    /**
     * Not supported.
     *
     * @param string $receiptCode
     * @param float $amount
     * @param array $options
     *
     * @return array
     * @throws \Exception
     */
    public function charge( string $receiptCode, float $amount, array $options = [] ): array
    {
        throw new \Exception( "Payment method not supported." );

        return [];
    }
}