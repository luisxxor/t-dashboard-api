<?php

namespace App\Lib\Writer\Common;

use App\Lib\Handlers\SpoutHandler;
use App\Lib\Writer\PlainTextWriter;
use App\Lib\Writer\XLSXWriter;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class FileWriterFactory
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
                return self::createPlainTextWriter();
            case 'xlsx':
                return self::createXLSXWriter();
            default:
                throw new \Exception( 'File type not supported: ' . $writerType );
        }
    }

    /**
     * Creates an instance of a PlainText writer.
     *
     * @return \App\Lib\Writer\PlainTextWriter
     */
    private static function createPlainTextWriter()
    {
        return new PlainTextWriter();
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
