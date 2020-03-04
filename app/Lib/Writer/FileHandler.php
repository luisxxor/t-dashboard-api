<?php

namespace App\Lib\Writer;

use App\Lib\Handlers\SpoutHandler;
use App\Lib\Writer\JSONWriter;
use App\Lib\Writer\NDJSONWriter;
use App\Lib\Writer\XLSXWriter;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class FileHandler
{
    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct() { }

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
    public function createFile( $fileData, string $name, string $fileType ): string
    {
        try {
            $filePath = config( 'app.file_path' ) . $name . '.' . $fileType;

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
                        $writer->addRow( WriterEntityFactory::createRowFromArray( $value, SpoutHandler::getBodyStyle() ) );
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

        return $filePath;
    }




    public static function createWriter( $writerType )
    {
        switch ( $writerType ) {
            case 'json': return self::createJSONWriter();
            case 'ndjson': return self::createNDJSONWriter();
            case 'xlsx': return self::createXLSXWriter();
            default:
                throw new Exception( 'Type not supported: ' . $writerType );
        }
    }

    private static function createJSONWriter()
    {
        return new JSONWriter();
    }

    private static function createNDJSONWriter()
    {
        return new NDJSONWriter();
    }

    private static function createXLSXWriter()
    {
        return new XLSXWriter();
    }
}
