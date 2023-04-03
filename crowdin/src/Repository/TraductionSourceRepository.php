<?php

namespace App\Repository;

use App\Entity\LangHasProjet;
use App\Entity\Projet;
use App\Entity\TraductionSource;
use App\Entity\TraductionTarget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TraductionSource|null find($id, $lockMode = null, $lockVersion = null)
 * @method TraductionSource|null findOneBy(array $criteria, array $orderBy = null)
 * @method TraductionSource[]    findAll()
 * @method TraductionSource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TraductionSourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TraductionSource::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(TraductionSource $entity, bool $flush = true): void
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
    public function remove(TraductionSource $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public  function ParseCsv(string $file, int $projetId): void
    {
        if (($handle = fopen($file, 'r')) !== FALSE) { // Check the resource is valid
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) { // Check opening the file is OK!
                if(count($data) == 2 && $data[0] != 'key' && $data[1] != 'traduction') {
                    $source = new TraductionSource();
                    $source->setProjetId($projetId);
                    $source->setSource($data[1]);
                    self::add($source);
                }
            }
            fclose($handle);
        }
    }

    public function SourceIsTranslate(ManagerRegistry $doctrine, $id_projet, $id_source){

        $projet = $doctrine->getRepository(Projet::class)->find($id_projet);
        $langs_projet = $doctrine->getRepository(LangHasProjet::class)->findBy(['id_projet'=>$id_projet]);
        $langs_translate = $doctrine->getRepository(TraductionTarget::class)->findBy(['traduction_source_id'=>$id_source]);
        $source = $doctrine->getRepository(TraductionSource::class)->find($id_source);
        if (count($langs_projet) == count($langs_translate)){
            $source->setTraduit(true);
            $doctrine->getRepository(TraductionSource::class)->add($source);
            $doctrine->getRepository(Projet::class)->ProjetIsTranslate($doctrine, $id_projet);
        } else if($projet->getTraduit()== true){
            $source->setTraduit(false);
            $doctrine->getRepository(TraductionSource::class)->add($source);
            $doctrine->getRepository(Projet::class)->ProjetIsTranslate($doctrine, $id_projet);
        }
    }
    // /**
    //  * @return TraductionSource[] Returns an array of TraductionSource objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TraductionSource
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
