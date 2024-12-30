<?php
// src/Command/AddAdminCommand.php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddAdminCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setName('app:add-admin-role')
             ->setDescription('Ajoute le rôle ADMIN à un utilisateur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Récupérer l'utilisateur par son email
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['mail' => 'yacine@gmail.com']);

        if ($user) {
            // Vérifier si l'utilisateur a déjà le rôle ROLE_ADMIN
            $roles = $user->getRoles();
            if (!in_array('ROLE_ADMIN', $roles)) {
                $roles[] = 'ROLE_ADMIN'; // Ajouter le rôle ADMIN
                $user->setRoles($roles);

                // Sauvegarder les modifications dans la base de données
                $this->entityManager->flush();

                $output->writeln('Le rôle ROLE_ADMIN a été ajouté à l\'utilisateur.');
                return Command::SUCCESS;  // Retourner SUCCESS
            } else {
                $output->writeln('L\'utilisateur a déjà le rôle ROLE_ADMIN.');
                return Command::SUCCESS;  // Même retour si l'utilisateur a déjà le rôle
            }
        } else {
            $output->writeln('Utilisateur non trouvé.');
            return Command::FAILURE;  // Retourner FAILURE si l'utilisateur n'est pas trouvé
        }
    }
}
