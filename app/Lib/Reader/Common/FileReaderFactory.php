<?php

namespace App\Lib\Reader\Common;

use App\Lib\Reader\PlainTextReader;

class FileReaderFactory
{
    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct() { }

    /**
     * Creates a reader by file extension
     *
     * @param string $filePath The path to the file. Supported extensions are .json, .ndjson
     * @throws \Exception
     *
     * @return \App\Lib\Reader\ReaderContract
     */
    public static function createReaderFromFile( $filePath )
    {
        $extension = strtolower( pathinfo( $filePath, PATHINFO_EXTENSION ) );

        switch ( $extension ) {
            case 'json':
            case 'ndjson':
                return self::createPlainTextReader( $filePath );
            default:
                throw new \Exception( 'File type not supported: ' . $extension );
        }
    }

    /**
     * Creates an instance of a PlainText writer.
     *
     * @param  string $filePath Path of the file to be read
     *
     * @return \App\Lib\Reader\PlainTextReader
     */
    private static function createPlainTextReader( string $filePath )
    {
        return new PlainTextReader( $filePath );
    }
}
