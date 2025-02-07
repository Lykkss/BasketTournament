<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-admin')]
class CreateAdminCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Vérifie si un admin existe déjà
        $existingAdmin = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']);
        if ($existingAdmin) {
            $output->writeln('<comment>⚠️ Un administrateur existe déjà.</comment>');
            return Command::FAILURE;
        }

        // Création de l'admin
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setPrenom('Admin');
        $admin->setNom('SuperAdmin');
        $admin->setRoles(['ROLE_ADMIN']);

        // Hachage du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->setPassword($hashedPassword);

        // Sauvegarde en base de données
        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $output->writeln('<info>✅ Administrateur créé avec succès !</info>');
        return Command::SUCCESS;
    }
}
