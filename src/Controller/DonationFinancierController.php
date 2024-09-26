<?php
    namespace App\Controller;

use App\Entity\DonationFinancier;
use App\Repository\DemandeFinancierRepository;
use App\Repository\DonationFinancierRepository;
use App\Repository\PersonneMembreRepository;
use App\Repository\UsersRepository;
use App\Service\InvestigationFinancier;
use App\Service\TresorerieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

    class DonationFinancierController extends AbstractController{

        #[Route('/api/DonationFinancier',name:'insetion_DonationFinancier',methods:'POST')]
        public function inerer(Request $request, EntityManagerInterface $em , UsersRepository $usersRepository,TresorerieService $tresorerieService){
            $em->beginTransaction();    
            try {
            $DonationFinancier = new DonationFinancier();
            $data = $request->getContent();
            $data_decode = json_decode($data, true);
            $user = $usersRepository->find($data_decode['utilisateur']);
            $DonationFinancier->setNomDonationFinancier($data_decode['nom_donation_financier']);
            $DonationFinancier->setDateDonationFinancier(new \DateTime());
            $DonationFinancier->setMontant($data_decode['montant']);
            $DonationFinancier->setIdUtilisateur($user);
            $em->persist($DonationFinancier);
            $em->flush();

            $tresorerieService->insert($DonationFinancier->getMontant());
            $em->commit();
                } catch (\Exception $th) {
                    $em->rollback();
                }
            return $this->json(['message' => 'Donation Financier inserer'], 200, []);
        }
        #[Route('/api/DemandeFinaciers',name:'insetion_DemandeFinaciers',methods:'GET')]
        public function selectAlls_DemandeFinacier(DemandeFinancierRepository $demandeFinancierRepository , InvestigationFinancier $investigationFinancier , PersonneMembreRepository $personneMembreRepository){
            $demande_initial = $demandeFinancierRepository->getDemanceFinancier_With_Investi();
            $demande_final = [];
            for($i = 0 ; $i<count($demande_initial) ; $i++){
                $investigationFinancier = new InvestigationFinancier();
                $pourcentage = $demandeFinancierRepository->pourcentage($demande_initial[$i]['id_personne_membre_id']);
                $demande = $demandeFinancierRepository->find($demande_initial[$i]['id']);
                $personne = $personneMembreRepository->find($demande_initial[$i]['id_personne_membre_id']);
                $investigationFinancier->setPersonnMembre($personne);
                $investigationFinancier->setMotif($demande_initial[$i]['motif']);
                $investigationFinancier->setMontant($demande_initial[$i]['montant']);
                $investigationFinancier->setPourcentage($pourcentage);
                $investigationFinancier->setDemandefinancier($demande);
                $demande_final[] = $investigationFinancier;
            }
            return $this->json($demande_final, 200, []);
        }
    }