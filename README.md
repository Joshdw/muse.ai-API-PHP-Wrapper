# MUSE.AI API PHP Wrapper

I recently was on the search for a new video hosting platform, and came across [Muse.ai](https://muse.ai/join?ref=wm5Drh6). I am absolutely amazed by the platform and what it offers, and for the cost it's unbeatable.

I decided to share this simple wrapper I wrote for my PHP app, maybe it'll be useful to someone else :)

I haven't added functionality for cutting videos or uploading subtitles, but can do if there is interest in it.

API documentation https://muse.ai/api

## Notes

Responses are json_decoded arrays (associative), so an example for getVideo result would be  `$result['title']`

I recommend delaying requests if doing multiple calls to not surpass any API limits. Use something like `usleep(500);` between API requests.

Also note that some video requests need the FID parameter, others need the SVID parameter.

## Requirements

curl must be enabled (run phpinfo(); and check)

## Installation

No composer needed, simply add the museai.php file as an include or require in your app and initiate an instance.

```php
require_once('museai.php');
$_MUSEAI = new museai('API-KEY-HERE');
```

## Usage

```php
// Get a list of collections (returns array)
$result = $_MUSEAI->listCollections();
/*
Example response:
Array
(
    [0] => Array
        (
            [name] => string
            [path] => string
            [scid] => string
            [tcreated] => int
            [videos] => Array
                (
                    [0] => Array
                        (
                            [duration] => float
                            [fid] => string
                            [svid] => string
                            [title] => string
                            [url] => string
                        )

                )

            [visibility] => string
        )

)
*/

// Get details of specific collection (returns array)
$result = $_MUSEAI->getCollection($scid);

// Create a new collection with specific name and visibility ("private", "unlisted", or "public")
$result = $_MUSEAI->createCollection('Name', 'public');

// Delete collection
$result = $_MUSEAI->deleteCollection($scid);

// Upload a new video, with optional collection and visibility parameters.
// Supported formats: AVI, MOV, MP4, OGG, WMV, WEBM, MKV, 3GP, M4V, MPEG
$file_location = "/uploads/test.mp4";
if (!file_exists($file_location)) die("Invalid File");
$tempfile =  curl_file_create($file_location);
$result = $_MUSEAI->uploadVideo(
    $tempfile,
    NULL, // Leave empty, or add SCID of the collection to add the video to 
    'unlisted' //"private", "unlisted", or "public"
);

// Updates a video with new content.
// Replace any optional parameter with NULL to skip it
$result = $_MUSEAI->updateVideo(
    $fid, // The video FID
    'unlisted', // Video visibility
    'Title', // Video title
    'Description', // Video description
    ['example.com'] // Domains to restrict the video to
);

// Delete Video
$result = $_MUSEAI->deleteVideo($fid);

// Get array of all videos
$result = $_MUSEAI->getVideos();
/*
Example response:
Array
(
    [0] => Array
        (
            [creator] => integer
            [description] => string
            [duration] => float
            [embed_domains] => Array
                (
                    [0] => string
                )

            [fid] => string
            [filename] => string
            [height] => integer
            [ingest_video] => integer
            [ingesting] => integer
            [mature] => integer
            [own] => integer
            [size] => integer
            [svid] => string
            [tcreated] => integer
            [title] => string
            [twatched] => integer
            [url] => string
            [views] => integer
            [visibility] => string
            [width] => string
        )
)
*/

// Get details of specific video
$result = $_MUSEAI->getVideo($svid);

// Check if video is being ingested (processing)
$result = $_MUSEAI->isVideoIngesting($svid);
if ($result === true) echo "Ingesting";

// Change the Video cover/thumbnail.
// To a file
// Supported formats: PNG, JPEG, JPG
$file_location = "/uploads/test.png";
if (!file_exists($file_location)) die("Invalid File");
$tempfile =  curl_file_create($file_location);
$result = $_MUSEAI->changeVideoCover(
    $fid,
    NULL, // Timestamp
    $tempfile // File
);
// Or to a specfic timestamp (seconds)
$result = $_MUSEAI->changeVideoCover(
    $fid,
    52, // Timestamp: 52 seconds
    NULL
);

// Get thumbnail of video. Optional time parameter
// Note: video must not be private, or else image will return a 404.
$result = $_MUSEAI->getVideoThumbnail($fid);
$result = $_MUSEAI->getVideoThumbnail($fid, 52); // Timestamp: 52 seconds
/*
Example result (string): 
https://cdn.muse.ai/w/fid/thumbnails/thumbnail.jpg
https://cdn.muse.ai/w/fid/thumbnails/00052.jpg
*/

// Get scenes array of video
$result = $_MUSEAI->getVideoScenes($svid);

// Get speech array of video
$result = $_MUSEAI->getVideoSpeech($svid);

// Get text array of video
$result = $_MUSEAI->getVideoText($svid);

// Get actions array of video
$result = $_MUSEAI->getVideoActions($svid);

// Get sounds array of video
$result = $_MUSEAI->getVideoSounds($svid);

// Get faces array of video
$result = $_MUSEAI->getVideoFaces($svid);
```

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License
[MIT](https://choosealicense.com/licenses/mit/)

## Author
Joshua Weller

joshdw.com | exhora.com