<?php
    namespace App\Controller;

use App\Entity\PayementCotisation;
use App\Entity\PersonneMembre;
use App\Repository\PayementCotisationRepository;
use App\Repository\PersonneMembreRepository;
use App\Repository\PrixCotisationRepository;
use App\Repository\UsersRepository;
use App\Service\TresorerieService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

    class PayementCotisationController extends AbstractController{

        #[Route('/api/Payement',name:'Nouveau_Enfant',methods:'POST')]
        public function insertionPayement(Request $request,EntityManagerInterface $em,UsersRepository $usersRepository,PersonneMembreRepository $personneMembre,TresorerieService $tresorerieService,JWTEncoderInterface $jWTEncoderInterface){
            $em->beginTransaction();    
            try {
            $data = $request->getContent();
            $data_decode = json_decode($data, true);
            $decode = $jWTEncoderInterface->decode($data_decode['utilisateur']);
            $user = $usersRepository->findOneBy(['username'=>$decode['username']]);
            $Devis = $data_decode['data'];
            $montants = 0;
            for($i = 0 ; $i < count($Devis) ; $i++) {
                for($j=0 ;$j<count($Devis[$i]) ; $j++)
                {
                    if (!empty($Devis[$i][$j])) {
                        $d = $Devis[$i][$j];
                        $id = $d["personnMembre"]["id"];
                        $date = new \DateTime($d["datePayer"]);
                        $montant = $d["Montant"];
                        $personne = $personneMembre->find($id);
                        $Payement = new PayementCotisation();
                        $Payement 
                            ->setDateDePayement(new \DateTime())
                            ->setMontantCotisationTotalPayer($montant)
                            ->setDate_payer($date)
                            ->setIdUtilisateur($user)
                            ->setIdPersonneMembre($personne);
                        $montants = $montants + $Payement->getMontantCotisationTotalPayer();
                        $em->persist($Payement);
                        $em->flush();                        
                    }
                }
            }
            $tresorerieService->insert($montants);
            $em->commit();
                } catch (\Exception $th) {
                    $em->rollback();
                }
            
            return $this->json(['message' => 'Payement inserer'],200,[]);
        }
        
        #[Route('/api/PayementCotisation',name:'selectAll_PayementCotisation',methods:'GET')]
        public function selectAll(PayementCotisationRepository $PayementCotisationRepository){
            return $this->json($PayementCotisationRepository->findAll(), 200, []);
        }

        #[Route('/api/PayementCotisation/{id}',name:'selectId_PayementCotisation',methods:'GET')]
        public function selectById($id,PayementCotisationRepository $PayementCotisationRepository){
            return $this->json($PayementCotisationRepository->find($id), 200, []);
        }

        #[Route('/api/Devis/{idPersonne_Resposable}/{nbr_mois}',name:'Devis',methods:'GET')]
        public function Devis($idPersonne_Resposable, $nbr_mois,  PayementCotisationRepository $PayementCotisationRepository){
            return $this->json($PayementCotisationRepository->Cotisation_Total($idPersonne_Resposable, $nbr_mois),200,[]);
        }
    } 