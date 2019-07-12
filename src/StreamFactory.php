<?php

namespace Kusabi\Stream;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @see StreamFactoryInterface::createStream()
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $stream = static::createStreamFromResource(
            fopen('php://temp', 'w+')
        );
        $stream->write($content);
        $stream->rewind();
        return $stream;
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamFactoryInterface::createStreamFromFile()
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = fopen($filename, $mode);
        if ($resource === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('File could not be opened');
            // @codeCoverageIgnoreEnd
        }
        return static::createStreamFromResource($resource);
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamFactoryInterface::createStreamFromResource()
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}
