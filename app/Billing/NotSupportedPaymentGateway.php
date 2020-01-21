<?php

namespace App\Billing;

class NotSupportedPaymentGateway implements PaymentGatewayContract
{
    /**
     * Not supported.
     *
     * @param string $orderCode
     * @param float $amount
     * @param int $itemsQuantity
     *
     * @return array
     * @throws \Exception
     */
    public function charge( string $orderCode, float $amount, int $itemsQuantity ): array
    {
        throw new \Exception( "Payment method not supported." );

        return [];
    }
}