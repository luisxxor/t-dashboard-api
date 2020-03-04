<?php

namespace App\Lib\Writer;

class JSONWriter implements WriterContract
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
            $this->filePath = config( 'app.file_path' ) . $fileName;

            $this->fh = fopen( $this->filePath, 'w' );

            $this->isWriterOpened = true;

            return $this;
        } catch ( \Exception $e ) {
            throw $e; # TODO
        }
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
                fwrite( $this->fh, $row );
            } catch ( \Exception $e ) {
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