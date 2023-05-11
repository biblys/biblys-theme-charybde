<?php

/** @noinspection PhpUnhandledExceptionInspection */

global $_SQL;

use Framework\Controller;
use Symfony\Component\HttpFoundation\Request;

$pm = new PostManager();

$_PAGE_TITLE = "Charybde TV";

$filter_sql = null;
$queries = array();
$params = array();
$request = Request::createFromGlobals();
$filter = $request->query->get('filter', false);
if ($filter) {
    $keywords = explode(" ", $filter);
    foreach ($keywords as $keyword) {
        $queries[] = "(`post_title` LIKE :filter OR `people_name` LIKE :filter OR `article_title` LIKE :filter OR `publisher_name` LIKE :filter)";
        $params['filter'] = "%".$keyword."%";
    }
    $filter_sql = " AND ".implode(" AND ", $queries);
}

$posts = $_SQL->prepare('SELECT post_url, post_title, post_date,
        GROUP_CONCAT(`people_name` SEPARATOR ", ") AS people
    FROM `posts` 
    JOIN `links` USING(`post_id`) 
    LEFT JOIN `people` USING(`people_id`)
    LEFT JOIN `articles` USING(`article_id`)
    LEFT JOIN `publishers` ON `links`.`publisher_id` = `publishers`.`publisher_id`
    WHERE `posts`.`category_id` = 54 '.$filter_sql.'
    GROUP BY `post_id`
    ORDER BY `post_date` DESC
');
$posts->execute($params);
$posts = $posts->fetchAll(PDO::FETCH_ASSOC);

$controller = new Controller();
return $controller->render(
    "AppBundle:Post:charybde-tv.html.twig",
    ['posts' => $posts, 'filter' => $filter]
);
