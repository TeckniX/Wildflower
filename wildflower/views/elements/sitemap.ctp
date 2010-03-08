<dl id="sitemap">
	<?php
	foreach($pages as $page):
		if(isset($page['Page']['title'])):
	?>
		<dt><?= $html->link($page['Page']['title'],$this->webroot.$page['Page']['url'], array('escape'=>false)); ?></dt>
		<?php
			if(isset($pages['child'][$page['Page']['id']]) && is_array($pages['child'][$page['Page']['id']]) && !empty($pages['child'][$page['Page']['id']])):
		?>
		<dd>
		<?php
				foreach($pages['child'][$page['Page']['id']] as $childPage):
		?>
				<div class="subpage">
					<p><?= $html->link($childPage['Page']['title'],$this->webroot.$childPage['Page']['url'], array('escape'=>false)) ?></p>
					
				</div>

		<?php
				endforeach;
		?>
		<div class="clear"></div>
		</dd>
		<?php
			endif;
		endif;
	endforeach;
	?>
 </dl>