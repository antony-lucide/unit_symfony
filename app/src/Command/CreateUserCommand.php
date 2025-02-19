<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';

    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Crée un nouvel utilisateur')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email de l\'utilisateur')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Mot de passe de l\'utilisateur')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Nom de l\'utilisateur')
            ->addOption('role', null, InputOption::VALUE_REQUIRED, 'Rôle de l\'utilisateur (ROLE_USER ou ROLE_ADMIN)', 'ROLE_USER');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupérer les options ou demander interactivement
        $email = $input->getOption('email');
        if (!$email) {
            $email = $io->ask('Email de l\'utilisateur?', 'user@example.com');
        }

        $password = $input->getOption('password');
        if (!$password) {
            $password = $io->askHidden('Mot de passe?', function () { return 'user123'; });
        }

        $name = $input->getOption('name');
        if (!$name) {
            $name = $io->ask('Nom de l\'utilisateur?', 'User');
        }

        $role = $input->getOption('role');
        if (!in_array($role, ['ROLE_USER', 'ROLE_ADMIN'])) {
            $role = 'ROLE_USER';
        }

        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setRoles([$role]);

        // Hash du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Sauvegarde dans la base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Utilisateur créé avec succès!');
        $io->note('Email: ' . $email);
        $io->note('Mot de passe: ' . $password);
        $io->note('Rôle: ' . $role);

        return Command::SUCCESS;
    }
} 