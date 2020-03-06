<?php

namespace App\Lib\Writer;

interface WriterContract
{
    /**
     * Initializes the writer and opens it to accept data.
     * By using this method, the data will be written to a file.
     *
     * @param  string $outputFilePath Path of the output file that will contain the data
     * @throws \Exception If the writer cannot be opened or if the given path is not writable
     *
     * @return WriterContract
     */
    public function openToFile( string $fileName ): WriterContract;

    /**
     * Appends a row to the end of the stream.
     *
     * @param mixed $row The row to be appended to the stream
     * @throws \Exception If the writer has not been opened yet or unable to write data
     *
     * @return WriterContract
     */
    public function addRow( $row ): WriterContract;

    /**
     * Closes the writer. This will close the streamer as well, preventing new data
     * to be written to the file.
     *
     * @return string
     */
    public function close(): string;
}