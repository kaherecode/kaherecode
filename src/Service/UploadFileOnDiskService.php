<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 *
 */
class UploadFileOnDiskService implements FileUploaderInterface
{
    protected $targetDirectory;
    protected $publicDir;
    protected $targetPath;
    protected $baseUrl;

    /**
     * @var SluggerInterface
     */
    protected $slugger;

    public function __construct(
        $publicDir,
        $targetDirectory,
        SluggerInterface $slugger,
        RequestStack $requestStack
    ) {
        $this->publicDir = $publicDir;
        $this->targetDirectory = $targetDirectory;
        $this->targetPath = '/' .trim($this->publicDir, '/').
            '/' .trim($this->targetDirectory, '/');
        $this->slugger = $slugger;
        $this->baseUrl = $requestStack->getCurrentRequest()->getHost();
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
        $fileName = "";

        if (isset($options['fileName'])) {
            $fileName = $this->slugger->slug(strtolower($options['fileName']));
        } else {
            $fileName = $this->slugger->slug(
                strtolower(
                    pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
                )
            );
        }
        $fileName .= '-' .uniqid(). '.' .$file->guessExtension();

        $fileURL = $this->baseUrl.
            '/' .trim($this->targetDirectory, '/').
            '/' .$fileName;

        try {
            $file->move($this->targetPath, $fileName);
        } catch (FileException $e) {
        }

        return ['fileURL' => $fileURL];
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
        $fileName = explode("/", $fileName);
        $fileName = end($fileName);
        $filePath = $this->targetPath. "/" .$fileName;

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
