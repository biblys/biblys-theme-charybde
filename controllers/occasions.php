<?php

	
	$_PAGE_TITLE = 'Occasions';
	$_REQ = "`stock_purchase_date` < NOW() AND `stock_condition` != 'Neuf'";
	$_REQ_LIMIT = "LIMIT 1000";
	$_REQ_ORDER = "ORDER BY RAND()";

	$_ECHO = '
		<h2>'.$_PAGE_TITLE.'</h2>
		<p>Une porte vers la troisième pièce : découvrez tous nos livres d\'occasion. Chaque exemplaire est photographié individuellement pour vous permettre de mieux apprécier son état.</p>
		<br>
    ';
	
	$path = get_controller_path("_list");
	include($path);
