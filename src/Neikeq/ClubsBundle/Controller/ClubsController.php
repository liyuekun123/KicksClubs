<?php

namespace Neikeq\ClubsBundle\Controller;

use Neikeq\ClubsBundle\DependencyInjection\ClubUtils;
use Neikeq\ClubsBundle\DependencyInjection\PlayerUtils;
use Neikeq\ClubsBundle\DependencyInjection\PlayerRoles;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $playerInfo = $playerId == null ? array() : PlayerUtils::getCharacterInfoById($playerId);
        $playerInfo['role'] = PlayerUtils::getPlayerRole($playerId);

        // params for the twig template
        $params = array('player' => $playerInfo, 'clubs' => $clubs,
            'pages' => ceil($clubsCount / ClubUtils::clubsPerPage));

        return $this->render('NeikeqClubsBundle:Default:clubs.html.twig', $params);
    }

    public function characterAction()
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);

        $user = $this->getUser();

        $playerId = PlayerUtils::getSelectedPlayer($this->get('session'), $user);

        $playerInfo = PlayerUtils::getCharacterInfoById($playerId);
        $playerInfo['role'] = PlayerUtils::getPlayerRole($playerId);

        return $this->render('NeikeqClubsBundle:Default:character.html.twig',
            array_merge(PlayerUtils::characterList($user), array('player' => $playerInfo)));
    }

    public function characterCheckAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);

        $this->get('session')->set('selectedSlot', $request->request->get('character'));
        $this->get('session')->save();

        return $this->redirect($this->generateUrl('kicks_clubs_clubs'));
    }

    public function clubAction()
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null);

        $playerId = PlayerUtils::getSelectedPlayer($this->get('session'), $this->getUser());

        if (PlayerUtils::mustSelectCharacter($playerId)) {
            return $this->redirect($this->generateUrl('kicks_clubs_character'));
        }

        $em = $this->get('doctrine.orm.entity_manager');

        $clubMember = $em->getRepository('NeikeqClubsBundle:ClubMembers')->find($playerId);

        $clubInfo = ClubUtils::clubView($clubMember->getClubId(), $em);

        // if user is not authenticated, set username to empty
        $playerInfo = $playerId == null ? array() : PlayerUtils::getCharacterInfoById($playerId);
        $playerInfo['role'] = PlayerUtils::getPlayerRole($playerId);

        // params for the twig template
        $params = array('player' => $playerInfo, 'club' => $clubInfo);

        return $this->render('NeikeqClubsBundle:Default:club.html.twig', $params);
    }

    public function ajaxClubInfoAction(Request $request)
    {
        if ($request->isXMLHttpRequest()) {
            $em = $this->get('doctrine.orm.entity_manager');

            return new JsonResponse(ClubUtils::clubView($request->request->get('club_id'), $em));
        }

        return new Response('This is not a valid ajax request.', 400);
    }

    public function joinAction()
    {

    }
}
