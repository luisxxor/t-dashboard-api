<?php

namespace App\Billing;

interface PaymentGatewayContract
{
    /**
     * Charge payment.
     *
     * @param string $orderCode
     * @param float $amount
     * @param int $itemsQuantity
     *
     * @return array
     * @throws \Exception
     */
    public function charge( string $orderCode, float $amount, int $itemsQuantity ): array;
}