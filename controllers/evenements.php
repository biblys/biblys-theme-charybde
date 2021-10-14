<?php

    $em = new EventManager();

    $_PAGE_TITLE = 'Prochains évènements';

    $_ECHO .= '<h1>'.$_PAGE_TITLE.'</h1>';

    if ($_V->isAdmin())
    {
        $_ECHO .= '<div class="admin">Évènements<p><a href="/pages/events_admin">gérer</a></p><p><a href="/pages/event_edit">nouveau</a></p></div>';
    }

    $events = $em->getAll(array('site_id' => $_SITE['site_id']), array('order' => 'event_start'));

    $future = array();
    $past = array();

    foreach ($events as $e)
    {
        $event = '
            <article class="event">
                <span class="event-day">'._date($e->get('start'), 'd').'<br>'._date($e->get('start'), 'M').'.</span>
                <p>
                    <a href="/evenements/'.$e->get('url').'">'.$e->get('title').'</a> '.(strstr($e["event_desc"],"youtube") ? '<i class="fa fa-video-camera orange"></i>' : null ).'<br>
                    <span class="post-infos">'._date($e->get('start'), 'L d f Y à H:i').'</span>
                </p>
            </article>';

        if ($e->get('start') > date('Y-m-d H:i:s')) $future[_date($e->get('start'),'F Y')][] = $event;
        else $past[_date($e->get('start'),'F Y')][] = $event;
    }

    foreach ($future as $month => $events)
    {
        $_ECHO .= '<h3>'.$month.'</h3>'.implode($events);
    }

    $_ECHO .= '<h2>Évènements passés</h2><p><em>Les événements ayant fait l\'objet de captures audio ou vidéo<br /> sont signalés ci-dessous par l\'icône <i class="fa fa-video-camera orange"></i>.</em></p>';

    $ev = null;
    foreach ($past as $month => $events)
    {
        $evs = null;
        foreach ($events as $e)
        {
            $evs = $e.$evs;
        }
        $ev = '<h3>'.$month.'</h3>'.$evs.$ev;
    }

    $_ECHO .= $ev;
