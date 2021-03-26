<?php


namespace App\Service;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

    public function upload(int $id, UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // this is needed to safely include the file name as part of the URL
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'.'.$file->guessExtension();

        $file->move(
            $this->targetDirectory.$id.'/',
            $fileName
        );

        return $fileName;
    }

    public function delete(int $id)
    {
        $path = $this->targetDirectory.$id.'/';
        $this->filesystem->remove($path);
    }

    public function rename(int $id, string $oldFilename, string $newFilename)
    {
        $path = $this->targetDirectory.$id.'/';

        $extension = pathinfo($path.$oldFilename)['extension'];
        $this->filesystem->rename($path.$oldFilename, $path.$newFilename.'.'.$extension);
        return $newFilename.'.'.$extension;
    }
}