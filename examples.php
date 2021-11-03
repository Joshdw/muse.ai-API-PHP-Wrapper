<?php
/**
 * MUSE.AI API PHP Examples
 *
 * @author Joshua Weller, joshdw.com
 * @copyright Copyright (c) 2021 Joshua Weller for ExHora.com
 * @license MIT License
 * 
 * Docs: https://muse.ai/api
 * 
 * Notes:
 * I recommend delaying requests if doing multiple calls to not surpass any
 * API limits. Use something like usleep(500) between API requests.
 * Also note that some video requests need the FID parameter, others need
 * the SVID parameter.
 * 
 * */


 // First you must include museai.php in your app
require_once('museai.php');

// Inititate an instance of the wrapper using an API KEY as the single parameter
$_MUSEAI = new museai('API-KEY-HERE');


// ---


// Get a list of collections (returns array)
$result = $_MUSEAI->listCollections();
/*
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