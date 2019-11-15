<?php

namespace App\Projects\PeruProperties\Lib;

use App\Lib\Handlers\SpoutHandler;
use App\Models\Dashboard\Purchase;
use App\Projects\PeruProperties\Repositories\PropertyRepository;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Support\Facades\Config;
use juliorafaelr\GoogleStorage\GoogleStorage;

class PropertyClass
{
    public function __construct()
    {
        $this->propertyRepository = new PropertyRepository;
    }

    /*
     * Crear archivo de datos exportados (hoja de calculos).
     */
    public function createExportFile( $header, $body, $format )
    {
        try {
            $fileName = 'TasingProperties.' . count( $body ) . '.' . time() . '.' . $format;
            $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;

            $writer = WriterEntityFactory::createWriter( $format )
                ->setDefaultRowStyle( SpoutHandler::getDefaultStyle() )
                ->openToFile( $filePath )
                ->addRow( WriterEntityFactory::createRowFromArray( $header, SpoutHandler::getHeaderStyle() ) )
                ->addRows( SpoutHandler::createRowsFromArray( $body, SpoutHandler::getBodyStyle() ) )
                ->close();

            return [
                'name' => $fileName,
                'path' => $filePath,
            ];
        } catch ( Exception $e ) {

        }
    }

    /**
     * Set the name of the file to create.
     *
     * @param string $rowQuantity   the count of rows.
     * @param string $fileType      the type (extension) of the file.
     * @param string $path          the path of the file.
     *
     * @return string
     */
    public static function setExportFileName( int $rowQuantity, string $fileType = 'json', string $path = null ): string
    {
        $name = 'TasingProperties.' . $rowQuantity . '.' . time() . '.' . $fileType;
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR;

        return $path . $name;
    }

    /**
     * Create json file and upload it to google storage.
     *
     * @param Purchase $purchase The purchase
     *
     * @return string
     */
    public function createPurchaseJson( Purchase $purchase )
    {
        // construct and execute query
        $fileData = $this->propertyRepository->getSelectedTempProperties( $purchase->search_id );

        //quantity of properties to process
        $rowQuantity = $fileData[ 'metadata' ][ 'rowQuantity' ];

        // json_encode
        $jsonFileData = json_encode( $fileData );

        // create json file
        try {
            $jsonFilePath = $this->setExportFileName( $rowQuantity, 'json' );

            $fh = fopen( $jsonFilePath, 'w' ) or die( 'Se produjo un error al crear el archivo' );

            fwrite( $fh, $jsonFileData ) or die( 'No se pudo escribir en el archivo' );

            fclose( $fh );
        }
        catch ( Exception $e ) {
            \Log::info( $e->getMessage() );
        }

        $extension = pathinfo( $jsonFilePath )[ 'extension' ];

        $googleStorage = new GoogleStorage( Config::get( 'app.google_key_file_path' ) );

        // upload to google storage
        $uploadedObject = $googleStorage->uploadObject( Config::get( 'app.pe_export_file_bucket' ), $purchase->code . '.' . $extension, $jsonFilePath );

        // file info
        $fileName = $uploadedObject[ 'name' ];
        $bucketName = $uploadedObject[ 'bucket' ];

        // update purchase info
        $purchaseFile = $purchase->purchaseFiles()->get()->first();
        $purchaseFile->file_path = $bucketName;
        $purchaseFile->file_name = $fileName;
        $purchaseFile->save();

        return true;
    }
}
