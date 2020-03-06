<?php

namespace App\Lib\Reader;

use Exception;

class PlainTextReader implements ReaderContract
{
    /**
     * @var resource|bool
     */
    protected $fh;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var bool Indicates whether the reader has been opened or not
     */
    protected $isReaderOpened = false;

    /**
     * Create a new class instance.
     *
     * @param string $filePath Path of the file to be read
     *
     * @throws Exception
     */
    public function __construct( string $filePath )
    {
        $this->filePath = $filePath;

        try {
            $this->fh = fopen( $this->filePath, 'r' );

            if ( empty( $this->fh ) === true ) {
                throw new Exception( 'Error opening the file: ' . $this->filePath );
            }
        } catch ( Exception $e ) {
            throw $e; # TODO
        }

        $this->isReaderOpened = true;

        return $this;
    }

    /**
     * Returns the read string from a file in secure binary mode.
     *
     * @throws Exception
     *
     * @return string
     */
    public function getContent(): string
    {
        if ( $this->isReaderOpened === true ) {
            try {
                $read = fread( $this->fh, filesize( $this->filePath ) );

                if ( empty( $read ) === true ) {
                    throw new Exception( 'Error reading the file: ' . $this->filePath );
                }
            } catch ( Exception $e ) {
                throw $e; # TODO
            }

            return $read;
        }
        else {
            throw new Exception( 'The reader needs to be opened before get content.' );
        }
    }

    /**
     * Returns an array to iterate over lines,
     * paginate the lines and formatting it.
     *
     * @param callable $formatLine
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function getLineIterator( callable $formatLine, array $options = array() ): array
    {
        $opt[ 'limit' ] = 25;
        $opt[ 'offset' ] = 0;

        foreach ( $options as $optionName => $o ) {
            $opt[ $optionName ] = $o;
        }

        if ( $opt[ 'limit' ] > 100 ) {
           throw new Exception( 'max limit allowed of 100 exceeded' );
        }

        $lines = [];

        $NLine = 0;
        while ( ( $line = fgets( $this->fh ) ) !== false ) {
            $NLine++;

            $line = trim( $line );

            if ( empty( $opt[ 'offset' ] ) === false ) {
                if ( $NLine <= $opt[ 'offset' ] ) {
                    continue;
                }
            }

            $lines[] = $formatLine( $line );

            if ( count( $lines ) >= $opt[ 'limit' ] ) {
                break;
            }
        }

        return $lines;
    }

    /**
     * Closes the reader. This will close the streamer as well, preventing new data
     * to be read to the file.
     *
     * @return string
     */
    public function close(): string
    {
        if ( $this->isReaderOpened === true ) {
            fclose( $this->fh );

            $this->isReaderOpened = false;
        }

        return $this->filePath;
    }
}
