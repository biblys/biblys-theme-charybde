<?php

	
	$_PAGE_TITLE = 'Dernières parutions';
	$_REQ = "`stock_purchase_date` < NOW() AND `stock_condition` = 'Neuf'";
    $_REQ_ORDER = "ORDER BY `article_pubdate` DESC";

	$_ECHO = '
		<h2>'.$_PAGE_TITLE.'</h2>
	';
	
	$path = get_controller_path("_list");
	include($path);
