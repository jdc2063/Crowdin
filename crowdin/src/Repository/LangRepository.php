<?php

namespace App\Repository;

use App\Entity\Lang;
use App\Entity\LangHasProjet;
use App\Entity\LangHasUser;
use App\Entity\Projet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Lang|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lang|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lang[]    findAll()
 * @method Lang[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LangRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lang::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Lang $entity, bool $flush = true): void
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
    public function remove(Lang $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function createOptionSelect(ManagerRegistry $doctrine, EntityManagerInterface $em, $id_user = null, $id_projet = null, $lang_origine = false){
        if ($id_user == null && $id_projet == null) {
            $langs = $em->getRepository(Lang::class)->findAll();
        } else {
            $lang_origine_projet = $doctrine->getRepository(Projet::class)->find($id_projet)->getLangCode();
            $langs_projet = $doctrine->getRepository(LangHasProjet::class)->findBy(['id_projet'=>$id_projet]);
            $langs_in_projet = [];
            foreach ($langs_projet as $lang_projet){
                if ($lang_origine || $lang_origine_projet != $lang_projet->getLang())
                    $langs_in_projet = array_merge($langs_in_projet, [$lang_projet->getLang()]);
            }
            if ($id_user) {
                $langs_user = $doctrine->getRepository(LangHasUser::class)->
                findBy(['user_id' => $id_user]);
                $lang_in_user = [];
                foreach ($langs_user as $lang_user) {
                    if (!$lang_origine || $lang_origine_projet != $lang_user->getLangCode())
                        $lang_in_user = array_merge($lang_in_user, [$lang_user->getLangCode()]);
                }
                $langs = array_unique(array_intersect($lang_in_user, $langs_in_projet));
            } else {
                $langs = $langs_in_projet;
            }
            $langs = $em->getRepository(Lang::class)->findBy(['code'=>$langs]);
        }
        $choice_langs = [];
        foreach ($langs as $lang){
            $choice_langs= array_merge($choice_langs, [$lang->getName()=> $lang->getCode()]);
        }
        return  $choice_langs;
    }
    // /**
    //  * @return Lang[] Returns an array of Lang objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Lang
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
