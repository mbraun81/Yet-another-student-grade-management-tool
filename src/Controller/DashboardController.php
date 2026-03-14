<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\KlasseRepository;
use App\Repository\FachRepository;

#[IsGranted("ROLE_TEACHER")]
class DashboardController extends AbstractController
{
    public function __construct(
        private readonly KlasseRepository $klasseRepo,
        private readonly FachRepository $fachRepo,
        
    ){}
    
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/info', name: 'app_info')]
    public function info(): Response
    {
        phpinfo();
        return $this->json([]);
    }
    
    
    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'klassen'=>$this->klasseRepo->findVisible(),
            'faecher'=>$this->fachRepo->findVisible(),
        ]);
    }
    
    #[Route('/klassen', name: 'app_klassen')]
    public function klassen(): Response
    {
        return $this->render('dashboard/klassen.html.twig', [
            'klassen'=>$this->klasseRepo->findVisible(),
        ]);
        
    }
    
    #[Route('/faecher', name: 'app_faecher')]
    public function faecher(): Response
    {
        return $this->render('dashboard/faecher.html.twig', [
            'faecher'=>$this->fachRepo->findVisible(),
        ]);
        
    }
}
