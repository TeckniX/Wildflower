<?php 
    if ($session->check('Message.flash')) {
        $session->flash();
    }

	$partialLayout->setLayoutVar('form_for_layout', $form->create('Post', array('action' => 'update', 'class' => 'editor_form')));

    echo 
    $form->input('title', array(
        'label' => __('Post title', true)
    )),
    $form->input('content', array(
        'type' => 'textarea',
        'class' => 'tinymce fill_screen',
        'rows' => '25',
        'label' => __('Body', true),
        'div' => array('class' => 'input editor')
    )),
    '<div>',
    $form->hidden('id'),
    $form->hidden('draft'),
    $form->hidden('archive'),
    $form->hidden('Category.Category'),
    '</div>';
?>
    
<div id="edit-buttons">
    <?php echo $this->element('admin_edit_buttons'); ?>
</div>

<?php $partialLayout->blockStart('sidebar'); ?>
    <li class="main_sidebar">
        <?php echo $html->link(
            '<span>Write a new post</span>', 
            array('action' => 'admin_create'),
            array('class' => 'add', 'escape' => false)); ?>
    </li>
    <li class="main_sidebar category_sidebar">
		<h4 class="sidebar_heading"><?php __('Categories'); ?></h4>
		<?php echo $tree->generate($categoriesForTree, array('model' => 'Category', 'class' => '', 'element' => '../categories/list_item', 'inCategories' => $inCategories)); ?>
    </li>
    <li class="main_sidebar">
        <ul class="sidebar-menu-alt edit-sections-menu">
            <li><?php echo $html->link('Options <small>like status, publish date, etc.</small>', array('action' => 'options', $this->data['Post']['id']), array('escape' => false)); ?></li>
            <li><?php echo $html->link('Browse older versions', '#Revisions', array('rel' => 'post-revisions')); ?></li>
            <li><?php echo $html->link("Comments ({$this->data['Post']['comment_count']})", array('action' => 'comments', $this->data['Post']['id'])); ?></li>
        </ul>
    </li>
<?php $partialLayout->blockEnd(); ?>
