<?php

namespace App\Repositories\Tokens;

use App\Models\Tokens\DataToken;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Hash;

/**
 * Class DataTokenRepository
 * @package App\Repositories\Tokens
 * @version February 5, 2020, 16:30 UTC
*/
class DataTokenRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'token',
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
     * @return DataToken
     */
    public function model()
    {
        return DataToken::class;
    }

    /**
     * Create model record
     *
     * @param array $data
     *
     * @return Model
     */
    public function create( $data )
    {
        $input = [
            'data' => $data,
            'token' => Hash::make( json_encode( $data ) ),
        ];

        return parent::create( $input );
    }

    /**
     * Find model record for given primaryKey
     *
     * @param mixed $primaryKey
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function findAndDelete( $primaryKey, $columns = ['*'] )
    {
        $model = parent::find( $primaryKey, $columns );

        $model->forceDelete();

        return $model;
    }
}
