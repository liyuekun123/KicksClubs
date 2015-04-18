<?php

namespace Neikeq\ClubsBundle\DependencyInjection;

use Doctrine\ORM\EntityManager;

use Neikeq\ClubsBundle\NeikeqClubsBundle;

class PlayerUtils
{
    public static function getPlayerRole($playerId)
    {
        if (self::mustSelectCharacter($playerId)) {
            return '';
        }

        $em = NeikeqClubsBundle::getEm();

        $qb = $em->createQueryBuilder();

        $qb->select('m.id, m.role')
           ->from('NeikeqClubsBundle:ClubMembers','m')
           ->where('m.playerId = ?1')
           ->setParameter(1, $playerId)
           ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();

        if ($result != null) {
            return $result['role'];
        }

        return 'CHARACTER';
    }

    public static function getCharacterNameById($playerId)
    {
        $em = NeikeqClubsBundle::getEm();

        $characterQB = $em->createQueryBuilder();
        $characterQB->select('c.name')
           ->from('NeikeqClubsBundle:Characters','c')
           ->where('c.id = ?1')
           ->setParameter(1, $playerId)
           ->setMaxResults(1);
        $result = $characterQB->getQuery()->getOneOrNullResult();

        return $result['name'];
    }

    public static function characterParam($playerId)
    {
        return array('id' => $playerId, 'name' => self::getCharacterNameById($playerId));
    }

    public static function mustSelectCharacter($playerId)
    {
        return $playerId == null || $playerId <= 0;
    }

    public static function getSlotByIndex($index, $user)
    {
        switch ($index) {
            case 0:
                return $user->getSlotOne();
            case 1:
                return $user->getSlotTwo();
            case 2:
                return $user->getSlotThree();
            default:
                return null;
        }
    }

    public static function characterList($user)
    {
        $characters = array();

        for ($i = 0; $i < 3; $i++) {
            $playerId = self::getSlotByIndex($i, $user);

            if ($playerId != null && $playerId > 0) {
                array_push($characters, self::characterParam($playerId));
            }
        }

        return array('username' => '', 'characters' => $characters);
    }
}
