<?php $tickets = Ticket::all(); ?>

<h1>Tickets</h1>

<?php if ($tickets): ?>

<?php else: ?>
	<p>Keine Tickets vorhanden!</p>

<?php endif; ?>
