<?php

namespace App\Repository;

use App\Entity\DemandeFinancier;
use App\Entity\PersonneMembre;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DemandeFinancier>
 */
class DemandeFinancierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry , private readonly PersonneMembreRepository $personneMembreRepository)
    {
        parent::__construct($registry, DemandeFinancier::class);
    }

    public function differenceEnMois($dateDebut, DateTime $dateFin) {
        $dateDebut = new DateTime($dateDebut);
        $dateFin = $dateFin;
    
        // Extraire les années et les mois
        $anneeDebut = (int) $dateDebut->format('Y');
        $moisDebut = (int) $dateDebut->format('m');
        $anneeFin = (int) $dateFin->format('Y');
        $moisFin = (int) $dateFin->format('m');
        // Calculer la différence totale en mois
        $diffMois = ($anneeFin - $anneeDebut) * 12 + ($moisFin - $moisDebut);
    
        return $diffMois;
    }
    
    public function pourcentage($id) {
        $personneMembre = $this->personneMembreRepository->getPersonne_LastCotisation($id);
        
        $diff100 = $this->differenceEnMois($personneMembre['date_inscription'] , new \DateTime());
        $diffpayer = $this->differenceEnMois($personneMembre['dernier_payement'] , new \DateTime());
        if( $diffpayer < 0){
            $diffpayer =  1;
        }
        if($diff100 == 0){
            $diff100 = 1;
        }
        $pourcentage = ($diffpayer * 100) / $diff100;
        return $pourcentage;
    }
    // public function pourcentage1($id) {
    //     // Récupérer les informations de la personne membre
    //     $personneMembre = $this->personneMembreRepository->getPersonne_LastCotisation($id);
        
    //     // Vérifier si 'date_inscription' est un objet DateTime ou une chaîne de caractères
    //     $dateInscription = $personneMembre['date_inscription'];
    //     if (!$dateInscription instanceof \DateTime) {
    //         $dateInscription = new \DateTime($dateInscription);
            
    //     }
        
    //     // Vérifier si 'dernier_payement' est un objet DateTime ou une chaîne de caractères
    //     $dernierPayement = $personneMembre['dernier_payement'];
    //     if (!$dernierPayement instanceof \DateTime) {
    //         $dernierPayement = new \DateTime($dernierPayement);
    //     }
    //     // Calculer la différence en mois depuis l'inscription jusqu'à aujourd'hui
    //     $diff100 = $this->differenceEnMois($dateInscription->format('Y-m-d'), date('Y-m-d'));
        
    //     // Calculer la différence en mois depuis le dernier paiement jusqu'à aujourd'hui
    //     $diffpayer = $this->differenceEnMois($dernierPayement->format('Y-m-d'), date('Y-m-d'));
        
    //     // Calculer le pourcentage
        
    //     $pourcentage = ($diffpayer * 100) / $diff100;
        
    //     return $pourcentage;
    // }
    public function getDemanceFinancier_With_Investi()
    {
        $sql = '
    SELECT DISTINCT dm.*,
       COALESCE(pc.date_de_payement, mp.date_inscription) AS date_de_payement,
       mp.date_inscription
            FROM demande_financier dm
    LEFT JOIN validation_demande_financier v ON dm.id = v.id_demande_financier_id
    LEFT JOIN payement_cotisation pc ON pc.id_personne_membre_id = dm.id_personne_membre_id
    LEFT JOIN refuser_Demande_Financier rf ON rf.id_demande_financier_id = dm.id
    JOIN personne_membre mp ON mp.id = dm.id_personne_membre_id
        WHERE (pc.date_de_payement IS NULL
            OR pc.date_de_payement = (
                SELECT MAX(p.date_de_payement)
                FROM payement_cotisation p
                WHERE p.id_personne_membre_id = mp.id
                AND p.date_de_payement IS NOT NULL
       ))
        AND v.id_demande_financier_id IS NULL
        AND rf.id_demande_financier_id IS NULL;';
        $conn = $this->getEntityManager()->getConnection();
        
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        
        return $resultSet->fetchAllAssociative();
    }

    //    /**
    //     * @return DemandeFinancier[] Returns an array of DemandeFinancier objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DemandeFinancier
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
