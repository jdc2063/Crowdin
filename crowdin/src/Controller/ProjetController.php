<?php

namespace App\Controller;

use App\Entity\Lang;
use App\Entity\LangHasProjet;
use App\Entity\LangHasUser;
use App\Entity\Projet;
use App\Entity\TraductionSource;
use App\Entity\User;
use App\Form\NewProjetType;
use App\Form\TraductionSourceType;
use App\Repository\ProjetRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

class ProjetController extends AbstractController
{
    /**
     * @Route("/projet/", name="home_projet")
     */
    public function home(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();
        $user_langs = $this->getDoctrine()->getRepository(LangHasUser::class)->findBy(["user_id" => $user->getId()]);
        $langs = [];
        foreach ($user_langs as $user_lang) {
            array_push($langs, $user_lang->getLangCode());
        }

        $projet_as_langs = $this->getDoctrine()->getRepository(LangHasProjet::class)->findBy(['lang' => $langs]);
        $projet_can_translate = [];
        foreach ($projet_as_langs as $projet_as_lang) {
            array_push($projet_can_translate, $projet_as_lang->getIdProjet());
        }
        $projet = $this->getDoctrine()->getRepository(Projet::class)->
        findBy(['lang_code' => $langs, 'id'=>$projet_can_translate, 'active'=>true, 'traduit'=>false]);
        shuffle($projet);
        $projet = $paginator->paginate(
            $projet, // Requête contenant les données à paginer (ici nos articles)
            $request->query->getInt('page', 1), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
            5 // Nombre de résultats par page
        );

        return $this->render('projet/home.html.twig', [
            'controller_name' => 'ProjetController',
            'projets' => $projet
        ]);
    }

    /**
     * @Route("/projet/new", name="new_projet")
     */
    public function new(ManagerRegistry $doctrine, Request $request): Response
    {
        $projet = new Projet();
        $user = $this->getUser();
        $form = $this->createForm(NewProjetType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $projet->setName($form->get("name")->getData());
            $projet->setLangCode($form->get("lang_code")->getData()->getCode());
            $projet->setUserId($user->getId());
            $projet->setActive(true);
            $projet->setTraduit(false);
            $projet->setCreationDate(new Datetime);
            $doctrine->getRepository(Projet::class)->add($projet);

            $langs_to_translate = $form->get("lang_has_projet")->getData();

            foreach ($langs_to_translate as $lang_to_translate) {
                $lang_as_projet = new LangHasProjet();
                $lang_as_projet->setLang($lang_to_translate->getCode());
                $lang_as_projet->setIdProjet($projet->getId());
                $doctrine->getRepository(LangHasProjet::class)->add($lang_as_projet);
            }
            return $this->redirectToRoute('show_projet', ['id' => $projet->getId()]);
        }

        return $this->render('projet/index.html.twig', [
            'NewProjet' => $form->createView(),
            'controller_name' => 'ProjetController',
        ]);
    }

    /**
     * @Route("/projet/{id}", name="show_projet")
     */
    public function show(ManagerRegistry $doctrine, Request $request, $id): Response
    {
        $user = $this->getUser();
        $projet = $this->getDoctrine()->getRepository(Projet::class)->
            find($id);
        $sources = $this->getDoctrine()->getRepository(TraductionSource::class)->
        findBy(['projet_id'=>$id]);
        $lang = $this->getDoctrine()->getRepository(Lang::class)->findBy(['code'=> $projet->getLangCode()]);
        $form = $this->createForm(TraductionSourceType::class);
        $gerant = $this->getDoctrine()->getRepository(User::class)->
        find($projet->getUserId());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get("source")->getData()) {
                $source = new TraductionSource();
                $source->setProjetId($id);
                $source->setSource($form->get("source")->getData());
                $source->setTraduit(false);
                $source->setBloque(false);
                $doctrine->getRepository(TraductionSource::class)->add($source);
                $this->addFlash(
                    'notice',
                    'Vous avez ajouté de nouvelles sources'
                );
            }

            if ($importFile = $form->get("importfile")->getData()) {
                $originalFilename = pathinfo($importFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$importFile->guessExtension();

                try {
                    $importFile->move(
                        $this->getParameter('source_directory'),
                        $newFilename
                    );
                    $doctrine->getRepository(TraductionSource::class)->ParseCsv($this->getParameter('source_directory').'/'.$newFilename, $id);
                } catch (FileException $e) {

                }

            }
            return $this->redirectToRoute('show_projet', ['id' => $id]);
        }

        return $this->render('projet/show.html.twig', [
            'projet' => $projet,
            'NewSource' => $form->createView(),
            'sources' => $sources,
            'gerant' => $gerant,
            'lang'=>$lang[0],
            'user'=>$user,
        ]);
    }

    /**
     * @Route("/projet/delete/{id}", name="delete_projet")
     */
    public function delete(ManagerRegistry $doctrine, Request $request, $id) {
        $user = $this->getUser();
        $projet = $this->getDoctrine()->getRepository(Projet::class)->
        find($id);
        $this->denyAccessUnlessGranted('DELETE', $projet);
        if ($projet->getUserId() == $user->getId()){
            $projet->setActive(false);
            $doctrine->getRepository(Projet::class)->add($projet);
            return $this->redirectToRoute('home_projet');
        } else {
            $this->addFlash(
                'error',
                'Vous n\'êtes pas le créateur de ce projet, vous ne pouvez donc pas le supprimer'
            );
            return $this->redirectToRoute('show_projet', ['id' => $id]);
        }

    }
}
