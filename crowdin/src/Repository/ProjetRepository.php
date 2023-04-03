<?php

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\TraductionSource;
use App\Entity\TraductionTarget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method Projet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Projet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Projet[]    findAll()
 * @method Projet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projet::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Projet $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Projet $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function ProjetIsTranslate(ManagerRegistry $doctrine, $id_projet){

        $projet = $doctrine->getRepository(Projet::class)->find($id_projet);
        $sources = $doctrine->getRepository(TraductionSource::class)->findBy(['projet_id'=>$id_projet, 'traduit'=>false]);
        if (!$sources){
            $projet->setTraduit(true);
            $doctrine->getRepository(Projet::class)->add($projet);
        } else if($projet->getTraduit() == true) {
            $projet->setTraduit(false);
            $doctrine->getRepository(Projet::class)->add($projet);
        }
    }

    public function ExportCsv(ManagerRegistry $doctrine, $id_projet, $lang){
        $projet = $doctrine->getRepository(Projet::class)->find($id_projet);
        $sources = $doctrine->getRepository(TraductionSource::class)->findBy(['projet_id'=>$id_projet]);
        $myVariableCSV = "key; Source; Traduction;\n";
        $i = 0;
        foreach ($sources as $source) {
            $traduction = $doctrine->getRepository(TraductionTarget::class)->findOneBy(['lang_code'=>$lang, 'traduction_source_id'=>$source->getId()]);
            $myVariableCSV .= "message " . $i . ";". $source->getSource().';';
            if($traduction) {
                $myVariableCSV .= $traduction->getTraduction() . ";\n";
            } else {
                $myVariableCSV .= "; ;\n";
            }
            $i++;
        }
        return new Response(
            $myVariableCSV,
            200,
            [
                'Content-Type' => 'application/vnd.ms-excel',
                "Content-disposition" => "attachment; filename=".$projet->getName() . ".". $lang . ".csv"
            ]);
    }

    public function ProjetTranslateByUser(ManagerRegistry $doctrine, $id_user){

        $traductions = $doctrine->getRepository(TraductionTarget::class)->findBy(['traducteur_id'=>$id_user]);
        $liste_traduction = [];
        foreach ($traductions as $traduction) {
            $liste_traduction = array_merge($liste_traduction, [$traduction->getTraductionSourceId()]);
        }
        $liste_traduction = array_unique($liste_traduction);
        $sources = $doctrine->getRepository(TraductionSource::class)->findBy(['id'=>$liste_traduction]);
        $liste_source = [];
        foreach ($sources as $source) {
            $liste_source = array_merge($liste_source, [$source->getProjetId()]);
        }
        $liste_source = array_unique($liste_source);

        return $doctrine->getRepository(Projet::class)->findBy(['id'=>$liste_source, 'active' => true]);
    }

    // /**
    //  * @return Projet[] Returns an array of Projet objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Projet
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
