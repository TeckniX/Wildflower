<?php

class AddCategoryToAssets extends Ruckusing_BaseMigration {

	public function up() {
		 $this->add_column('assets', 'category_id', 'integer', array('default' => 0));
		 $result = $this->select_one("select rght from categories where 1 = 1 order by rght desc limit 1");
		 if($result){
		 	$chain = ($result['rght']+1);
			$query = "INSERT INTO categories SET slug = 'assets', title = 'Assets', lft = ".$chain.", rght = ".($chain+3);
			$this->execute($query);
			$result = $this->select_one("select id from categories where 1 = 1 order by id desc limit 1");
			$lastId = $result['id'];
			$query = "INSERT INTO categories SET parent_id = $lastId, slug = 'image', title = 'Image', lft = ".($chain+1).", rght = ".($chain+2);
			$this->execute($query);
			$query = "INSERT INTO settings SET name = 'category_parent_id', value = $lastId, description = 'The parent category to use for all assets', type = 'select', label = 'Asset Category Parent', order = 4 ";
			$this->execute($query);
		 }
		 
	}//up()

	public function down() {
		 $this->remove_column('assets', 'category_id');
	}//down()
}
?>