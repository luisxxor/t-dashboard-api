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
     * @throws \Exception
     *
     * @return array
     */
    public function getLineIterator( callable $formatLine, array $options = [] ): array;

    /**
     * Closes the reader. This will close the streamer as well, preventing new data
     * to be written to the file.
     *
     * @return string
     */
    public function close(): string;
}
