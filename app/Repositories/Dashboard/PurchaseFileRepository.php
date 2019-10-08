<?php

namespace App\Repositories\Dashboard;

use App\Models\Dashboard\PurchaseFile;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Config;
use juliorafaelr\GoogleStorage\GoogleStorage;

/**
 * Class PurchaseFileRepository
 * @package App\Repositories\Dashboard
 * @version February 6, 2019, 3:27 pm UTC
*/
class PurchaseFileRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'purchase_id',
        'file_path',
        'bucket_name',
        'file_name',
        'row_quantity',
        'filters',
        'amount',
        'tax'
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
     **/
    public function model()
    {
        return PurchaseFile::class;
    }

    /**
     * Get JSON data.
     **/
    public function getJson( string $bucket, string $jsonName ): array
    {
        $googleStorage = new GoogleStorage( Config::get( 'app.google_key_file_path' ) );

        // path to file
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $jsonName;

        // if file not exists download
        if ( file_exists( $filePath ) === false ) {
            $googleStorage->downloadObject( $bucket, $jsonName, sys_get_temp_dir() . DIRECTORY_SEPARATOR . $jsonName );
        }

        // obtener json
        $fp = fopen( $filePath, 'r' );
        $contents = fread( $fp, filesize( $filePath ) );
        $decodedContent = json_decode( $contents, true );

        return $decodedContent;
    }
}
