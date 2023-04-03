<?php

namespace App\Controller;

use App\Entity\Lang;
use App\Entity\LangHasUser;
use App\Entity\Projet;
use App\Entity\TraductionSource;
use App\Entity\TraductionTarget;
use App\Entity\User;
use App\Form\NewProjetType;
use App\Form\TraductionSourceType;
use App\Form\TraductionTargetType;
use App\Repository\ProjetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

class SourceController extends AbstractController
{
    /**
     * @Route("/source/{id}", name="show_source")
     */
    public function show(ManagerRegistry $doctrine, Request $request,EntityManagerInterface $em, $id): Response
    {
        $user = $this->getUser();
        $sources = $this->getDoctrine()->getRepository(TraductionSource::class)->
        find($id);
        $projet = $this->getDoctrine()->getRepository(Projet::class)->
        findOneBy(['id'=>$sources->getProjetId()]);
        $choice_langs = $doctrine->getRepository(Lang::class)->createOptionSelect($doctrine,$em, $this->getUser()->getId(), $projet->getId());
        $form = $this->createForm(TraductionTargetType::class, ['test' => $choice_langs]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $traduction = $this->getDoctrine()->getRepository(TraductionTarget::class)->
            findOneBy(['traduction_source_id'=>$id, 'lang_code'=>$form->get("lang_code")->getData()]);
            if (!$traduction)
                $traduction = new TraductionTarget();
            $traduction->setLangCode($form->get("lang_code")->getData());
            $traduction->setTraductionSourceId($id);
            $traduction->setTraduction($form->get("traduction")->getData());
            $traduction->setTraducteurId($user->getId());
            $doctrine->getRepository(TraductionTarget::class)->add($traduction);
            $doctrine->getRepository(TraductionSource::class)->SourceIsTranslate($doctrine, $projet->getId(), $sources->getId());
            $this->addFlash(
                'notice',
                'Vous avez ajouté de nouvelles sources'
            );


            return $this->redirectToRoute('show_source', ['id' => $id]);
        }

        return $this->render('source/show.html.twig', [
            'NewTrad' => $form->createView(),
            'source' => $sources,
            'lang' => $projet->getLangCode(),
        ]);
    }

    /**
     * @Route("/source/edit/{id}", name="edit_source")
     */
    public function edit(ManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $source = $this->getDoctrine()->getRepository(TraductionSource::class)->
        find($id);

        $projet = $this->getDoctrine()->getRepository(Projet::class)->
        find($source->getProjetId());

        if($projet->getUserId() != $user->getId()){
            return $this->redirectToRoute('show_projet', ['id' => $source->getProjetId()]);
        }
        $form = $this->createForm(TraductionSourceType::class, $source);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get("source")->getData()) {
                $source->setSource($form->get("source")->getData());
                $source->setTraduit(false);
                $doctrine->getRepository(TraductionSource::class)->add($source);
                $this->addFlash(
                    'notice',
                    'Vous avez modifié une source avec succes'
                );
                $sourcetraduits = $this->getDoctrine()->getRepository(TraductionTarget::class)->
                findby(['traduction_source_id'=>$id]);
                foreach ($sourcetraduits as $sourcetraduit) {
                    $doctrine->getRepository(TraductionTarget::class)->remove($sourcetraduit);
                }
                if ($projet->getTraduit() == true){
                    $projet->setTraduit(false);
                    $doctrine->getRepository(Projet::class)->add($projet);
                }
            }
            return $this->redirectToRoute('show_projet', ['id' => $source->getProjetId()]);
        }

        return $this->render('source/edit.html.twig', [
            'NewSource' => $form->createView(),
            'sources' => $source,
            'user'=>$user,
        ]);
    }

    /**
     * @Route("/source/delete/{id}", name="delete_source")
     */
    public function delete(ManagerRegistry $doctrine, Request $request, $id) {
        $user = $this->getUser();

        $source = $this->getDoctrine()->getRepository(TraductionSource::class)->
        find($id);

        $projet = $this->getDoctrine()->getRepository(Projet::class)->
        find($source->getProjetId());
        if ($projet->getUserId() == $user->getId()){
            $sourcetraduits = $this->getDoctrine()->getRepository(TraductionTarget::class)->
            findby(['traduction_source_id'=>$id]);
            foreach ($sourcetraduits as $sourcetraduit) {
                $doctrine->getRepository(TraductionTarget::class)->remove($sourcetraduit);
            }
            $doctrine->getRepository(TraductionSource::class)->remove($source);
            return $this->redirectToRoute('show_projet', ['id' => $projet->getId()]);
        } else {
            $this->addFlash(
                'error',
                'Vous n\'êtes pas le créateur de ce projet, vous ne pouvez donc pas le supprimer'
            );
            return $this->redirectToRoute('show_projet', ['id' => $projet->getId()]);
        }

    }

    /**
     * @Route("/source/block/{id}", name="block_source")
     */
    public function block(ManagerRegistry $doctrine, Request $request, $id) {
        $user = $this->getUser();

        $source = $this->getDoctrine()->getRepository(TraductionSource::class)->
        find($id);
        $projet = $this->getDoctrine()->getRepository(Projet::class)->
        find($source->getProjetId());
        if ($source->getBloque() == true){
            $source->setBloque(false);
            $doctrine->getRepository(TraductionSource::class)->add($source);
            return $this->redirectToRoute('show_projet', ['id' => $projet->getId()]);
        } else {
            $source->setBloque(true);
            $doctrine->getRepository(TraductionSource::class)->add($source);
            return $this->redirectToRoute('show_projet', ['id' => $projet->getId()]);
        }

    }
}
