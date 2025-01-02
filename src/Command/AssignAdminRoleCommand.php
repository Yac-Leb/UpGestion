<?php
// src/Command/AssignAdminRoleCommand.php
namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssignAdminRoleCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setName('app:assign-admin-role') // Définir le nom de la commande ici
             ->setDescription('Ajoute le rôle ADMIN à un utilisateur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Chercher l'utilisateur par son email
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['mail' => 'yacine@gmail.com']);

        if ($user) {
            // Ajouter le rôle "ROLE_ADMIN" à l'utilisateur
            $user->setRoles(['ROLE_ADMIN']);
            $this->entityManager->flush();

            $output->writeln('L\'utilisateur a été promu administrateur.');
        } else {
            $output->writeln('Utilisateur non trouvé.');
        }

        return Command::SUCCESS;
    }
}
