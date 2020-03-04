<?php

namespace App\Lib\Handlers;

use juliorafaelr\GoogleStorage\GoogleStorage;

class GoogleStorageHandler
{
    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct() { }

    /**
     * Download file from google storage.
     *
     * @param string $bucket
     * @param string $fileName The file name with extension.
     *
     * @return string
     */
    public function downloadFile( string $bucket, string $fileName ): string
    {
        // path to file
        $filePath = config( 'app.temp_path' ) . $fileName;

        // if file not exists download
        if ( file_exists( $filePath ) === false ) {
            $googleStorage = new GoogleStorage( config( 'app.google_key_file_path' ) );
            $googleStorage->downloadObject( $bucket, $fileName, $filePath );
        }

        return $filePath;
    }

    /**
     * Upload file from google storage.
     *
     * @param string $bucket
     * @param string $filePath
     * @param int $rowsQuantity
     *
     * @return array
     * @throws \Exception
     */
    public function uploadFile( string $bucket, string $filePath, int $rowsQuantity ): array
    {
        $googleStorage = new GoogleStorage( config( 'app.google_key_file_path' ) );

        // upload to google storage
        $uploadedObject = $googleStorage->uploadObject( $bucket, basename( $filePath ), $filePath );

        return [
            'name' => $uploadedObject[ 'name' ],
            'bucket' => $uploadedObject[ 'bucket' ],
            'type' => pathinfo( $filePath, PATHINFO_EXTENSION ),
            'rowsQuantity' => $rowsQuantity,
        ];
    }
}
