<?php

namespace App\Lib\Writer;

class PlainTextWriter implements WriterContract
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
     * @var bool Indicates whether the writer has been opened or not
     */
    protected $isWriterOpened = false;

    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct() { }

    /**
     * Initializes the writer and opens it to accept data.
     * By using this method, the data will be written to a file.
     *
     * @param string $fileName Name of the output file that will contain the data.
     * @throws \Exception If the writer cannot be opened or if the given path is not writable.
     *
     * @return WriterContract
     */
    public function openToFile( string $fileName ): WriterContract
    {
        try {
            $this->filePath = config( 'app.temp_path' ) . $fileName;

            $this->fh = fopen( $this->filePath, 'w' );

            if ( empty( $this->fh ) === true ) {
                throw new \Exception( 'Error opening the file: ' . $this->filePath );
            }
        }
        catch ( \Exception $e ) {
            throw $e; # TODO
        }

        $this->isWriterOpened = true;

        return $this;
    }

    /**
     * Appends a row to the end of the stream.
     *
     * @param mixed $row The row to be appended to the stream
     * @throws \Exception If the writer has not been opened yet or unable to write data
     *
     * @return WriterContract
     */
    public function addRow( $row ): WriterContract
    {
        if ( $this->isWriterOpened === true ) {
            try {
                $written = fwrite( $this->fh, $row );

                if ( empty( $written ) === true ) {
                    throw new \Exception( 'Error writing the file: ' . $this->filePath );
                }
            }
            catch ( \Exception $e ) {
                throw $e; # TODO
            }

            return $this;
        }
        else {
            throw new \Exception( 'The writer needs to be opened before adding row.' );
        }
    }

    /**
     * Closes the writer. This will close the streamer as well, preventing new data
     * to be written to the file.
     *
     * @return string
     */
    public function close(): string
    {
        if ( $this->isWriterOpened === true ) {
            fclose( $this->fh );

            $this->isWriterOpened = false;
        }

        return $this->filePath;
    }
}