<?php

/** @noinspection PhpUnhandledExceptionInspection */

use Biblys\Service\Config;
use Biblys\Service\CurrentSite;
use Biblys\Service\CurrentUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$em = new EventManager();

$request = Request::createFromGlobals();
$config = Config::load();
$currentUserService = CurrentUser::buildFromRequestAndConfig($request, $config);
$currentSiteService = CurrentSite::buildFromConfig($config);

$request->attributes->set("page_title", "Prochains évènements");

$content = "<h1>Prochains évènements</h1>";

if ($currentUserService->isAdmin()) {
    $content .= '<div class="admin">Évènements<p><a href="/pages/events_admin">gérer</a></p><p><a href="/pages/event_edit">nouveau</a></p></div>';
}

$events = $em->getAll(['site_id' => $currentSiteService->getId()], ['order' => 'event_start']);

$future = array();
$past = array();

foreach ($events as $e) {
    $event = '
        <article class="event">
            <span class="event-day">'._date($e->get('start'), 'd').'<br>'._date($e->get('start'), 'M').'.</span>
            <p>
                <a href="/evenements/'.$e->get('url').'">'.$e->get('title').'</a> '.(_isVideoCapturedEvent($e["event_desc"]) ? '<i class="fa fa-video-camera orange"></i>' : null ).'<br>
                <span class="post-infos">'._date($e->get('start'), 'L d f Y à H:i').'</span>
            </p>
        </article>';

    if ($e->get('start') > date('Y-m-d H:i:s')) $future[_date($e->get('start'),'F Y')][] = $event;
    else $past[_date($e->get('start'),'F Y')][] = $event;
}

foreach ($future as $month => $events) {
    $content .= '<h3>'.$month.'</h3>'.implode($events);
}

$content .= '<h2>Évènements passés</h2><p><em>Les événements ayant fait l\'objet de captures audio ou vidéo<br /> sont signalés ci-dessous par l\'icône <i class="fa fa-video-camera orange"></i>.</em></p>';

$ev = null;
foreach ($past as $month => $events) {
    $evs = null;
    foreach ($events as $e)
    {
        $evs = $e.$evs;
    }
    $ev = '<h3>'.$month.'</h3>'.$evs.$ev;
}

$content .= $ev;

return new Response($content);

function _isVideoCapturedEvent(?string $eventDescription): bool
{
    return $eventDescription !== null && str_contains($eventDescription, "youtube");
}
