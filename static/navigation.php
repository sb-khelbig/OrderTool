<?php $root='/OrderTool/index.php'; ?>

<div id="nav">
	<nav>
		<ul>
			<li> <a href="<?php echo $root; ?>" class="nav_cat">Home</a>
			</li>
		</ul>
		<ul>
			<li> <a href="<?php echo $root . '?p=orderlists'; ?>" class="nav_cat">Bestellungen</a> 
				<ul>
					<li><a href="<?php echo $root . '?p=import'; ?>" class="nav_sub">Importieren</a></li>					
				</ul>
			</li>
		</ul>
		<ul>
			<li><a href="<?php echo $root . '?p=settings'; ?>" class="nav_cat">Einstellungen</a>
				<ul>
					<li><a href="<?php echo $root . '?p=header'; ?>" class="nav_sub">Header</a></li>
					<li><a href="<?php echo $root . '?p=status'; ?>" class="nav_sub">Status</a></li>
				</ul>
			</li>
		</ul>
	</nav>
</div>