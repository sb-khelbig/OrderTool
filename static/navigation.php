<?php $root='/OrderTool/index.php'; ?>

<div id="nav">
	<nav>
		<ul>
			<li> <a href="<?php echo $root; ?>" class="nav_cat">Home</a>
			</li>
		</ul>
		<ul>
			<li> <a href="<?php echo $root . '?p=products'; ?>" class="nav_cat">Produkte</a>
				<ul>
					<li> <a href="/products/" class="nav_sub">Übersicht</a> </li>
					<li> <a href="/products/import/" class="nav_sub"> Importieren </a> </li>
					<li> <a href="/products/search/" class="nav_sub"> Suche </a> </li>
				</ul>
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
			<li> <a href="/messages/" class="nav_cat"> Nachrichten </a>
			</li>
		</ul>
		<ul>
			<li><a href="<?php echo $root . '?p=settings'; ?>" class="nav_cat">Einstellungen</a>
				<ul>
					<li><a href="<?php echo $root . '?p=header'; ?>" class="nav_sub">Kopfzeilen</a></li>
				</ul>
			</li>
		</ul>
		<ul>
			<li> <a href="/help/" class="nav_cat"> Hilfe </a> 
			</li>
		</ul>
	</nav>
</div>