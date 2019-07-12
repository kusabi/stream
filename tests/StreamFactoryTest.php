<?php

namespace Tests;

use Kusabi\Stream\Stream;
use Kusabi\Stream\StreamFactory;
use PHPUnit\Framework\TestCase;

class StreamFactoryTest extends TestCase
{
    public function testCreateStreamFromResource()
    {
        $factory = new StreamFactory();
        $resource = fopen('php://temp', 'w+');
        $stream = $factory->createStreamFromResource($resource);
        $this->assertInstanceOf(Stream::class, $stream);
    }

    public function testCreateStreamCreatesTemporaryResource()
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('test');
        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertSame('test', $stream->getContents());
    }

    public function testCreateStreamFromFile()
    {
        $factory = new StreamFactory();
        $stream = $factory->createStreamFromFile(__DIR__.'/resources/readable_file.txt');
        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertSame('This file should be readable', $stream->getContents());
    }
}
