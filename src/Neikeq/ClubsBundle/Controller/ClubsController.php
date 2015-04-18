<?php

namespace Neikeq\ClubsBundle\Controller;

use Neikeq\ClubsBundle\DependencyInjection\ClubUtils;
use Neikeq\ClubsBundle\DependencyInjection\PlayerUtils;
use Neikeq\ClubsBundle\DependencyInjection\PlayerRoles;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ClubsController extends Controller
{
    public function clubsAction()
    {
        return $this->clubsPageAction(1);
    }

    public function clubsPageAction($page)
    {
        $playerId = PlayerUtils::getSelectedPlayer($this->get('session'), $this->getUser());

        if ($this->isGranted('ROLE_USER') && PlayerUtils::mustSelectCharacter($playerId)) {
            return $this->redirect($this->generateUrl('kicks_clubs_character'));
        }

        $em = $this->get('doctrine.orm.entity_manager');

        $clubsCount = ClubUtils::clubsCount($em);
        $clubs = ClubUtils::clubsForPage($page, $em);

        // if user is not authenticated, set username to empty
        $name = $playerId == null ? '' : PlayerUtils::getCharacterNameById($playerId);

        // params for the twig template
        $params = array('name' => $name, 'player_role' => PlayerUtils::getPlayerRole($playerId),
            'clubs' => $clubs, 'pages' => ceil($clubsCount / ClubUtils::clubsPerPage));

        return $this->render('NeikeqClubsBundle:Default:clubs.html.twig', $params);
    }

    public function createAction()
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);

        $playerId = PlayerUtils::getSelectedPlayer($this->get('session'), $this->getUser());

        if (PlayerUtils::mustSelectCharacter($playerId)) {
            return $this->redirect($this->generateUrl('kicks_clubs_character'));
        }

        $playerRole = PlayerUtils::getPlayerRole($playerId);

        // if the user is already a club member, redirect to his club page
        if (PlayerRoles::isGranted('MEMBER', $playerRole)) {
            return $this->redirect($this->generateUrl('kicks_clubs_myclub'));
        }

        $params = array('name' => PlayerUtils::getCharacterNameById($playerId),
            'player_role' => $playerRole);

        return $this->render('NeikeqClubsBundle:Default:create.html.twig', $params);
    }

    public function characterAction()
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);

        $user = $this->getUser();

        $playerId = PlayerUtils::getSelectedPlayer($this->get('session'), $user);

        $params = array('name' => PlayerUtils::getCharacterNameById($playerId),
            'player_role' => PlayerUtils::getPlayerRole($playerId));

        return $this->render('NeikeqClubsBundle:Default:character.html.twig',
            array_merge(PlayerUtils::characterList($user), $params));
    }

    public function characterCheckAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);

        $this->get('session')->set('selectedSlot', $request->query->get('character'));
        $this->get('session')->save();

        return $this->redirect($this->generateUrl('kicks_clubs_clubs'));
    }
}
