<?php
defined('C5_EXECUTE') or die("Access Denied.");

$form = \Core::make('helper/form');
?>

<div class="form-group">
    <?php echo $form->label('title', t('Chatbot Title')); ?>
    <?php echo $form->text('title', $title ?? '', ['class' => 'form-control', 'placeholder' => t('e.g., AI Assistant, Chat with us')]); ?>
    <small class="form-text text-muted"><?php echo t('Optional title to display above the chatbot'); ?></small>
</div>

<div class="form-group">
    <div class="form-check">
        <?php echo $form->checkbox('showTitle', 1, $showTitle ?? true, ['class' => 'form-check-input']); ?>
        <?php echo $form->label('showTitle', t('Show Title'), ['class' => 'form-check-label']); ?>
    </div>
    <small class="form-text text-muted"><?php echo t('Display the chatbot title above the chat interface'); ?></small>
</div>

<div class="form-group">
    <?php echo $form->label('chatbotPosition', t('Chatbot Position')); ?>
    <?php echo $form->select('chatbotPosition', [
        'bottom-right' => t('Bottom Right'),
        'bottom-left' => t('Bottom Left'),
        'top-right' => t('Top Right'),
        'top-left' => t('Top Left'),
        'center' => t('Center')
    ], $chatbotPosition ?? 'bottom-right', ['class' => 'form-control']); ?>
    <small class="form-text text-muted"><?php echo t('Position of the chatbot on the page'); ?></small>
</div>

<div class="form-group">
    <?php echo $form->label('theme', t('Theme')); ?>
    <?php echo $form->select('theme', [
        'light' => t('Light'),
        'dark' => t('Dark'),
        'auto' => t('Auto (follows site theme)')
    ], $theme ?? 'light', ['class' => 'form-control']); ?>
    <small class="form-text text-muted"><?php echo t('Visual theme for the chatbot interface'); ?></small>
</div>

<div class="alert alert-info">
    <i class="fa fa-info-circle"></i>
    <strong><?php echo t('Note:'); ?></strong>
    <?php echo t('The welcome message will be automatically generated using your AI settings from the Chat Bot Settings page.'); ?>
</div> 