<?php

namespace App\Services;

use App\Entity\Document;
use App\Services\UploadFilesHelper;
use App\Repository\IndividualRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DocumentHelper {

    private $uploadHelper;
    private $session;
    private $security;
    private $manager;

    public function __construct(UploadFilesHelper $uploadHelper, SessionInterface $session, Security $security, EntityManagerInterface $manager){
        $this->uploadHelper = $uploadHelper;
        $this->session = $session;
        $this->security = $security;
        $this->manager = $manager;
    }

    public function DocumentAccess(Document $document, $token)
    {
        if($document->getindividual()->getUser() == $this->security->getUser() || !empty($this->session->get($document->getIndividual()->getId())) ){
            $directoryAccess = $this->session->get($document->getIndividual()->getId());
            if($document->getindividual()->getUser() == $this->security->getUser() || $directoryAccess[0] == $token){
                return true;
            }
        }
    }

    public function DocumentDisplay(Document $document)
    {
            $response = new StreamedResponse(function() use ($document){
                $outputStream = fopen('php://output', 'wb');
                $fileStream = $this->uploadHelper->readStream($document->getFilePath(), false);
                stream_copy_to_stream($fileStream, $outputStream);
            });
    
            $response->headers->set('Content-Type', $document->getMimeType());
    
            return $response;
    }

    public function DocumentDownload(Document $document)
    {
        $response = new StreamedResponse(function() use ($document){
            $outputStream = fopen('php://output', 'wb');
            $fileStream = $this->uploadHelper->readStream($document->getFilePath(), false);
            stream_copy_to_stream($fileStream, $outputStream);
        });

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $document->getData()
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    public function DocumentDelete(Document $document)
    {
            $this->manager->remove($document);
            $this->uploadHelper->deleteFile($document->getFilePath(), false);
            $this->manager->flush();
    }

}