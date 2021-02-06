<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 *
 */
interface UploaderInterface
{
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
    public function upload(UploadedFile $file, $options = []): array;

    /**
     * Delete a file
     *
     * @param string $fileName The file name to delete
     * @param array  $options  An array of options you would like to use in
     *                         your function
     */
    public function delete(string $fileName, $options = []);
}
