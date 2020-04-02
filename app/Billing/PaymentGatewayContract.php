<?php

namespace App\Billing;

interface PaymentGatewayContract
{
    /**
     * Charge payment.
     *
     * @param string $receiptCode
     * @param float $amount
     * @param array $options
     *
     * @return array
     * @throws \Exception
     */
    public function charge( string $receiptCode, float $amount, array $options = [] ): array;
}