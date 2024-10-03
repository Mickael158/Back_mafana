<?php
namespace App\Controller;

use App\Entity\Evenement;
use App\Repository\AssociationRepository;
use App\Repository\EvenementRepository;
use App\Repository\TypeEvenementRepository;
use App\Repository\UsersRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EvenementController extends AbstractController
{
    #[Route('api/evenement',name:'insertion_evenement',methods:'POST')]
    public function insertion(EntityManagerInterface $em,Request $request,UsersRepository $usersRepository,AssociationRepository $associationRepository,TypeEvenementRepository $typeEvenementRepository,JWTEncoderInterface $jWTEncoderInterface): JsonResponse
    {
        $evenement = new Evenement();
        $data = $request->getContent();
        $data_decode = json_decode($data,true);
        $decode = $jWTEncoderInterface->decode($data_decode['user_id']);
        $utilisateur = $usersRepository->findOneBy(['username'=>$decode['username']]);
        $association = $associationRepository->find($data_decode['association_id']);
        $typeEvenement = $typeEvenementRepository->find($data_decode['typeEvenement_id']);
        $evenement
            ->setIdUtilisateur($utilisateur)
            ->setIdTypeEvenement($typeEvenement)
            ->setIdAssociation($association)
            ->setDescriptionEvenement($data_decode['description'])
            ->setDateEvenement(new DateTime($data_decode['date_debut']))
            ->setDatePublication(new DateTime())
            ->setDateFinEvenement(new DateTime($data_decode['date_fin']))
            ->setLieuEvenement($data_decode['lieu']);
        $em->persist($evenement);
        $em->flush();
        return $this->json(['message' => 'success de linsertion'],200,[]);
    }

    #[Route('/api/Evenement/supprimer/{id}',name:'suppresseion_Evenement',methods:'DELETE')]
        public function supprimer(Evenement $Evenement,Request $request, EntityManagerInterface $em){
            $em->remove($Evenement);
            $em->flush();
            return $this->json(['message' => 'Evenement Supprimer'], 200, []);
        }

        #[Route('/api/Evenement',name:'selectAll_Evenement',methods:'GET')]
        public function selectAll(EvenementRepository $EvenementRepository){
            return $this->json($EvenementRepository->findAll(), 200, []);
        }
        #[Route('/api/Evenement/proche_evenement',name:'select3_proche_evenement',methods:'GET')]
        public function select3_proche_evenement(EvenementRepository $EvenementRepository){
            return $this->json($EvenementRepository->get3_proche_evenement(), 200, []);
        }
}