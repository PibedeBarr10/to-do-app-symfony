<?php


namespace App\Service;


use Symfony\Component\Filesystem\Filesystem;
// use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileManagement
{
    private $targetDirectory;
    private Filesystem $filesystem;
    private SluggerInterface $slugger;

    public function __construct($targetDirectory, Filesystem $filesystem, SluggerInterface $slugger)
    {
        $this->targetDirectory = $targetDirectory;
        $this->filesystem = $filesystem;
        $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file): array
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // this is needed to safely include the file name as part of the URL
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'.'.$file->guessExtension();
        $unique_name = uniqid(time()).'.'.$file->guessExtension();

        $file->move(
            $this->targetDirectory.'/',
            $unique_name
        );

        return [$fileName, $unique_name];
    }

    public function download(string $filename)
    {
        $path = $this->targetDirectory.'/';
        if (!$this->filesystem->exists($path . '/'. $filename)) {
            return null;
        }
        $file = new Stream($path . '/'. $filename);
        $response = new BinaryFileResponse($file);

        return $response;
    }

    public function delete(string $filename)
    {
        $path = $this->targetDirectory.'/';
        $this->filesystem->remove($path.'/'.$filename);
    }

    public function rename(string $oldFilename, string $newFilename)
    {
        $path = $this->targetDirectory.'/';

        $extension = pathinfo($path.$oldFilename)['extension'];
        $this->filesystem->rename($path.$oldFilename, $path.$newFilename.'.'.$extension);
        return $newFilename.'.'.$extension;
    }
}