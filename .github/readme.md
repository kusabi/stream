[![Release Badge](https://img.shields.io/github/release/kusabi-psr/uri.svg)](https://img.shields.io/github/release/kusabi-psr/uri.svg)
[![Tag Badge](https://img.shields.io/github/tag/kusabi-psr/uri.svg)](https://img.shields.io/github/tag/kusabi-psr/uri.svg)
[![Coverage Badge](https://img.shields.io/codacy/coverage/a2236972c0084da8a41a880cb7e017b8.svg)](https://img.shields.io/codacy/grade/bec9194f88a843fd9abd4edef6102f9b.svg)
[![Grade Badge](https://img.shields.io/codacy/grade/a2236972c0084da8a41a880cb7e017b8.svg)](https://img.shields.io/codacy/grade/bec9194f88a843fd9abd4edef6102f9b.svg)
[![Issues Badge](https://img.shields.io/github/issues/kusabi-psr/uri.svg)](https://img.shields.io/github/issues/kusabi-psr/uri.svg)
[![Licence Badge](https://img.shields.io/github/license/kusabi-psr/uri.svg)](https://img.shields.io/github/license/kusabi-psr/uri.svg)
[![Code Size](https://img.shields.io/github/languages/code-size/kusabi-psr/uri.svg)](https://img.shields.io/github/languages/code-size/kusabi-psr/uri.svg)

An implementation of a [PSR-7](https://www.php-fig.org/psr/psr-7/) & [PSR-17](https://www.php-fig.org/psr/psr-17/) conforming Uri library

# Using the Stream class

The Stream class is a very basic wrapper around a stream resource.


```php
use Kusabi\Stream\Stream;

// Instantiate a Uri instance
$stream = new Stream(fopen('php://stdin', 'r'));

// Fetch the properties of the Stream
$stream->getContents(); // Get everything from the current pointer to the end of the stream
$stream->getSize(); // Get the size of the stream in bytes
$stream->isSeekable();
$stream->isReadable();
$stream->isWritable();
$stream->seek($offset, $whence = SEEK_SET); // Move the pointer around in the stream
$stream->tell(); // Where is the pointer in the stream
$stream->rewind(); // Set the pointer to the beginning of the stream
$stream->read($length); // Read the next $length character from the stream
$stream->getMetadata($key = null); // Get all the metadata, or a particular key
$stream->getStat($key = null); // Get all the fstat entries, or a particular key
(string) $stream; // Rewind and get all the contents from the stream

```


# Using the Stream Factory

The Stream factory can be used to create the Stream instance too.


```php
use Kusabi\Stream\StreamFactory;

// Instantiate a Uri instance
$factory = new StreamFactory();
$stream = $factory->createStream('temp resource with data in it');
$stream = $factory->createStreamFromFile('file.txt');
$stream = $factory->createStreamFromResource(fopen('php://stdin', 'r'));
```