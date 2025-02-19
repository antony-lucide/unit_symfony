<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteUserCommand extends Command
{
    protected static $defaultName = 'app:delete-user';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Supprime un utilisateur par email')
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'utilisateur à supprimer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        // Supprimer l'utilisateur par email
        $deleted = $this->entityManager->createQuery(
            'DELETE FROM App\Entity\User u WHERE u.email = :email'
        )
        ->setParameter('email', $email)
        ->execute();

        if ($deleted > 0) {
            $io->success(sprintf('L\'utilisateur avec l\'email %s a été supprimé!', $email));
        } else {
            $io->error(sprintf('Aucun utilisateur trouvé avec l\'email %s', $email));
        }

        return Command::SUCCESS;
    }
} 