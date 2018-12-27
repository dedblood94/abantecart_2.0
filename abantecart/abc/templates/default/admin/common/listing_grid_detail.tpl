<table class="table">
	<?php
		foreach($detail as $key => $value) {
		//if (${'text_'.$key} && $value) {
	?>
	<tr><td><?=$value['title'] ?></td><td><?= $value['value'] ?></td></tr>
	<?php
		//}
	 } ?>
</table>