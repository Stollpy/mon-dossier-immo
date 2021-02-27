<?php

namespace App\Services;

use App\Entity\Income;
use App\Entity\Document;
use App\Entity\Profiles;
use App\Entity\IncomeYear;
use App\Entity\Individual;
use App\Entity\IndividualDataCategory;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UploadFilesHelper{

    const UPLOAD_REFERENCE = 'Private_Document';

    private $slugger;
    private $manager;
    private $privateFilesystem;
    private $validator;

    public function __construct(SluggerInterface $slugger, EntityManagerInterface $manager, FilesystemInterface $privateUploadsFilesystem, ValidatorInterface $validator)
    {
        $this->slugger = $slugger;
        $this->manager = $manager;
        $this->privateFilesystem = $privateUploadsFilesystem; 
        $this->validator = $validator;
    }

    /**
     * uploadFilePublic function
     *
     * @param UploadedFile $file
     * @param Individual $individual
     * @param string $label
     * @param IndividualDataCategory $category
     */
    public function uploadFilePublic(UploadedFile $file, Individual $individual, string $label, IndividualDataCategory $category)
    {
        $fileName = $this->uploadFileGeneric($file, self::UPLOAD_REFERENCE);

        $document = new Document();
        $document->setData($fileName);
        $document->setLabel($label);
        $document->setIndividual($individual);
        $document->setCategory($category);

        $this->manager->persist($document);
        $this->manager->flush();
    }

    /**
     * uploadFilePrivate function
     *
     * @param UploadedFile $file
     * @param string $label
     * @param Individual $individual
     * @param IndividualDataCategory $category
     * @param Profiles $profile
     * @param Income $income
     * @param IncomeYear $years
     */
    public function uploadFilePrivate(UploadedFile $file, string $label, Individual $individual, IndividualDataCategory $category, Profiles $profile = null, Income $income = null, IncomeYear $years = null)
    {
        $fileName = $this->uploadFileGeneric($file, self::UPLOAD_REFERENCE, false);

        $document = new Document();
        $document->setData($fileName);
        $document->setMimeType($file->guessExtension());
        $document->setLabel($label);
        $document->setIndividual($individual);
        $document->setCategory($category);
        $document->setProfile($profile);
        $document->setincome($income);
        $document->setIncomeYear($years);

        $this->manager->persist($document);
        $this->manager->flush();

    }

    /**
     * uploadFileGeneric function
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param boolean $isPublic
     */
    private function uploadFileGeneric(UploadedFile $file, string $directory, bool $isPublic = true )
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

    /**
     * readStream function
     *
     * @param string $path
     * @param boolean $isPublic
     * @return resource
     */
    public function readStream(string $path, bool $isPublic)
    {
        $filesystem = $isPublic ? $this->publicFileSystem : $this->privateFilesystem;

        $resource = $filesystem->readStream($path);
        if($resource === false){
            throw new \Exception(sprintf('Error opening stream for "%s"', $path));
        }

        return $resource;
    }

    /**
     * deleteFile Function
     *
     * @param string $path
     * @param boolean $isPublic
     */
    public function deleteFile(string $path, bool $isPublic)
    {
        $filesystem = $isPublic ? $this->publicFileSystem : $this->privateFilesystem;
        $result = $filesystem->delete($path);
        if($result === false){
            throw new \Exception(sprintf('Error deleting "%s"', $path));
        }
    } 
    
    public function FileValidator($file)
    {
        $violations = $this->validator->validate($file, [new File([
            'maxSize' => '10000k', 
            'maxSizeMessage' => 'Le fichier est trop volumineux. Maximun autorisé : 1ko',
            'mimeTypes' => [
                'image/*',
                'application/pdf',
                'application/msword',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain'
            ],
            'mimeTypesMessage' => 'Type de fichier invalide']),
            New NotBlank(['message' => 'Merci de séléctionner un fichier.'])
        ]);
        
        return $violations;
    }
}