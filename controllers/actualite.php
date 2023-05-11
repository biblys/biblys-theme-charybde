<?php

$_PAGE_TITLE = 'L\'actualité de la librairie';

$sql = $_SQL->prepare('SELECT *, GROUP_CONCAT(`links`.`article_id`) AS `articles` FROM `posts` '
        . 'LEFT JOIN `users` ON `posts`.`user_id` = `users`.`id`'
        . 'LEFT JOIN `categories` USING(`category_id`)'
        . 'LEFT JOIN `links` USING(`post_id`)'
        . 'WHERE `posts`.`site_id` = :site_id AND `post_status` = 1 AND `category_id` != 3 AND `category_id` != 45 AND `post_date` <= NOW()'
        . 'GROUP BY `post_id`'
        . 'ORDER BY `post_date` DESC LIMIT 10');
$sql->execute(array('site_id' => $_SITE['site_id']));
$posts = $sql->fetchAll();
$sql->closeCursor();

$the_posts = array();
foreach($posts as $post)
{
    $p = new Post($post);

    $category = null; $author = null;

    if ($p->has('category_name')) $category = ' &nbsp; <i class="fa fa-folder"></i> <a href="/blog/'.$p->get('category_url').'/">'.$p->get('category_name').'</a>';
    if ($p->has('user_screen_name')) $author = ' &nbsp; <i class="fa fa-user"></i> <a href="/blog/author/'.$p->get('user_slug').'">'.$p->get('user_screen_name').'</a>';

    // Linked article
    $the_articles = null;
    if ($p->has('articles'))
    {
        $am = new ArticleManager();
        $articles = $am->getByIds(explode(',', $p->get('articles')));
        foreach ($articles as $a)
        {
            $cover = $a->getCover();
            $the_articles .= '<article><a href="/'.$a->get('url').'" title="'.$a->get('title').' de '.$a->get('authors').'"><img src="'.$cover->url('h150').'" alt="'.$a->get('title').'" height=150></a></article>';
        }
    }

    $the_posts[] = '
        <article class="post">
            <h1><a href="/blog/'.$p->get('url').'">'.$p->get('title').'</a></h1>
            <p class="post-infos">
                <i class="fa fa-clock-o"></i> '._date($p->get('date'), 'L j f Y').'
                '.$author.'
                '.$category.'
            </p>
            '.$p->get('content').'
            <div class="linkedArticles">'.$the_articles.'</div>
        </article>
    ';
}

$_ECHO .= '<h1>'.$_PAGE_TITLE.'</h1>

    '.implode($the_posts).'

';
