<?php

namespace App\Lib\Writer;

class JSONWriter
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
     * @param  string $outputFilePath Path of the output file that will contain the data
     *
     * @throws \Exception If the writer cannot be opened or if the given path is not writable
     *
     * @return WriterInterface
     */
    public function openToFile( string $fileName )
    {
        try {
            $this->filePath = config( 'app.file_path' ) . $fileName;

            $this->fh = fopen( $this->filePath, 'w' );

            $this->isWriterOpened = true;

            return $this;
        } catch ( \Exception $e ) {
            # TODO
        }
    }

    /**
     * Appends a row to the end of the stream.
     *
     * @param mixed $row The row to be appended to the stream
     *
     * @throws \Exception If the writer has not been opened yet or unable to write data
     *
     * @return WriterInterface
     */
    public function addRow( $row )
    {
        if ( $this->isWriterOpened === true ) {
            try {
                fwrite( $this->fh, $row );
            } catch ( \Exception $e ) {
                # TODO
            }

            return $this;
        }
        else {
            throw new Exception( 'The writer needs to be opened before adding row.' );
        }
    }

    /**
     * Closes the writer. This will close the streamer as well, preventing new data
     * to be written to the file.
     *
     * @return void
     */
    public function close() {
        fclose( $this->fh );

        return $this->filePath;
    }
}