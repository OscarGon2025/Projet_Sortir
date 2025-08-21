<?php

namespace App\Controller;

use App\Service\UserImportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/admin', name: 'admin_')]
class UserImportController extends AbstractController
{
    private UserImportService $userImportService;

    public function __construct(UserImportService $userImportService)
    {
        $this->userImportService = $userImportService;
    }

    #[Route('/import-users', name: 'import_users')]
    public function import(Request $request): Response
    {
        $message = null;

        if ($request->isMethod('POST')) {
            $file = $request->files->get('csv_file');

            if ($file) {
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
                $fileName = uniqid() . '-' . $file->getClientOriginalName();

                try {
                    $file->move($uploadDir, $fileName);
                    $filePath = $uploadDir . '/' . $fileName;

                    $count = $this->userImportService->importUsers($filePath);
                    $message = "Import de $count utilisateurs effectué avec succès !";

                } catch (FileException $e) {
                    $message = "Erreur lors de l'upload du fichier : " . $e->getMessage();
                } catch (\Exception $e) {
                    $message = "Erreur lors de l'import des utilisateurs : " . $e->getMessage();
                }
            } else {
                $message = "Veuillez sélectionner un fichier CSV.";
            }
        }

        return $this->render('user_import/import-user.html.twig', [
            'message' => $message,
        ]);
    }
}
