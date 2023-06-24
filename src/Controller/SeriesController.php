<?php

namespace App\Controller;

use App\Entity\Series;
use App\Repository\SeriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeriesController extends AbstractController
{

    public function __construct(private SeriesRepository $seriesRepository,
                                private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/series', name: 'app_series', methods: ['GET'])]
    public function seriesList(Request $request): Response
    {
        $seriesList = $this->seriesRepository->findAll();
        $session = $request->getSession();

        $successMessageDelete = $session->get('successDelete');
        $session->remove('successDelete');

        $successMessageAddSeries = $session->get('successAddSeries');
        $session->remove('successAddSeries');

        return $this->render('series/index.html.twig', [
            'seriesList' => $seriesList,
            'successMessageDelete' => $successMessageDelete,
            'successMessageAddSeries' => $successMessageAddSeries
        ]);
    }


    #[Route('/series/create', name: 'app_series_form_GET' ,methods: ['GET'])]
    public function addSeriesForm(): Response
    {
        return $this->render('series/form.html.twig'); //atalho alt+enter
    }

    #[Route('/series/create', name: 'app_series_form_POST', methods: ['POST'])]
    public function addSeries(Request $req): Response
    {
        $seriesName =  $req->request->get('name');
        $series = new Series($seriesName);

        $session = $req->getSession();
        $session->set('successAddSeries', 'Série Adicionada com sucesso');

        $this->seriesRepository->save($series, true);

        return new RedirectResponse('/series');

    }


    /* com requirements, é restringir o que vem na url, por exemplo, aqui to falando que o id
só pode ser digitos, se eu colocar uma string, vai dar erro antes mesmo de checar na função */
    #[Route('/series/delete/{id}',
        name: 'app_delete_series',
        methods: ['DELETE'],
        requirements: ['id' => '[0-9]+']
    )]
    public function deleteSeries(int $id, Request $request): Response
    {
        $this->seriesRepository->removeById($id);

        $session = $request->getSession();
        $session->set('successDelete', 'Série removida com sucesso');

        return new RedirectResponse('/series');
    }

    #[Route('/series/edit/{id}', name: 'app_edit_series', methods: ['GET'],
        requirements: ['id' => '[0-9]+'])]
    public function editSeriesForm(int $id):Response
    {
        /*
        A injeção de dependência automática não ta funcionando, nas configurações até onde vi
        estão normais, então fui no banco manualmente para pegar o name
        */

        $series = $this->seriesRepository->find($id);

        return $this->render('series/form.html.twig', compact('series'));
       /* Funciona igual em cima, a função compact é para quando a chave tiver o mesmo nome do
        da variavel valor'*/
        /*return $this->render('series/form.html.twig', [
            'series' => $series
        ]);*/
    }

}
