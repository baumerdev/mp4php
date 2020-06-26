# MP4PHP

This is a library for parsing and modifying MP4 files.

## Why?

Sadly FFMPEG does not support many of MP4 metadata settings, e.g. marking subtitles as forced
or correctly recognizing/setting Dolby Atmos® even if such metadata exist in E-AC3 audio streams. 

There are some tools/libraries out there that support modifying the structure and data of MP4 files,
but either they didn't support features I needed, or active support stopped years ago or
they came with too much requirements I wasn't willing to fulfill.

(If you are using macOS you should really take a look at [Subler](https://subler.org/))

I wanted a solution that I can use on my private Linux NAS with simple integration in other console
based scripts... and that's when I started this library. 

## Version

The library is currently not in a release state. I do use it for my purposes and it works, but don't
expect it to work in your situation.

## Examples

For now there is no real documentation but a few examples in the "example" folder in this
repository. I usually use the methods in optimize_and_optimize.php, fixing a few metadata
and saving the file as optimized versions.

Optimized means the same you can achieve with tools like qt-faststart. The root boxes are ordered
for better streaming. First the file type "ftyp" box, then all of the metadata which also contains
references/offsets to the video, audio, etc. tracks. The tracks itself within the media data "mdat"
boxes comes last. 

## Todos

### Frontend/API

There is no real defined API for modifying data in boxes. You have to work your way down the hierarchy
to find the boxes you need, this means a lot of iterations, issets, is_a, etc.

For searching there should be something like XPath.

For simpler edits there should be convenience methods like
$file->metadata->setName('X') or $file->audioTracks[1]->setLanguage('eng')

### Saving/Overwrite

Currently only saving and optimizing into new files is supported but not saving to same file.
This requires a little bit more logic because I don't simply want to replace the "moov" box with
null values ("free" box) and add a new "moov" box at the end, if the original "moov" box perfectly
fits the new content.

The advantage of this is to get smaller file sizes and it can avoid an additional optimization in a
second step.

### Audio/Video interleaving

The current optimization process re-orders the boxes, so the file type and "moov" metadata are the first
boxes of the file. So the player directly knows all the video/audio data offsets after loading only a few
kilobytes of the file from the beginning, without jumping within the file/making additional network
requests.

An even better optimization would be to interleave video and audio streams. Instead of putting the complete
video stream, followed by audio streams, followed by subtitle streams in the "mdat" box, you would put e.g.
X seconds of video, followed by X seconds of each audio tream, followed by X seconds of each subtitle stream
in the "mdat" box. After that the following X seconds of each streams follow. So the player needn't to jump
constantly within the file for each stream but only can load X seconds in one block.

### Testing

I just started adding some unit tests, but this really needs to be more. Testing is important especially
in this project because we are dealing with binary files and literally a single wrong bit can make the MP4
file invalid and unreadable for any player.
