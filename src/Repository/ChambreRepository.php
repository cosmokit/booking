<?php

namespace App\Repository;

use App\Entity\Chambre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Expr\Array_;

/**
 * @method Chambre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Chambre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Chambre[]    findAll()
 * @method Chambre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChambreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chambre::class);
    }

    // /**
    //  * @return Chambre[] Returns an array of Chambre objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Chambre
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function isChambreAvailable($id , $checkin , $checkout): ?bool
    {
       $chambre = $this->createQueryBuilder('c')
           ->leftJoin('App\Entity\Reservation','r' , 'WITH' , 'c.id = r.chambre')
           ->andWhere('c.id = :id')
           ->andWhere('(r.checkIn IS NULL) OR (:checkin NOT BETWEEN r.checkIn AND r.checkOut)')
           ->andWhere('(r.checkOut IS NULL) OR (:checkout NOT BETWEEN r.checkIn AND r.checkOut)')
           ->setParameter('id', $id)
           ->setParameter('checkin', $checkin)
           ->setParameter('checkout', $checkout)
           ->getQuery()
           ->getOneOrNullResult()
       ;

       if (is_null($chambre)) {
           return false;
       } else {
           return true;
       }
    }

     /**
      * @return Chambre[] Returns an array of Chambre objects
      */
    public function findChambresByHotel($hotelId)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.hotel = :hotelId')
            ->setParameter('hotelId', $hotelId)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param $rooms
     * @param $stars
     * @param $type
     * @param $distance
     * @param $maxprice
     * @return Chambre[] Returns an array of Chambre objects
     */
    public function filter($rooms, $stars, $type ,$distance, $maxprice)
    {
        $result = array();
        foreach ($rooms as $room) {
            if ($distance==0) {
                if($room->getHotel()->getDistanceCentre() < 1 && $room->getHotel()->getNbrEtoiles()==$stars && $room->getCategorie()==$type) {
                    if($room->getPrixSaison()->getPrix()*$room->getPrixSaison()->getTaux() < $maxprice) {
                        array_push($result, $room);
                    }
                }
            } else {
                if($room->getHotel()->getDistanceCentre() < $distance && $room->getHotel()->getNbrEtoiles()==$stars && $room->getCategorie()==$type) {
                    if($room->getPrixSaison()->getPrix()*$room->getPrixSaison()->getTaux() < $maxprice) {
                        array_push($result, $room);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @return Chambre[] Returns an array of Chambre objects
     */
    public function findAllChambreByHotel($hotelId)
    {
        return $this->createQueryBuilder('c')
        ->join('App\Entity\Hotel','h')
        ->andWhere('h.id = :hotelId')
        ->andWhere('c.hotel = h.id')
        ->setParameter('hotelId', $hotelId)
        ->getQuery()
        ->getResult()
    ;
    }


    //old query not good need optimisation
     /**
      * @return Chambre[] Returns an array of Chambre objects
      */
    public function findByInputsOld($destination, $checkin, $checkout ,$guests)
    {
        if ($guests > 3) {
            return $this->createQueryBuilder('c')
                ->leftJoin('App\Entity\Reservation', 'r', 'WITH', 'c.id = r.chambre')
                ->join('App\Entity\Hotel', 'h')
                ->andWhere('c.hotel = h.id')
                ->andWhere('c.capacity >= :guests')
                ->andWhere('(r.checkIn IS NULL) OR (:checkin NOT BETWEEN r.checkIn AND r.checkOut)')
                ->andWhere('(r.checkOut IS NULL) OR (:checkout NOT BETWEEN r.checkIn AND r.checkOut)')
                ->andWhere('h.region = :destination OR h.ville = :destination OR h.nom = :destination')
                ->setParameter('checkin', $checkin)
                ->setParameter('checkout', $checkout)
                ->setParameter('destination', $destination)
                ->setParameter('guests', $guests)
                ->getQuery()
                ->getResult();
        } elseif ($guests == 0) {
            return $this->createQueryBuilder('c')
                ->leftJoin('App\Entity\Reservation','r' , 'WITH' , 'c.id = r.chambre')
                ->join('App\Entity\Hotel','h')
                ->andWhere('c.hotel = h.id')
                ->andWhere('(r.checkIn IS NULL) OR (:checkin NOT BETWEEN r.checkIn AND r.checkOut)')
                ->andWhere('(r.checkOut IS NULL) OR (:checkout NOT BETWEEN r.checkIn AND r.checkOut)')
                ->andWhere('h.region = :destination OR h.ville = :destination OR h.nom = :destination ')
                ->setParameter('checkin', $checkin)
                ->setParameter('checkout', $checkout)
                ->setParameter('destination', $destination)
                ->getQuery()
                ->getResult()
                ;
        } else {
            return $this->createQueryBuilder('c')
                ->leftJoin('App\Entity\Reservation','r' , 'WITH' , 'c.id = r.chambre')
                ->join('App\Entity\Hotel','h')
                ->andWhere('c.hotel = h.id')
                ->andWhere('c.capacity = :guests')
                ->andWhere('(r.checkIn IS NULL) OR (:checkin NOT BETWEEN r.checkIn AND r.checkOut)')
                ->andWhere('(r.checkOut IS NULL) OR (:checkout NOT BETWEEN r.checkIn AND r.checkOut)')
                ->andWhere('h.region = :destination OR h.ville = :destination OR h.nom = :destination ')
                ->setParameter('checkin', $checkin)
                ->setParameter('checkout', $checkout)
                ->setParameter('destination', $destination)
                ->setParameter('guests', $guests)
                ->getQuery()
                ->getResult()
                ;
        }
    }

    //new query well organized
    /**
     * @return Chambre[] Returns an array of Chambre objects
     */
    public function findByInputs($destination, $checkin, $checkout ,$guests)
    {
            $querybuilder = $this->createQueryBuilder('c')
                ->leftJoin('App\Entity\Reservation','r' , 'WITH' , 'c.id = r.chambre')
                ->join('App\Entity\Hotel','h')
                ->andWhere('c.hotel = h.id')
                ->andWhere('(r.checkIn IS NULL) OR (:checkin NOT BETWEEN r.checkIn AND r.checkOut)')
                ->andWhere('(r.checkOut IS NULL) OR (:checkout NOT BETWEEN r.checkIn AND r.checkOut)')
                ->andWhere('h.region = :destination OR h.ville = :destination OR h.nom = :destination ')
                ->setParameter('checkin', $checkin)
                ->setParameter('checkout', $checkout)
                ->setParameter('destination', $destination)
            ;

            if($guests == 0) {
                $query = $querybuilder->getQuery();
            } elseif ($guests <= 3) {
                $query = $querybuilder
                    ->andWhere('c.capacity = :guests')
                    ->setParameter('guests', $guests)
                    ->getQuery();
            }
            else {
                $query = $querybuilder
                    ->andWhere('c.capacity >= :guests')
                    ->setParameter('guests', $guests)
                    ->getQuery();
            }
            return $query->getResult();
    }
}