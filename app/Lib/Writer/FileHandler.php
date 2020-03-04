<?php

namespace App\Lib\Writer;

use App\Lib\Handlers\SpoutHandler;
use App\Lib\Writer\JSONWriter;
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
     * Creates an instance of the appropriate writer,
     * given the type of the file to be written.
     *
     * @param string $writerType Type of the writer to instantiate
     * @throws \Exception
     *
     * @return \App\Lib\Writer\WriterContract
     */
    public static function createWriter( $writerType )
    {
        switch ( $writerType ) {
            case 'json':
                return self::createJSONWriter();
            case 'xlsx':
                return self::createXLSXWriter();
            default:
                throw new \Exception( 'File type not supported: ' . $writerType );
        }
    }

    /**
     * Creates an instance of a JSON writer.
     *
     * @return \App\Lib\Writer\JSONWriter
     */
    private static function createJSONWriter()
    {
        return new JSONWriter();
    }

    /**
     * Creates an instance of a XLSX writer.
     *
     * @return \App\Lib\Writer\XLSXWriter
     */
    private static function createXLSXWriter()
    {
        return new XLSXWriter();
    }
}
