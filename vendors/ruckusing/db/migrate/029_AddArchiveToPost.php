<?php

class AddArchiveToPost extends Ruckusing_BaseMigration {

	public function up() {
		$this->execute("ALTER TABLE posts ADD COLUMN archive tinyint(1) DEFAULT 0  AFTER draft ");

	}//up()

	public function down() {
		$this->remove_column('posts', 'archive');
	}//down()
}
?>