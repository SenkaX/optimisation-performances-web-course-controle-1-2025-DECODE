<?php

namespace App\Controller;

use App\Repository\DirectusFilesRepository;
use App\Repository\GalaxyRepository;
use App\Repository\ModelesFilesRepository;
use App\Repository\ModelesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class CarouselController extends AbstractController
{
    #[Route('/carousel', name: 'app_carousel')]
    public function index(GalaxyRepository $galaxyRepository, CacheInterface $cache): Response
    {
        $carousel = $cache->get('carousel_data_v1', function (ItemInterface $item) use ($galaxyRepository) {
            $item->expiresAfter(3600); // Cache 1h
            
            $rawData = $galaxyRepository->findAllWithModelsAndFiles();
            
            // Regroupement des données par galaxie en PHP
            $carouselData = [];
            $currentGalaxyId = null;
            
            foreach ($rawData as $row) {
                // Nouvelle galaxie détectée
                if ($currentGalaxyId !== $row['galaxy_id']) {
                    $currentGalaxyId = $row['galaxy_id'];
                    
                    $carouselData[$currentGalaxyId] = [
                        'title' => $row['galaxy_title'],
                        'description' => $row['galaxy_description'],
                        'files' => []
                    ];
                }
                
                // Ajouter le fichier si présent
                if ($row['file_id']) {
                    $carouselData[$currentGalaxyId]['files'][] = [
                        'filename_disk' => $row['filename_disk']
                    ];
                }
            }
            
            // Conversion en tableau indexé (sans les clés galaxy_id)
            return array_values($carouselData);
        });
        
        $response = $this->render('carousel/index.html.twig', [
            'carousel' => $carousel
        ]);
        
        // Headers HTTP Cache : 1 heure (3600 secondes)
        $response->setSharedMaxAge(3600);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->setPublic();
        
        // ETag pour validation conditionnelle
        $response->setEtag(md5($response->getContent()));
        
        return $response;
    }
}
