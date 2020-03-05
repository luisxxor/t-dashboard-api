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
     * @param int $limit
     * @param int $offset
     * @param callable $formatLine
     * @throws \Exception
     *
     * @return string
     */
    public function getLineIterator( int $limit, int $offset = 0, callable $formatLine ): array;

    /**
     * Closes the reader. This will close the streamer as well, preventing new data
     * to be written to the file.
     *
     * @return string
     */
    public function close(): string;
}