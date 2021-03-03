<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Document;
use App\Security\Access;
use App\Services\DocumentHelper;
use App\Repository\DocumentRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\IndividualDataCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DocumentController extends AbstractController
{
     /**
       * @Route("mes-documents/{id}", name="document.edit")
       * @param User $user
       * @param DocumentRepository $documentRepository
       * @param IndividualDataCategoryRepository $individualDataCategory
       * @param Access $access
       */
      public function EditDocument($id, Access $access, User $user, DocumentRepository $documentRepository, IndividualDataCategoryRepository $individualDataCategoryRepository)
      {
        if($access->accessDashboard($id) !== true){
            $this->addFlash('error', 'Access refuse !');
            return $this->redirectToRoute('home.index');
          }
            $individual = $user->getIndividual();
            
            $documents = [];

            $identity = $individualDataCategoryRepository->findOneBy(["code" => 'identity']);
            $domiciliation = $individualDataCategoryRepository->findOneBy(["code" => 'domiciliation']);

            $domiciliations = $documentRepository->findBy(["individual" => $individual, "category" => $domiciliation]);
            foreach ($domiciliations as $domiciliation){
                array_push($documents, $domiciliation);
            }
            $identitys = $documentRepository->findBy(["individual" => $individual, "category" => $identity]);
            foreach ($identitys as $identity){
                array_push($documents, $identity);
            }
            
            return $this->render('user/Dashboard/information/document/index.html.twig', [
                'documents' => $documents,
            ]);
      }

      /**
       * @Route("mes-documents/{id}/display/{token}", name="document.display")
       * @param string $token
       * @param Document $document
       * @param DocumentHelper $documentHelper
       */
      public function DisplayDocument($token, Document $document, DocumentHelper $documentHelper)
      {
            $access = $documentHelper->DocumentAccess($document, $token);
            if($access){
                return $documentHelper->DocumentDisplay($document);
            }else{
                $this->addFlash('error', 'vous n\'êtes pas autorisé à consulter se document !');
                return $this->redirectToRoute('home.index');
            }
     
      }

       /**
       * @Route("mes-documents/{id}/download/{token}", name="document.download")
       * @param string $token
       * @param Document $document
       * @param DocumentHelper $documentHelper
       */
      public function DownloadDocument($token, Document $document, DocumentHelper $documentHelper)
      {
            $access = $documentHelper->DocumentAccess($document, $token);
            if($access){
                return $documentHelper->DocumentDownload($document);
            }else{
                $this->addFlash('error', 'vous n\'êtes pas autorisé à consulter se document !');
                return $this->redirectToRoute('home.index');
            }

 
      }


      /**
       * @Route("mes-documents/{id}/delete", name="document.delete")
       * @param Document $document
       * @param DocumentHelper $documentHelper
       */
      public function DeleteDocument(Document $document, DocumentHelper $documentHelper)
      {
            $access = $documentHelper->DocumentAccess($document, 'access');
            if($access) {
                $documentHelper->DocumentDelete($document);
                $this->addFlash('success', 'Votre document à bien été supprimé.');
                return $this->redirectToRoute('document.edit', ['id' => $this->getUser()->getId()]);
            }
                
      }
}
