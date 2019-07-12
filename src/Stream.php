<?php

namespace Kusabi\Stream;

use Exception;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Stream implements StreamInterface
{
    /**
     * The underlying resource we are wrapped around
     *
     * @var resource|null
     */
    protected $resource;

    /**
     * Stream constructor.
     *
     * @param resource $resource
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamInterface::__toString()
     */
    public function __toString()
    {
        try {
            $this->rewind();
            return $this->getContents();
        } catch (Exception $exception) {
            return '';
        }
    }

    /**
     * Get the resource this instance is wrapped around
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Is the url of the stream a local one or not?
     *
     * @return bool
     */
    public function isLocal() : bool
    {
        return stream_is_local($this->resource);
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamInterface::close()
     */
    public function close()
    {
        fclose($this->resource);
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamInterface::detach()
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamInterface::getSize()
     */
    public function getSize()
    {
        return $this->getStat('size');
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamInterface::tell()
     */
    public function tell()
    {
        $result = ftell($this->resource);
        if ($result !== false) {
            return $result;
        }
        // @codeCoverageIgnoreStart
        throw new RuntimeException('Could not determine the position on the stream');
        // @codeCoverageIgnoreEnd
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamInterface::eof()
     */
    public function eof()
    {
        return feof($this->resource);
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamInterface::isSeekable()
     */
    public function isSeekable()
    {
        return $this->getMetadata('seekable') === true;
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamInterface::seek()
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->isSeekable()) {
            throw new RuntimeException('Resource is not seekable');
        }
        $result = fseek($this->resource, $offset, $whence);
        if ($result === -1) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('An unknown error occurred while trying to seek the resource');
            // @codeCoverageIgnoreEnd
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamInterface::rewind()
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamInterface::isWritable()
     */
    public function isWritable()
    {
        return in_array($this->getMetadata('mode'), ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+', 'r+b', 'wb', 'w+b', 'ab', 'a+b', 'xb', 'x+b', 'cb', 'c+b', 'r+t', 'wt', 'w+t', 'at', 'a+t', 'xt', 'x+t', 'ct', 'c+t']);
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamInterface::write()
     */
    public function write($string)
    {
        if (!$this->isWritable()) {
            throw new RuntimeException('Resource is not writable');
        }
        $bytes = fwrite($this->resource, $string);
        if ($bytes === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('An unknown error occurred while trying to write to the resource');
            // @codeCoverageIgnoreEnd
        }
        return $bytes;
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamInterface::isReadable()
     */
    public function isReadable()
    {
        return in_array($this->getMetadata('mode'), ['r', 'r+', 'w+', 'a+', 'x+', 'c+', 'rb', 'r+b', 'w+b', 'a+b', 'x+b', 'c+b', 'rt', 'r+t', 'w+t', 'a+t', 'x+t', 'c+t']);
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamInterface::read()
     */
    public function read($length)
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Resource is not readable');
        }
        return fread($this->resource, $length) ?? '';
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamInterface::getContents()
     */
    public function getContents()
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Resource is not readable');
        }
        $result = stream_get_contents($this->resource);
        if ($result === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('An unknown error occurred while trying to read the resource');
            // @codeCoverageIgnoreEnd
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @see StreamInterface::getMetadata()
     */
    public function getMetadata($key = null)
    {
        $metadata = stream_get_meta_data($this->resource);
        if ($key === null) {
            return $metadata;
        }
        return $metadata[$key] ?? null;
    }

    /**
     * Get the wrapper type from the metadata
     *
     * @return string
     */
    public function getWrapperType() : string
    {
        return $this->getMetadata('wrapper_type');
    }

    /**
     * Get the stream type from the metadata
     *
     * @return string
     */
    public function getStreamType() : string
    {
        return $this->getMetadata('stream_type');
    }

    /**
     * Get the mode from the metadata
     *
     * @return string
     */
    public function getMode() : string
    {
        return $this->getMetadata('mode');
    }

    /**
     * Get the unread bytes from the metadata
     *
     * @return int
     */
    public function getUnreadBytes() : int
    {
        return $this->getMetadata('unread_bytes');
    }

    /**
     * Get the uri from the metadata
     *
     * @return string
     */
    public function getUri() : string
    {
        return $this->getMetadata('uri');
    }

    /**
     * Get stream stats as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHPs fstat() function.
     *
     * @link https://php.net/manual/en/function.fstat.php
     *
     * @param string $key Specific stat to retrieve.
     *
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getStat($key = null)
    {
        $stats = fstat($this->resource);
        if ($key === null) {
            return $stats;
        }
        return $stats[$key] ?? null;
    }
}
