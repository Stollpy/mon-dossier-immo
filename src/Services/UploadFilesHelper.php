<?php

namespace App\Services;

use App\Entity\Document;
use App\Entity\Individual;
use App\Entity\IndividualDataCategory;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFilesHelper{

    const UPLOAD_REFERENCE = 'Private_Document';

    private $slugger;
    private $manager;
    private $privateFilesystem;

    public function __construct(SluggerInterface $slugger, EntityManagerInterface $manager, FilesystemInterface $privateUploadsFilesystem)
    {
        $this->slugger = $slugger;
        $this->manager = $manager;
        $this->privateFilesystem = $privateUploadsFilesystem;      
    }

    public function uploadFilePublic($file, $individual, $label, $category)
    {
        $fileName = $this->uploadFileGeneric($file, self::UPLOAD_REFERENCE, false);

        $document = new Document();
        $document->setData($fileName);
        $document->setLabel($label);
        $document->setIndividual($individual);
        $document->setCategory($category);

        $this->manager->persist($document);
        $this->manager->flush();
    }

    public function uploadFilePrivate($file, string $label, Individual $individual, IndividualDataCategory $category)
    {
        $fileName = $this->uploadFileGeneric($file, self::UPLOAD_REFERENCE, false);

        $document = new Document();
        $document->setData($fileName);
        $document->setLabel($label);
        $document->setIndividual($individual);
        $document->setCategory($category);

        $this->manager->persist($document);
        $this->manager->flush();

    }

    private function uploadFileGeneric($file, string $directory, bool $isPublic = true )
    {
        if( $file instanceof UploadedFile){
            $originalFilename = $file->getClientOriginalName();
        }else{
            $originalFilename = $file->getFilename();
        }

        $newFilename = $this->slugger->slug($originalFilename).'-'.uniqid().'.'.$file->guessExtension();

        $stream = fopen($file->getPathname(), 'r');
        $filesystem = $isPublic ? $this->publicFileSystem : $this->privateFilesystem;

        $result = $filesystem->writeStream($directory.'/'.$newFilename, $stream);

        if($result === false){
            throw new \Exception(sprintf('Could not wrtie u^loaded file "%s"', $newFilename));
        }

        if(is_resource($stream)){
            fclose($stream);
        }

        return $newFilename;
    }
}