<?php

namespace App\Repositories\Dashboard;

use App\Models\Dashboard\Order;
use App\Repositories\BaseRepository;

/**
 * Class OrderRepository
 * @package App\Repositories\Dashboard
 * @version February 5, 2019, 4:16 am UTC
*/
class OrderRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'code',
        'user_id',
        'total_amount',
        'total_tax',
        'status',
        'search_id',
        'project',
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     *
     * @return Order
     */
    public function model()
    {
        return Order::class;
    }

    /**
     * Create order record with code.
     *
     * @param array $input
     *
     * @return \App\Models\Dashboard\Order
     */
    public function create( $input )
    {
        $order = parent::create( $input );

        $order->code = $order->id;
        $order->save();

        return $order;
    }
}
