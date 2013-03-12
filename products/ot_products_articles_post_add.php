<?php

$article = new Article();
$article->save();

$redirect = $ot->get_link('products', $article->id, 'articles');
header("Location: $redirect");