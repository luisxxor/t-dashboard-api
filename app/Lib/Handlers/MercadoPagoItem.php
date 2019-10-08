<?php

namespace App\Lib\Handlers;

use MercadoPago\Item;

class MercadoPagoItem
{
    protected $itemInstance;

    /**
     * Set the itemInstance options.
     *
     * @see < COLOCAR LA PAGINA DONDE SALEN LAS OPCIONES DISPONIBLES >
     *
     */
    public function __construct( array $itemInstanceOptions )
    {
        $this->itemInstance = new Item();

        foreach ( $itemInstanceOptions as $name => $value ) {
            $this->itemInstance->{ $name } = $value;
        }
    }

    public function getItemInstance()
    {
        return $this->itemInstance;
    }
}
