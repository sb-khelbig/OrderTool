<?php

$offer = new Article();
$offer->save();

$redirect = $ot->get_link('products', $offer->id, 'offers');
header("Location: $redirect");