<?php

	$_PAGE_TITLE = 'Exportation du stock';
	
	if (isset($_GET['collection_id']))
	{
		$collection = $_SQL->prepare('SELECT `collection_id`, `collection_name`, `collection_url` FROM `collections` WHERE `collection_id` = :collection_id LIMIT 1');
		$collection->bindValue('collection_id',$_GET['collection_id'],PDO::PARAM_INT);
		$collection->execute() or error_get_last($articles->errorInfo());
		if ($c = $collection->fetch(PDO::FETCH_ASSOC))
		{
			$collection_h3 = '<h3>'.$c['collection_name'].'</h3>';
		}
		
		$articles = $_SQL->prepare('SELECT `article_authors`, `article_title`, COUNT(`stock_id`) AS `article_stock` FROM `articles`
			JOIN `stock` USING(`article_id`)
			WHERE `collection_id` = :collection_id AND `site_id` = :site_id AND `stock_selling_date` IS NULL AND `stock_return_date` IS NULL AND `stock_lost_date` IS NULL
			GROUP BY `article_id`
			ORDER BY `article_authors_alphabetic`, `article_title_alphabetic`');
		$articles->bindValue('collection_id',$_GET['collection_id'],PDO::PARAM_INT);
		$articles->bindValue('site_id',$_SITE['site_id'],PDO::PARAM_INT);
		$articles->execute() or error($articles->errorInfo());
		
		$export = array();
		
		while ($a = $articles->fetch(PDO::FETCH_ASSOC))
		{
			$table .= '
				<tr>
					<td>'.authors($a['article_authors']).'</td>
					<td>'.$a['article_title'].'</td>
					<td class="right">'.$a['article_stock'].'</td>
				</tr>
			';
			
			$export[] = $a;
		}
	}
	
	$_ECHO .= '
		<h2>'.$_PAGE_TITLE.'</h2>
		
		<form>
			<fieldset>
				<label>Collection n&deg;&nbsp;:</label>
				<input name="collection_id" type="num" min=1 max=10000 value="'.$_GET['collection_id'].'">
				<button type="submit">Afficher</button>
			</fieldset>
		</form>
		
		'.$collection_h3.'
		<br />
		
		<form action="/pages/export_to_csv" method="post">
			<fieldset class="center">
				<input type="hidden" name="filename" value="'.$c['collection_url'].'">
				<input type="hidden" name="data" value="'.htmlentities(json_encode($export)).'">
				<button type="submit">Télécharger au format Excel</button>
			</fieldset>
		</form>
		<br />
		
		<table class="admin-table">
			<thead>
				<tr>
					<th>Auteur</th>
					<th>Titre</th>
					<th>Stock</th>
				</tr>
			</thead>
			<tbody>
				'.$table.'
			</tbody>
		</table>
		
	';
	
?>