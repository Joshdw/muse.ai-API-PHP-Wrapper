<?php
/**
 * MUSE.AI API PHP INTEGRATION
 *
 * @author Joshua Weller, joshdw.com
 * @copyright Copyright (c) 2021 Joshua Weller for ExHora.com
 * @license MIT License
 * 
 * Docs: https://muse.ai/api
 * 
 */
class museai {
    
    /**
     * Endpoint for muse.ai api requests
     * @property string
     */
    private $URL = 'https://muse.ai/api/';

    /**
     * Resource which will contain the API key used for requests
     * @property string
     */
    private $auth_key;
     

    public function __construct() {
        $num_args = func_num_args();
        if ($num_args == 1) {
            $parameters = func_get_args();
            $this->auth_key = $parameters[0];
        } else {
            return false;
        }
        
    }

    /**
     * Returns the first 64 characters of a FID.
     * Muse.ai uses 64chars for data urls, FID can be 64 chars + a random number sometimes, so let's truncate it.
     * For context this FID number is the sha256 of the uploaded file.
     * Used in generation of thumbnail images
     *
     * @param string $fid
     * @return string
     */
    private function fixFID($fid) {
        if (strlen($fid) > 64) $fid = substr($fid, 0, 64);
        return $fid;
    }

    
    /**
     * Returns array of collections.
     *
     * @return array
     */
    public function listCollections() {
        return $this->http_request('files/collections');
    }
    
    /**
     * Returns array of specific collection details.
     *
     * @param string $scid Collection ID
     * @return array
     */
    public function getCollection($scid) {
        return $this->http_request('files/collections/' . $scid);
    }

    /**
     * Create a new collection with specific name and visibility.
     *
     * @param string $colName Desired name for collection
     * @param string $colVis "private", "unlisted", or "public"
     * @return array
     */
    public function createCollection($colName, $colVis) {
        return $this->http_request(
            'files/collections',
            [
                'name' => $colName,
                'visibility' => $colVis
            ],
            'POST'
        );
    }

    /**
     * Delete specific collection.
     *
     * @param string $scid Collection ID
     * @return array
     */
    public function deleteCollection($scid) {
        return $this->http_request('files/collections/' . $scid, [], 'DELETE');
    }
    
    
    /**
     * Upload a new video, with optional collection and visibility parameters.
     * Supported formats: AVI, MOV, MP4, OGG, WMV, WEBM, MKV, 3GP, M4V, MPEG
     *
     * @param file $file The file to be uploaded, usually created using curl_file_create()
     * @param string $colID Optional collection scid
     * @param string $colVis "private", "unlisted", or "public" (default: private)
     * @return array
     */
    public function uploadVideo(&$file, $colID, $colVis) {
        $tmp = array();
        $tmp['file'] = &$file;
        if (!empty($colID)) $tmp['collection'] = $colID;
        if (!empty($colVis)) $tmp['visibility'] = $colVis;

        // dieJSON(json_encode($tmp));
        return $this->http_request(
            'files/upload',
            $tmp,
            'POST'
        );
    }


    /**
     * Updates a video with new content. All parameters are optional. Set something to NULL if you do not want to change it.
     *
     * @param string $fid File ID of the video to be edited
     * @param string $visibility "private", "unlisted", or "public" (default: private)
     * @param string $title Optional new title
     * @param string $description Optional new description
     * @param string|array $domains Optional limit to specific domains/referrers
     * @return array|boolean:false if no changes found
     */
    public function updateVideo($fid, $visibility, $title, $description, $domains) {
        $tmp = array();
        if (!empty($visibility)) $tmp['visibility'] = $visibility;
        if (!empty($title)) $tmp['title'] = $title;
        if (!empty($description)) $tmp['description'] = $description;
        if (!empty($domains)) {
            if (!is_array($domains)) $domains = array($domains);
            $tmp['domains'] = $domains;
        }
        if (empty($tmp)) return false;
        return $this->http_request(
            'files/set/' . $fid,
            $tmp,
            'POST'
        );
    }

    /**
     * Delete specific video.
     *
     * @param string $fid File ID of the video
     * @return array
     */
    public function deleteVideo($fid) {
        return $this->http_request('files/delete/' . $fid, [], 'DELETE');
    }
    
    /**
     * Get array of all videos.
     *
     * @return array
     */
    public function getVideos() {
        return $this->http_request('files/videos');
    }

    /**
     * Get specific video.
     *
     * @param string $svid Video ID (not file ID!)
     * @return array
     */
    public function getVideo($svid) {
        return $this->http_request('files/videos/' . $svid);
    }

    /**
     * Get ingesting status of video.
     *
     * @param string $svid Video ID (not file ID!)
     * @return boolean
     */
    public function isVideoIngesting($svid) {
        $tmp = $this->http_request('files/videos/' . $svid);
        if (!isset($tmp['ingesting'])) return false;
        return $tmp['ingesting'];
    }

