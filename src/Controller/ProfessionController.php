<?php
    namespace App\Controller;

use App\Entity\Profession;
use App\Repository\ProfessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

    class ProfessionController extends AbstractController{

        #[Route('/api/Profession',name:'insetion_Profession',methods:'POST')]
        public function inerer(Request $request, EntityManagerInterface $em){
            $Profession = new Profession();
            $data = $request->getContent();
            $data_decode = json_decode($data, true);
            $Profession->setNomProfession($data_decode['Nom_Profession']);
            $em->persist($Profession);
            $em->flush();
            return $this->json(['message' => 'Profession inserer'], 200, []);
        }

        #[Route('/api/Profession/{id}',name:'modification_Profession',methods:'POST')]
        public function modifier(Profession $Profession,Request $request, EntityManagerInterface $em){
            $data = $request->getContent();
            $data_decode = json_decode($data, true);
            $Profession->setNomProfession($data_decode['Nom_Profession']);
            $em->flush();
            return $this->json(['message' => 'Profession modifier'], 200, []);
        }

        #[Route('/api/Profession/supprimer/{id}',name:'suppresseion_Profession',methods:'POST')]
        public function supprimer(Profession $Profession,Request $request, EntityManagerInterface $em){
            $em->remove($Profession);
            $em->flush();
            return $this->json(['message' => 'Profession Supprimer'], 200, []);
        }

        #[Route('/api/Profession',name:'selectAll_Profession',methods:'GET')]
        public function selectAll(ProfessionRepository $ProfessionRepository){
            return $this->json($ProfessionRepository->findAll(), 200, []);
        }

        #[Route('/api/Profession/{id}',name:'selectId_Profession',methods:'GET')]
        public function selectById($id,ProfessionRepository $ProfessionRepository){
            return $this->json($ProfessionRepository->find($id), 200, []);
        }
    }