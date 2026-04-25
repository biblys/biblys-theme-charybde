<?php

use Biblys\Service\CurrentSite;
use Biblys\Service\CurrentUser;
use Model\EventQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @throws InvalidDateFormatException
 * @throws PropelException
 */
return function (Request $request, CurrentSite $currentSiteService, CurrentUser $currentUserService): Response
{
    $request->attributes->set("page_title", "Prochains évènements");

    $content = "<h1>Prochains évènements</h1>";

    if ($currentUserService->isAdmin()) {
        $content .= '<div class="admin">Évènements<p><a href="/pages/events_admin">gérer</a></p><p><a href="/pages/event_edit">nouveau</a></p></div>';
    }

    $events = EventQuery::create()
        ->orderByStart()
        ->findByStatus(1)
        ->getData();

    $future = array();
    $past = array();

    /** @var \Model\Event $event */
    foreach ($events as $event) {
        $eventStartDate = $event->getStart();
        $eventHtml = '
            <article class="event">
                <span class="event-day">' . _date($eventStartDate, 'd') . '<br>' . _date($eventStartDate, 'M') . '.</span>
                <p>
                    <a href="/evenements/' . $event->getUrl() . '">' . $event->getTitle() . '</a> ' . (_isVideoCapturedEvent($event->getDesc()) ? '<i class="fa-solid fa-video"></i>' : null) . '<br>
                    <span class="post-infos">' . _date($eventStartDate, 'L d f Y à H:i') . '</span>
                </p>
            </article>
        ';

        if ($eventStartDate > new DateTime()) {
            $future[_date($eventStartDate, 'F Y')][] = $eventHtml;
        } else {
            $past[_date($eventStartDate, 'F Y')][] = $eventHtml;
        }
    }

    foreach ($future as $month => $events) {
        $content .= '<h3>' . $month . '</h3>' . implode($events);
    }

    $content .= '<h2>Évènements passés</h2><p><em>Les événements ayant fait l’objet de captures audio ou vidéo<br /> sont signalés ci-dessous par l’icône <i class="fa-solid fa-video"></i>.</em></p>';

    $ev = null;
    foreach ($past as $month => $events) {
        $evs = null;
        foreach ($events as $e) {
            $evs = $e . $evs;
        }
        $ev = '<h3>' . $month . '</h3>' . $evs . $ev;
    }

    $content .= $ev;

    return new Response($content);
};

function _isVideoCapturedEvent(?string $eventDescription): bool
{
    return $eventDescription !== null && str_contains($eventDescription, "youtube");
}
