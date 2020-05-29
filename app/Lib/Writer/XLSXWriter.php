<?php

namespace App\Lib\Writer;

use App\Lib\Handlers\SpoutHandler;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class XLSXWriter implements WriterContract
{
    /**
     * @var \Box\Spout\Writer\XLSX\Writer
     */
    protected $writer;

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
     * @throws \Exception If the writer cannot be opened or if the given path is not writable
     *
     * @return WriterInterface
     */
    public function openToFile( string $fileName ): WriterContract
    {
        try {
            $this->filePath = config( 'app.temp_path' ) . $fileName;

            $this->writer = WriterEntityFactory::createXLSXWriter()
                ->setDefaultRowStyle( SpoutHandler::getDefaultStyle() )
                ->openToFile( $this->filePath );

            $this->isWriterOpened = true;

            return $this;
        }
        catch ( \Exception $e ) {
            throw $e; # TODO
        }
    }

    /**
     * Appends a row to the end of the stream.
     *
     * @param mixed $row The row to be appended to the stream
     * @param bool $headerStyle Indicates if header style needs to be applied.
     * @throws \Exception If the writer has not been opened yet or unable to write data
     *
     * @return WriterInterface
     */
    public function addRow( $row, bool $headerStyle = false ): WriterContract
    {
        if ( $this->isWriterOpened === true ) {
            try {
                $this->writer->addRow( WriterEntityFactory::createRowFromArray(
                    $row,
                    $headerStyle === false
                        ? SpoutHandler::getBodyStyle()
                        : SpoutHandler::getHeaderStyle()
                ) );
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
            $this->writer->close();

            $this->isWriterOpened = false;
        }

        return $this->filePath;
    }
}