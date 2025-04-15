<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use App\Model\Filtre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function add(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllExceptArchive()
    {
        return $this->createQueryBuilder('e')
            ->join('e.status', 's')
            ->addSelect('s')
            ->andWhere('s.type != :status')
            ->setParameter('status', 'Archivée')
            ->orderBy('e.startDateTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function findByRecherche(Filtre $filtre, User $user, int $page, int $limit): Paginator
    {

        // Création du QueryBuilder
        $queryBuilder = $this->createQueryBuilder('e')
            // Jointures avec les relations nécessaires
            ->leftJoin('e.organizer', 'o')
            ->addSelect('o')
            ->leftJoin('e.status', 's')
            ->addSelect('s')
            ->andWhere('s.type != :status')
            ->setParameter('status', 'Archivée')
            ->orderBy('e.startDateTime', 'ASC');



        // Filtre par recherche (nom de l'événement)
        if ($filtre->getSearch() !== null) {
            $queryBuilder->andWhere('e.name LIKE :name')
                ->setParameter('name', '%' . $filtre->getSearch() . '%');
        }

        // Filtre par site
        if ($filtre->getSite() !== null) {
            $queryBuilder->andWhere('e.site = :site')
                ->setParameter('site', $filtre->getSite());
        }

        // Filtre par date de début
        if ($filtre->getStartDateTime() !== null) {
            $queryBuilder->andWhere('e.startDateTime >= :startDateTime')
                ->setParameter('startDateTime', $filtre->getStartDateTime());
        }

        // Filtre par date limite d'inscription
        if ($filtre->getRegistrationDeadline() !== null) {
            $queryBuilder->andWhere('e.registrationDeadline < :registrationDeadline')
                ->setParameter('registrationDeadline', $filtre->getRegistrationDeadline());
        }

        // Filtre par organisateur
        if ($filtre->getOrganizer() !== false) {
            $queryBuilder->andWhere('e.organizer = :organizerId')
                ->setParameter('organizerId', $user->getId());
        }

        // Filtre par inscription de l'utilisateur
        if ($filtre->getIsRegister() !== false) {
            $queryBuilder->andWhere('(e.organizer = :userIdForOrganizer OR :userId MEMBER OF e.users)')
                ->setParameter('userIdForOrganizer', $user->getId())
                ->setParameter('userId', $user);
        }

        // Filtre par non-inscription de l'utilisateur
        if ($filtre->getUnRegister() !== false) {
            $queryBuilder->andWhere('e.organizer != :userIdForNonOrganizer')
                ->andWhere(':userNonRegister NOT MEMBER OF e.users')
                ->setParameter('userIdForNonOrganizer', $user->getId())
                ->setParameter('userNonRegister', $user);
        }

        // Filtre par événements passés
        if ($filtre->getIsPast() !== false) {
            $queryBuilder->andWhere('e.startDateTime < :dateToDay')
                ->setParameter('dateToDay', new \DateTime('now'));
        }

        // Calcul de l'offset pour la pagination
        $firstResult = ($page - 1) * $limit;


        // Exécution de la requête avec pagination
        $query = $queryBuilder->getQuery();
        $query->setFirstResult($firstResult);
        $query->setMaxResults($limit);

               return new Paginator($query);

    }

    /**
     * Renvoie les informations de pagination pour un paginator donné
     */
    public function getPaginationData(Paginator $paginator, int $page, int $limit): array
    {
        // Calculer le nombre total d'éléments
        $totalItems = count($paginator);

        // Calculer le nombre total de pages
        $totalPages = ceil($totalItems / $limit);

        return [
            'items' => $paginator,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'hasPreviousPage' => $page > 1,
            'hasNextPage' => $page < $totalPages,
            'previousPage' => max(1, $page - 1),
            'nextPage' => min($totalPages, $page + 1),
        ];
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
