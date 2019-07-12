<?php

namespace Tests;

use Kusabi\Stream\Stream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class StreamTest extends TestCase
{
    public function testCreateStreamAndGetResource()
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);
        $this->assertSame($resource, $stream->getResource());
    }

    public function testToStringReturnsWholeValue()
    {
        $text = 'this is some test data';
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $text);
        $stream = new Stream($resource);
        $this->assertSame($text, (string) $stream);
    }

    public function testToStringReturnsEmptyStringWhenNotReadable()
    {
        $resource = fopen('php://stdin', 'w');
        fwrite($resource, '.');
        $stream = new Stream($resource);
        $this->assertSame('', (string) $stream);
    }

    public function testCloseClosesTheResource()
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);
        $stream->close();
        $this->assertNotEquals('stream', get_resource_type($resource));
    }

    public function testDetachReturnsAndRemovesTheResource()
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);
        $this->assertSame($resource, $stream->detach());
        $this->assertNull($stream->getResource());
        $this->assertNull($stream->detach());
    }

    public function testGetSizeReturnsSize()
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, 'this is some test data');
        rewind($resource);
        $stream = new Stream($resource);
        $this->assertSame(22, $stream->getSize());
    }

    public function testTellGivesCorrectPosition()
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, 'this is some test data');
        rewind($resource);
        $stream = new Stream($resource);
        $this->assertSame(0, $stream->tell());
        fseek($resource, 5);
        $this->assertSame(5, $stream->tell());
    }

    public function testEofKnowsIfWeHaveReachedTheEndOfTheStream()
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, 'this is some test data');
        rewind($resource);
        $stream = new Stream($resource);
        while (!feof($resource)) {
            $this->assertFalse($stream->eof());
            fread($resource, 5);
        }
        $this->assertTrue($stream->eof());
    }

    public function testIsSeekableIsCorrect()
    {
        $stream = new Stream(fopen('php://temp', 'r+'));
        $this->assertTrue($stream->isSeekable());

        $stream = new Stream(fopen('php://stdout', 'r+'));
        $this->assertFalse($stream->isSeekable());
    }

    public function testSeekWorksAsExpected()
    {
        $text = 'this is some test data';
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $text);
        rewind($resource);
        $stream = new Stream($resource);

        $stream->seek(1);
        $this->assertSame(1, $stream->tell());

        $stream->seek(1, SEEK_CUR);
        $this->assertSame(2, $stream->tell());

        $stream->seek(1, SEEK_SET);
        $this->assertSame(1, $stream->tell());

        $stream->seek(0, SEEK_END);
        $this->assertSame(strlen($text), $stream->tell());
    }

    public function testSeekThrowsExceptionIfNotSeekable()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Resource is not seekable');
        $stream = new Stream(fopen('php://stdout', 'r+'));
        $stream->seek(1);
    }

    public function testRewindWorks()
    {
        $text = 'this is some test data';
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $text);
        rewind($resource);
        $stream = new Stream($resource);

        $stream->seek(10);
        $this->assertSame(10, $stream->tell());
        $stream->rewind();
        $this->assertSame(0, $stream->tell());
    }

    public function testRewindThrowsExceptionIfNotSeekable()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Resource is not seekable');
        $stream = new Stream(fopen('php://stdout', 'r+'));
        $stream->rewind();
    }

    public function providesReadableWritableData()
    {
        return [
            ['r', true, false],
            ['r+', true, true],
            ['w', false, true],
            ['w+', true, true],
            ['a', false, true],
            ['a+', true, true],
            ['x', false, true],
            ['x+', true, true],
            ['c', false, true],
            ['c+', true, true],
        ];
    }

    /**
     * @param string $mode
     * @param bool $isReadable
     * @param bool $isWritable
     *
     * @dataProvider providesReadableWritableData
     */
    public function testReadableAndWritable($mode, $isReadable, $isWritable)
    {
        $stream = new Stream(fopen('php://stdin', $mode));
        $this->assertSame($isReadable, $stream->isReadable());
        $this->assertSame($isWritable, $stream->isWritable());
    }

    public function testWriteWorksCorrectly()
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, 'hello ');
        $stream = new Stream($resource);
        $this->assertSame(6, ftell($resource));
        $stream->write('6 more');
        $this->assertSame(12, ftell($resource));
        rewind($resource);
        $this->assertSame('hello 6 more', stream_get_contents($resource));
    }

    public function testWriteThrowsExceptionIfNotWritable()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Resource is not writable');
        $stream = new Stream(fopen('php://stdin', 'r'));
        $stream->write('test');
    }

    public function testReadWorksCorrectly()
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, 'hello ');
        rewind($resource);
        $stream = new Stream($resource);
        $this->assertSame('hel', $stream->read(3));
        $this->assertSame('lo', $stream->read(2));
        $this->assertSame(' ', $stream->read(1));
    }

    public function testReadThrowsExceptionIfNotReadable()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Resource is not readable');
        $stream = new Stream(fopen('php://stdin', 'w'));
        $stream->read(1);
    }

    public function testGetContentsWorksCorrectly()
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, 'hello');
        rewind($resource);
        $stream = new Stream($resource);
        $this->assertSame('hello', $stream->getContents());
        fseek($resource, 2);
        $this->assertSame('llo', $stream->getContents());
    }

    public function testGetContentsThrowsExceptionWhenNotReadable()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Resource is not readable');
        $resource = fopen('php://stdout', 'w');
        $stream = new Stream($resource);
        $stream->getContents();
    }

    public function testGetMetaDataWithoutKeyReturnsAll()
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);
        $this->assertSame([
            'wrapper_type' => 'PHP',
            'stream_type' => 'TEMP',
            'mode' => 'w+b',
            'unread_bytes' => 0,
            'seekable' => true,
            'uri' => 'php://temp'
        ], $stream->getMetadata());
    }

    public function testGetMetaDataWithKeyReturnsValueOrNull()
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);
        $this->assertSame('PHP', $stream->getMetadata('wrapper_type'));
        $this->assertSame('TEMP', $stream->getMetadata('stream_type'));
        $this->assertSame('w+b', $stream->getMetadata('mode'));
        $this->assertSame(0, $stream->getMetadata('unread_bytes'));
        $this->assertSame(true, $stream->getMetadata('seekable'));
        $this->assertSame('php://temp', $stream->getMetadata('uri'));
        $this->assertNull($stream->getMetadata('not-real'));
    }

    public function testGetWrapperType()
    {
        $stream = new Stream(fopen('php://temp', 'r+'));
        $this->assertSame('PHP', $stream->getWrapperType());
    }

    public function testGetStreamType()
    {
        $stream = new Stream(fopen('php://temp', 'r+'));
        $this->assertSame('TEMP', $stream->getStreamType());
    }

    public function testGetMode()
    {
        $stream = new Stream(fopen('php://stdin', 'r+'));
        $this->assertSame('r+', $stream->getMode());
    }

    public function testGetUnreadBytes()
    {
        $stream = new Stream(fopen('php://temp', 'r+'));
        $this->assertSame(0, $stream->getUnreadBytes());
    }

    public function testGetUri()
    {
        $stream = new Stream(fopen('php://temp', 'r+'));
        $this->assertSame('php://temp', $stream->getUri());
    }

    public function testGetStatsWithoutKeyReturnsAll()
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);
        $this->assertSame([
            0 => 12,
            1 => 0,
            2 => 33206,
            3 => 1,
            4 => 0,
            5 => 0,
            6 => -1,
            7 => 0,
            8 => 0,
            9 => 0,
            10 => 0,
            11 => -1,
            12 => -1,
            'dev' => 12,
            'ino' => 0,
            'mode' => 33206,
            'nlink' => 1,
            'uid' => 0,
            'gid' => 0,
            'rdev' => -1,
            'size' => 0,
            'atime' => 0,
            'mtime' => 0,
            'ctime' => 0,
            'blksize' => -1,
            'blocks' => -1
        ], $stream->getStat());
    }

    public function testGetStatWithKeyReturnsValueOrNull()
    {
        $resource = fopen('php://temp', 'r+');
        $stream = new Stream($resource);
        $this->assertSame(12, $stream->getStat(0));
        $this->assertSame(0, $stream->getStat(1));
        $this->assertSame(33206, $stream->getStat(2));
        $this->assertSame(1, $stream->getStat(3));
        $this->assertSame(0, $stream->getStat(4));
        $this->assertSame(0, $stream->getStat(5));
        $this->assertSame(-1, $stream->getStat(6));
        $this->assertSame(0, $stream->getStat(7));
        $this->assertSame(0, $stream->getStat(8));
        $this->assertSame(0, $stream->getStat(9));
        $this->assertSame(0, $stream->getStat(10));
        $this->assertSame(-1, $stream->getStat(11));
        $this->assertSame(-1, $stream->getStat(12));
        $this->assertSame(12, $stream->getStat('dev'));
        $this->assertSame(0, $stream->getStat('ino'));
        $this->assertSame(33206, $stream->getStat('mode'));
        $this->assertSame(1, $stream->getStat('nlink'));
        $this->assertSame(0, $stream->getStat('uid'));
        $this->assertSame(0, $stream->getStat('gid'));
        $this->assertSame(-1, $stream->getStat('rdev'));
        $this->assertSame(0, $stream->getStat('size'));
        $this->assertSame(0, $stream->getStat('atime'));
        $this->assertSame(0, $stream->getStat('mtime'));
        $this->assertSame(0, $stream->getStat('ctime'));
        $this->assertSame(-1, $stream->getStat('blksize'));
        $this->assertSame(-1, $stream->getStat('blocks'));
        $this->assertNull($stream->getStat('not-real'));
    }
}
