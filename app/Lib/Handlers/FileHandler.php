<?php

namespace App\Lib\Handlers;

use App\Lib\Handlers\SpoutHandler;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use juliorafaelr\GoogleStorage\GoogleStorage;

class FileHandler
{
    /**
     * @var string
     */
    protected $path;

    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct() {
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR;
    }

    /**
     * Set the name of the file to create.
     *
     * @param string $name
     * @param string $fileType The type (extension) of the file.
     *
     * @return string
     */
    protected function exportFileName( string $name, string $fileType ): string
    {
        $name = $name . '.' . $fileType;

        return $this->path . $name;
    }

    /**
     * Create json/excel file and upload it to google storage.
     *
     * @param string|array $fileData The file data.
     * @param int $rowsQuantity The quantity of rows.
     * @param string $name The file name without extension.
     * @param string $fileType The extension of file.
     *
     * @return array
     * @throws \Exception
     */
    public function createAndUploadFile( $fileData, int $rowsQuantity, string $name, string $fileType ): array
    {
        try {
            $filePath = $this->exportFileName( $name, $fileType );

            switch ( $fileType ) {
                case 'json':

                    $fh = fopen( $filePath, 'w' ) or die( 'Se produjo un error al crear el archivo' );

                    fwrite( $fh, json_encode( $fileData ) ) or die( 'No se pudo escribir en el archivo' );

                    fclose( $fh );
                    break;

                case 'xlsx':
                case 'csv':

                    $writer = WriterEntityFactory::createWriter( $fileType )
                        ->setDefaultRowStyle( SpoutHandler::getDefaultStyle() )
                        ->openToFile( $filePath );

                    $writer->addRow( WriterEntityFactory::createRowFromArray( $fileData[ 'header' ], SpoutHandler::getHeaderStyle() ) );

                    foreach ( $fileData[ 'body' ] as $value ) {
                        $writer->addRow( WriterEntityFactory::createRowFromArray( $value, SpoutHandler::getHeaderStyle() ) );
                    }

                    $writer->close();

                    break;

                default:
                    throw new \Exception( 'File type not supported.' );

                    break;
            }
        }
        catch ( \Exception $e ) {
            \Log::info( $e->getMessage() );

            throw $e;
        }

        $googleStorage = new GoogleStorage( config( 'app.google_key_file_path' ) );

        // upload to google storage
        $uploadedObject = $googleStorage->uploadObject( config( 'app.pe_export_file_bucket' ), basename( $filePath ), $filePath );

        return [
            'name' => $uploadedObject[ 'name' ],
            'bucket' => $uploadedObject[ 'bucket' ],
            'type' => $fileType,
            'rowsQuantity' => $rowsQuantity,
        ];
    }

    /**
     * Get json/excel file from google storage.
     *
     * @param string $bucket
     * @param string $fileName The file name with extension.
     *
     * @return string
     */
    public function downloadFile( string $bucket, string $fileName ): string
    {
        // path to file
        $filePath = $this->path . $fileName;

        // if file not exists download
        if ( file_exists( $filePath ) === false ) {
            $googleStorage = new GoogleStorage( config( 'app.google_key_file_path' ) );
            $googleStorage->downloadObject( $bucket, $fileName, $filePath );
        }

        return $filePath;
    }
}
