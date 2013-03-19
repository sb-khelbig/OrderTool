<?php $ticket_category = TicketCategory::get($_GET['id']); ?>

<h1><?php echo $ticket_category->name; ?></h1>