    /**
     * Change the Video cover/thumbnail.
     * This function has two options, first for TIME and second for a FILE
     * If using TIME, submit an integer representing the SECONDS value for where to grab the screenshot from.
     * If using FILE, submit an image file to replace the thumbnail with.
     * Supported formats: PNG, JPEG, JPG
     * NOTE: Video must not be private, if video is set to private the image will return a 404.
     *
     * @param string $fid Video FID (not Video SVID!)
     * @param integer $time Seconds timestamp to take screenshot
     * @param file $file The file to be uploaded, usually created using curl_file_create()
     * @return array|boolean:false if no changes found
     */
    public function changeVideoCover($fid, $time = NULL, &$file = NULL) {
        $tmp = array();
        $extra = "";
        if (!empty($time)) {
            $extra = '?t=' . (int)$time;
        }
        if (!empty($file)) {
            $tmp['file'] = &$file;
        }
        if (empty($tmp)) return false;
        return $this->http_request(
            'files/set/' . $fid . '/cover' . $extra,
            $tmp,
            'POST'
        );
    }

    /**
     * Get scenes array of video.
     *
     * @param string $svid Video SVID (not file ID!)
     * @return array
     */
    public function getVideoScenes($svid) {
        return $this->http_request('files/i/scenes/' . $svid);
    }

    /**
     * Get speech array of video.
     *
     * @param string $svid Video SVID (not file ID!)
     * @return array
     */
    public function getVideoSpeech($svid) {
        return $this->http_request('files/i/speech/' . $svid);
    }

    /**
     * Get text array of video.
     *
     * @param string $svid Video SVID (not file ID!)
     * @return array
     */
    public function getVideoText($svid) {
        return $this->http_request('files/i/text/' . $svid);
    }

    /**
     * Get actions array of video.
     *
     * @param string $svid Video SVID (not file ID!)
     * @return array
     */
    public function getVideoActions($svid) {
        return $this->http_request('files/i/actions/' . $svid);
    }

    /**
     * Get sounds array of video.
     *
     * @param string $svid Video SVID (not file ID!)
     * @return array
     */
    public function getVideoSounds($svid) {
        return $this->http_request('files/i/sounds/' . $svid);
    }

    /**
     * Get faces array of video.
     *
     * @param string $svid Video SVID (not file ID!)
     * @return array
     */
    public function getVideoFaces($svid) {
        return $this->http_request('files/i/faces/' . $svid);
    }

    /**
     * Get thumbnail of video. Optional time parameter
     *
     * @param string $fid Video FID (not SVID!)
     * @param integer $time Timestamp in seconds
     * @return string
     */
    public function getVideoThumbnail($fid, $time = 0) {
        $fid = $this->fixFID($fid);
        if (!empty($time)) {
            $time = (int)$time;
            // Make sure the filename is 5 characters long
            while (strlen($time) < 5) {
                $time = "0" . $time;
            }
            return "https://cdn.muse.ai/w/{$fid}/thumbnails/{$time}.jpg";
        }
        return "https://cdn.muse.ai/w/{$fid}/thumbnails/thumbnail.jpg";
    }

    /**
    * Handle http request to museai api
    */
    private function http_request($endpoint = "", $data = array(), $method = "GET") {
        $url = $this->URL . $endpoint;
        
        $headers = ["Key: {$this->auth_key}"];

        // Important: For file uploads make sure we set content type correctly
        if (!empty($data['file'])) {
        //if ($endpoint === "files/upload") {
            $headers[] = 'Content-type: multipart/form-data';
            $json_data = $data;

        } else {
            $headers[] = 'Content-type: application/json';
            $json_data = json_encode($data);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, '500');
        curl_setopt($ch, CURLOPT_TIMEOUT, '300');
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        
        if ($method === 'POST')
            curl_setopt($ch, CURLOPT_POST, true);
        
        if ($method === 'PUT')
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        
        if ($method === 'DELETE')
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        if ($method === 'PATCH')
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    
        //get request otherwise pass post data
        if (!isset($method) || $method == 'GET') {
            $url .= '?'.http_build_query($data);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        
        $http_response = curl_exec($ch);
        $error = curl_error($ch);
        //$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!empty($error)) {
            $response = array();
            $response['error'] = $error;
        }
        
        $response = json_decode($http_response, true);

        if ($http_response === false || (!is_array($response) && !is_object($response))) {
            $response = array();
            $response['error'] = 'Unknown response: ' . json_encode($http_response);
            //$response = json_decode(json_encode($response));
        } 
        
        if (!empty($response['error'])) {
            foreach ($response as $r) {
                $response = array();
                $response['error'] = $r;
            }
        }
        
        return $response;
    }
}