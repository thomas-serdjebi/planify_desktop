<?php 
namespace App\Repository;

use App\Entity\Tournee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class TourneeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tournee::class);
    }

    public function findTourneesByDateAndLivreur(string $type, int $livreurId): array
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.livreur = :livreurId')
            ->setParameter('livreurId', $livreurId);

        switch ($type) {
            case 'past':
                $qb->andWhere('t.date < :today');
                break;
            case 'today':
                $qb->andWhere('t.date = :today');
                break;
            case 'future':
                $qb->andWhere('t.date > :today');
                break;
        }

        $qb->setParameter('today', new \DateTime('today'));

        return $qb->getQuery()->getResult();
    }
}
