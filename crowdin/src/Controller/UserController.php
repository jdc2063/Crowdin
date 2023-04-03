<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Projet;
use App\Entity\LangHasUser;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="app_user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'getuser' => $user
        ]);
    }

    /**
     * @Route("/{id}", name="app_user_show", methods={"GET"})
     */
    public function show(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator, ChartBuilderInterface $chartBuilder, $id): Response
    {
        $user = $this->getDoctrine()->getRepository(User::class)->
        find($id);
        $projet = $this->getDoctrine()->getRepository(Projet::class)->
        findBy(['user_id' => $id, 'active' =>  true]);

        $projet = $paginator->paginate(
            $projet, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            6 // Nombre de résultats par page
        );
        $langhasuser = $this->getDoctrine()->getRepository(LangHasUser::class)->
        findBy(['user_id' => $id]);
        $langhasuser = $paginator->paginate(
            $langhasuser, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            6 // Nombre de résultats par page
        );
        $projet_traduit = $doctrine->getRepository(Projet::class)->ProjetTranslateByUser($doctrine, $id);
        $projet_traduits = $paginator->paginate(
            $projet_traduit, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            6 // Nombre de résultats par page
        );
        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => array_merge(['Langue maitriser', 'Projet Créer', 'Projet Traduites']),
            'datasets' => [
                [
                    'label' => 'Suivi du projet',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => array_merge([count($langhasuser), count($projet), count($projet_traduit)]),
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive'=> false,
        ]);
        return $this->render('user/show.html.twig', [
            'user' => $user,
            'projets' => $projet,
            'langhasuser' => $langhasuser,
            'projet_traduits' => $projet_traduits,
            'chart' => $chart,
        ]);

    }

    /**
     * @Route("/{id}/edit", name="app_user_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('EDIT', $user);
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->add($user);
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
        //dd($form, $user);
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="app_user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $user);
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
