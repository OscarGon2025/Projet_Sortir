<?php

namespace App\Command;

use App\Service\UserImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import-users',
    description: 'Import des utilisateurs')]

class ImportUserCommand extends Command {

    private UserImportService $userImportService;

    public function __construct(UserImportService $userImportService) {
        $this->userImportService = $userImportService;
        parent::__construct();
    }

    protected function configure (): void {
    $this->addArgument('file', InputArgument::REQUIRED ,'C:\Users\msola2025\Desktop\test-import-user\users-import.csv');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $filePath = $input->getArgument('file');
        try {
            $count = $this->userImportService->importUsers($filePath);
            $output->writeln("Import de $count utilisateurs effectué");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>Erreur : {$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }

}