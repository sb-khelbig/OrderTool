<?php $root='?p=ordertool'; ?>

<div id="nav">
	<nav>
		<ul>
			<li> <a href="/OrderTool/" class="nav_cat">Home</a>
			</li>
		</ul>
		<ul>
			<li> <a href="<?php echo $root . '&ot=orders'; ?>" class="nav_cat">Bestellungen</a> 
				<ul>
					<li><a href="<?php echo $root . '&ot=import'; ?>" class="nav_sub">Importieren</a></li>
					<li><a href="<?php echo '?p=search'; ?>" class="nav_sub">Suchen</a></li>					
				</ul>
			</li>
		</ul>
		<ul>
			<li><a href="<?php echo $root . '&ot=settings'; ?>" class="nav_cat">Einstellungen</a>
				<ul>
					<li><a href="<?php echo $root . '&ot=settings&sub=status'; ?>" class="nav_sub">Status</a></li>
					<li><a href="<?php echo $root . '&ot=settings&sub=attributes'; ?>" class="nav_sub">Attribute</a></li>
					<li><a href="<?php echo $root . '&ot=settings&sub=attributeset'; ?>" class="nav_sub"><span style="font-size: 89%;">Attribut-Sets</span></a></li>
				</ul>
			</li>
		</ul>
		<ul>
			<li> <a href="<?php echo $root . '&ot=ticket'; ?>" class="nav_cat">Tickets</a>
			</li>
		</ul>
		<ul>
			<li> <a href="<?php echo '?p=mails'; ?>" class="nav_cat">eMails</a>
				<ul>
					<li><a href="<?php echo $root . '&ot=mail&sub=accounts'; ?>" class="nav_sub">Accounts</a></li>
					<li><a href="<?php echo $root . '&ot=mail&sub=template'; ?>" class="nav_sub">Templates</a></li>
				</ul>
			</li>
		</ul>
	</nav>
</div>