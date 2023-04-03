<?php

namespace App\Controller;

use App\Entity\Lang;
use App\Entity\User;
use App\Entity\LangHasUser;
use App\Form\LangHasUserType;
use App\Repository\LangHasUserRepository;
use App\Repository\LangRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @Route("/lang/has/user")
 */
class LangHasUserController extends AbstractController
{
    /**
     * @Route("/", name="app_lang_has_user_index", methods={"GET"})
     */
    public function index(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();
        $langhasuser = $this->getDoctrine()->getRepository(LangHasUser::class)->
        findBy(['user_id' => $user->getId()]);

        $langhasuser = $paginator->paginate(
            $langhasuser, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            6 // Nombre de résultats par page
        );

        return $this->render('lang_has_user/index.html.twig', [
            'lang_has_users' => $langhasuser,
        ]);
    }

    /**
     * @Route("/new", name="app_lang_has_user_new", methods={"GET", "POST"})
     */
    public function new(ManagerRegistry $doctrine, Request $request, LangHasUserRepository $langHasUserRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $langHasUser = new LangHasUser();
        $choice_langs = $doctrine->getRepository(Lang::class)->createOptionSelect($doctrine, $em);
        $form = $this->createForm(LangHasUserType::class,$langHasUser, ['data'=>$choice_langs]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $langHasUser->setUserId($user->getId());
            $langHasUser->setLangCode($form->get("lang_code")->getData());

            $condition = $this->getDoctrine()->getRepository(LangHasUser::class)->
            findBy(['lang_code' => $form->get("lang_code")->getData(), 'user_id' => $user->getId()]);

            if (!$condition) {
                $langHasUserRepository->add($langHasUser);
            }

            return $this->redirectToRoute('app_lang_has_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lang_has_user/new.html.twig', [
            'lang_has_user' => $langHasUser,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/{id}/edit", name="app_lang_has_user_edit", methods={"GET", "POST"})
     */
    public function edit(ManagerRegistry $doctrine, Request $request, LangHasUser $langHasUser, LangHasUserRepository $langHasUserRepository, EntityManagerInterface $em, $id): Response
    {
        $langHasUser = $em->getRepository(LangHasUser::class)->find($id);
        $user = $this->getUser();
        $choice_langs = $doctrine->getRepository(Lang::class)->createOptionSelect($doctrine, $em);
        $form = $this->createForm(LangHasUserType::class, $langHasUser, ['data'=>$choice_langs]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $langHasUser->setLangCode($form->get("lang_code")->getData());
            $condition = $this->getDoctrine()->getRepository(LangHasUser::class)->
            findBy(['lang_code' => $form->get("lang_code")->getData(), 'user_id' => $user->getId()]);
            
            if (!$condition) {
                $langHasUserRepository->add($langHasUser);
            }
            return $this->redirectToRoute('app_lang_has_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lang_has_user/edit.html.twig', [
            'lang_has_user' => $langHasUser,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="app_lang_has_user_delete", methods={"POST"})
     */
    public function delete(Request $request, LangHasUser $langHasUser, LangHasUserRepository $langHasUserRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$langHasUser->getId(), $request->request->get('_token'))) {
            $langHasUserRepository->remove($langHasUser);
        }

        return $this->redirectToRoute('app_lang_has_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
