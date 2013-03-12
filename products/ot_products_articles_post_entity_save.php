<?php 

$article = Article::get($_GET['id']);

$product = isset($_POST['product']) ? $_POST['product'] : $article->product;
$supplier = isset($_POST['supplier']) ? $_POST['supplier'] : $article->supplier;

$new_data_source = isset($_POST['new_data_source']) ? $_POST['new_data_source'] : array();
$new_ahds = isset($_POST['new_ahds']) ? $_POST['new_ahds'] : array();

if (($article->product !== $product) || ($article->supplier !== $supplier)) {
	$article->product = $product;
	$article->supplier = $supplier;
	$article->update();
}

if (count($new_data_source) == count($new_ahds)) {
	$ahds = array();
	foreach ($new_data_source as $index => $id) {
		$new = new ArticleHasDataSource();
		$new->article = $article;
		$new->data_source = $id;
		$new->external_id = $new_ahds[$index];
		$ahds[] = $new;
	}
	if ($ahds) {
		ArticleHasDataSource::bulk_save($ahds);
	}
}

$redirect = $ot->get_link('products', $article->id, 'articles');
header("Location: $redirect");