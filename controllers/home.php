<?php /** @noinspection PhpUnhandledExceptionInspection */

global $_SQL;

use Biblys\Service\Config;
use Biblys\Service\CurrentSite;
use Symfony\Component\HttpFoundation\Response;

$em = new EventManager();

$blog = [];

/* HEADLINE POST */

$query = "SELECT `post_id`, `post_title`, `post_url`, `post_content`, `post_date`, `post_update`,
        `category_name`, `category_url`,
        `user_screen_name`, `user_slug`
    FROM `posts`
    LEFT JOIN `categories` USING(`category_id`)
    LEFT JOIN `users` ON `user_id` = `users`.`id`
    WHERE `posts`.`site_id` = :site_id AND `post_date` <= NOW() AND `post_status` = 1 AND `post_selected` = 1 AND `category_id` != 3 AND `category_id` != 45
    ORDER BY `post_date` DESC LIMIT 3";

$config = Config::load();
$currentSite = CurrentSite::buildFromConfig($config);

$posts = $_SQL->prepare($query);
$posts->execute(['site_id' => $currentSite->getId()]);
$posts = $posts->fetchAll(PDO::FETCH_ASSOC);

foreach ($posts as $p) {
    $post = new Post($p);

    $blog[] = '<article class="right">' .
        '<p>' .
        '<a href="/blog/' . $post->get('url') . '">' . $post->get('title') . '</a><br>' .
        '<time>' . _date($post->get('date'), 'd f Y') . '</time>' .
        '</p>' .
        '</article>';
}

/* NEXT EVENTS */

$calendar = [];
$events = $em->getAll(array('site_id' => $currentSite->getId(), 'event_start' => '> ' . date('Y-m-d H:i:s')), array('order' => 'event_start', 'limit' => 3), false);
$ie = 0;
foreach ($events as $e) {
    $event = '
        <article class="event">
            <h1><a href="/evenements/' . $e->get('url') . '">' . $e->get('title') . '</a></h1>
            <p class="event-infos"><strong>Rendez-vous le ' . _date($e->get('start'), 'd/m/Y à H:i') . '</strong></p>
        </article>
    ';

    if ($ie > 3) break;
    if ($e->get('start') > date('Y-m-d H:i:s')) {
        $calendar[] = $event;
        $ie++;
    }
}

/* COUPS DE CŒUR */

// Derniers coups de cœur
$posts = $_SQL->query("SELECT `post_url`, `post_content`, `article_id`, `article_url`, `article_title`, `article_authors`, `article_publisher`
    FROM `articles`
    JOIN `links` USING(`article_id`)
    JOIN `posts` USING(`post_id`)
    WHERE `post_date` < NOW() AND `post_status` = 1 AND `posts`.`category_id` = 3
    GROUP BY `article_id`, `post_url`, `post_content`, `post_date`, `article_url`, `article_title`, `article_authors`, `article_publisher`
    ORDER BY `post_date` DESC
LIMIT 4");
$selection = array();
$ip = 0;
while ($p = $posts->fetch(PDO::FETCH_ASSOC)) {
    $size = 60;
    $text = null;
    if ($ip == 0) {
        $size = 180;
        $text = '<p>' . truncate(strip_tags($p['post_content']), 300, '...', true) . '<br><a href="/blog/' . $p['post_url'] . '" class="btn btn-default btn-sm">Lire la suite <i class="fa fa-chevron-right"></i></a></p>';
    }

    $media = new Media('article', $p['article_id']);
    if ($media->exists())
        $selection[] = '
        <a href="/blog/' . $p["post_url"] . '" class="va-text-bottom">
            <img width="' . $size . '" src="' . $media->getUrl(["size" => "w".$size]) . '" alt="' . $p["article_title"] . '" title="' . $p["article_title"] . ' de ' . $p["article_authors"] . ' (' . $p["article_publisher"] . ')">
        </a>' . $text;
    $ip++;
}

/* LAST RELEASES */

$articles = $_SQL->query("SELECT `article_id`, `article_title`, `article_authors`, `article_publisher`, `article_url`
    FROM `articles`
    JOIN `stock` USING(`article_id`)
    WHERE `article_pubdate` < NOW() AND `article_availability` = 1 AND `stock`.`site_id` = 5
    GROUP BY `article_id`
    ORDER BY `article_pubdate` DESC
LIMIT 15");
$recents = array();
$ir = 0;
while ($a = $articles->fetch(PDO::FETCH_ASSOC)) {

    $media = new Media('article', $a['article_id']);
    if ($media->exists()) {
        $recents[] = '
        <a href="/' . $a["article_url"] . '" class="va-text-bottom">
            <img width="60" src="' . $media->getUrl(["size" => "w60"]) . '" alt="' . $a["article_title"] . '" title="' . $a["article_title"] . ' de ' . $a["article_authors"] . ' (' . $a["article_publisher"] . ')"> </a>';
        $ir++;
    }
    if ($ir >= 9) break;
}


$content = '

    <div class="row">

        <div class="col-sm-8">
            <h1>Bientôt chez Charybde</h1>

            <div class="event-list">
                ' . implode($calendar) . '
            </div>
        </div>

        <div class="col-sm-4">
            <h2 class="right">L\'actu de la librairie</h2>
            ' . implode($blog) . '

            <h2 class="right">Nos derniers<br>coups de coeur</h2>
            <div class="right">' . implode($selection) . '</div>

            <h2 class="right">Parutions récentes</h2>
            <div class="right">' . implode($recents) . '</div>
        </div>

    </div>

    <div class="row">

    </div>
';

return new Response($content);
