<?php

namespace App\Controller;

use App\Entity\Lang;
use App\Entity\LangHasProjet;
use App\Entity\LangHasUser;
use App\Entity\Projet;
use App\Entity\TraductionSource;
use App\Entity\TraductionTarget;
use App\Entity\User;
use App\Form\ExportType;
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
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Knp\Component\Pager\PaginatorInterface;

class TableauDeBordController extends AbstractController
{
    /**
     * @Route("/projet/{id}/tableau", name="show_tableau")
     */
    public function show(ManagerRegistry $doctrine, Request $request,EntityManagerInterface $em, $id, ChartBuilderInterface $chartBuilder): Response
    {
        $projet = $this->getDoctrine()->getRepository(Projet::class)->
        find($id);
        $sources = $this->getDoctrine()->getRepository(TraductionSource::class)->
        findBy(['projet_id'=>$id]);
        $liste_source = [];
        foreach ($sources as $source) {
            $liste_source = array_merge($liste_source, [$source->getId()]);
        }

        $sources_traduite = $this->getDoctrine()->getRepository(TraductionSource::class)->
        findBy(['projet_id'=>$id, 'traduit'=>true]);
        $langs_projet_translate = $this->getDoctrine()->getRepository(LangHasProjet::class)->
        findBy(['id_projet'=>$id]);
        $sources = count($sources);
        $sources_traduite = count($sources_traduite);
        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $labels = [];
        $data = [];
        foreach ($langs_projet_translate as $lang_projet_translate) {
            $lang = $this->getDoctrine()->getRepository(Lang::class)->
            findOneBy(['code'=>$lang_projet_translate->getLang()]);
            $labels = array_merge($labels, [$lang->getName()]);
            $data = array_merge($data, [count($this->getDoctrine()->getRepository(TraductionTarget::class)->
            findBy(['traduction_source_id'=>$liste_source, 'lang_code'=>$lang->getCode()]))]);

        }
        $chart->setData([
            'labels' => array_merge(['Sources', 'Sources Traduites'], $labels),
            'datasets' => [
                [
                    'label' => 'Suivi du projet',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => array_merge([$sources, $sources_traduite],$data),
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive'=> false,
        ]);
        $choice_langs = $doctrine->getRepository(Lang::class)->createOptionSelect($doctrine,$em, null, $projet->getId(), true);
        $form = $this->createForm(ExportType::class, ['data' => $choice_langs]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return  $doctrine->getRepository(Projet::class)->ExportCsv($doctrine,$projet->getId(), $form->get("lang_code")->getData());
        }
        return $this->render('tableau/show.html.twig', [
            'projet' => $projet,
            'lang' => $projet->getLangCode(),
            'nombre_source'=> $sources,
            'nombre_traduit'=> $sources_traduite,
            'chart' => $chart,
            'ExportForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/projet/{id}/export", name="export_source")
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
}
