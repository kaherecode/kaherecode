<?php

namespace App\Service;

/**
 *
 */
class CloudinaryService
{
    public function __construct()
    {
        \Cloudinary::config(
            [
                "cloud_name" => $_ENV["CLOUDINARY_CLOUD"],
                "api_key" => $_ENV["CLOUDINARY_KEY"],
                "api_secret" => $_ENV["CLOUDINARY_SECRET"],
                "secure" => true
            ]
        );
    }

    public function upload($file, $options = [])
    {
        return \Cloudinary\Uploader::upload($file, $options);
    }

    public function destroy($publicId, $options = [])
    {
        return \Cloudinary\Uploader::destroy($publicId, $options);
    }
}
