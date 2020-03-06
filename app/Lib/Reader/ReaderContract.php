<?php

namespace App\Lib\Reader;

interface ReaderContract
{
    /**
     * Returns the read string from a file.
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getContent(): string;

    /**
     * Returns an array to iterate over lines,
     * paginate the lines and formatting it.
     *
     * @param callable $formatLine
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function getLineIterator( callable $formatLine, array $options = array() ): array;

    /**
     * Closes the reader. This will close the streamer as well, preventing new data
     * to be written to the file.
     *
     * @return string
     */
    public function close(): string;
}
