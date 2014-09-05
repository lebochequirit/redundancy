<div class="col-lg-2 col-md-2 visible-md visible-lg">
<div data-spy="affix" data-offset-top="140" class="affix-top">
	<div class="dropdown">
		<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			<span class = "elusive icon-user glyphIcon"></span><?php echo $_SESSION["user_name"];?> <span class="caret"></span>
		</button>
		<ul class="dropdown-menu" role="menu">
			<li>
				<a href="?module=account"><?php echo $GLOBALS["Program_Language"]["My_Account"];?></a>
			</li>	
			<li>
				<a href="?module=storageinfo">
					<?php echo $GLOBALS["Program_Language"]["Account_Storage_Info"];?>
				</a>
			</li>		
			<?php if ($_SESSION["role"] == 0 && isAdmin()): ?>
				<li>
				<a href="?module=admin"><?php echo $GLOBALS["Program_Language"]["Administration"];?></a>
			</li>
			<?php endif;?>		
			<li>
				<a href="index.php?module=info">Info</a>
			</li>
			<li class="divider"></li>
			<li>
				<a href="?module=logout"><?php echo $GLOBALS["Program_Language"]["Exit"];?></a>
			</li>
		</ul>
	</div>
</div>
</div>