<?php
$url = preg_replace("/\/(filter|page)\:[0-9]+/","",$this->params['url']['url']);
?>
<select id="catFilter">
	<option>-- Filter by --</option>
	<option value="<?=$this->webroot.$url?>">All</option>
<?php
   foreach($categories as $catIndex=>$category):
?>
	<option value="<?=$this->webroot.$url?>/filter:<?=$catIndex?>"><?= ucfirst($category)?></option>
<?php
   endforeach;
?>
</select>
<span class="cleaner"></span>
