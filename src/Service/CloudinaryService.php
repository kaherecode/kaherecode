<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 *
 */
class CloudinaryService implements UploaderInterface
{
    const UPLOAD_FOLDER = 'kaherecode/tutorials/';
    const IMAGE_FORMAT = 'webp';

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

    /**
     * Upload a file
     *
     * @param UploadedFile $file    The file to upload
     * @param array        $options An array of options you would like to use
     *                              in your function
     *
     * @return array      An associative array with fileURL (mandatory) and
     *                    thumbnailURL (optional) as keys
     */
    public function upload(UploadedFile $file, $options = []): array
    {
        if (! isset($options['folder'])) {
            $options['folder'] = self::UPLOAD_FOLDER;
        }
        if (! isset($options['format'])) {
            $options['format'] = self::IMAGE_FORMAT;
        }
        $data = \Cloudinary\Uploader::upload($file, $options);

        // it's a looong string and I don't actually know another way to do it
        $thumbnailURL = "https://res.cloudinary.com/";
        $thumbnailURL .= "{$_ENV['CLOUDINARY_CLOUD']}/";
        $thumbnailURL .= "{$data['resource_type']}/";
        $thumbnailURL .= "{$data['type']}/c_thumb,w_400,g_face/";
        $thumbnailURL .= "v{$data['version']}/";
        $thumbnailURL .= "{$data['public_id']}.{$data['format']}";

        return [
            'fileURL' => $data['secure_url'],
            'thumbnailURL' => $thumbnailURL
        ];
    }

    /**
     * Delete a file
     *
     * @param string $fileName The file name to delete
     * @param array  $options  An array of options you would like to use in
     *                         your function
     */
    public function delete(string $fileName, $options = [])
    {
        $publicId = array_slice(explode("/", $fileName), -3, 3);
        $publicId[2] = implode(
            array_slice(explode(".", $publicId[2]), 0, -1),
            "."
        );
        $publicId = implode("/", $publicId);

        \Cloudinary\Uploader::destroy($publicId, $options);
    }
}
