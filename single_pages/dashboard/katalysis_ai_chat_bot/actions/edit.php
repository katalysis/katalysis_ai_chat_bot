<?php
/** @noinspection DuplicatedCode */

defined('C5_EXECUTE') or die('Access denied');

use KatalysisAiChatBot\Entity\Action;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Core\Support\Facade\Application;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;


/** @var $entry Action */
/** @var $form Form */
/** @var $token Token */

$app = Application::getFacadeApplication();
/** @var EntityManagerInterface $entityManager */
$entityManager = $app->make(EntityManagerInterface::class);

// Ensure we have default values
$name = isset($name) ? $name : '';
$icon = isset($icon) ? $icon : '';
$triggerInstruction = isset($triggerInstruction) ? $triggerInstruction : '';
$responseInstruction = isset($responseInstruction) ? $responseInstruction : '';
$createdBy = isset($createdBy) ? $createdBy : '';
$createdDate = isset($createdDate) ? $createdDate : '';
$createdByName = isset($createdByName) ? $createdByName : '';
?>



<form action="#" method="post">
    <?php echo $token->output("save_katalysis_actions_entity"); ?>

    <div class="row justify-content-between">
        <div class="col-7">
            <fieldset>
                <div class="form-group">
                    <?php echo $form->label(
                        "name",
                        t("Name"),
                        [
                            "class" => "control-label"
                        ]
                    ); ?>
                    <span class="text-muted small">
                        <?php echo t('Required') ?>
                    </span>
                    <?php echo $form->text(
                        "name",
                        $name,
                        [
                            "class" => "form-control",
                            "required" => "required",
                            "max-length" => "255",
                        ]
                    ); ?>
                </div>
                <div class="form-group">
                    <?php echo $form->label(
                        "triggerInstruction",
                        t("Trigger Instruction"),
                        [
                            "class" => "control-label"
                        ]
                    ); ?>
                    <span class="text-muted small">
                        <?php echo t('Required') ?>
                    </span>
                    <div class="alert alert-info mb-2">
                        <?php echo t('Instructions for the LLM on when to show this action button. Example: "Show this button when the user expresses interest in booking a meeting or getting work done."'); ?>
                    </div>
                    <?php echo $form->textarea(
                        "triggerInstruction",
                        $triggerInstruction,
                        [
                            "class" => "form-control",
                            "required" => "required",
                            "rows" => "3",
                            "placeholder" => t("e.g., Show this button when the user expresses interest in booking a meeting or getting work done.")
                        ]
                    ); ?>
                </div>

                <div class="form-group">
                    <?php echo $form->label(
                        "responseInstruction",
                        t("Response Instruction"),
                        [
                            "class" => "control-label"
                        ]
                    ); ?>
                    <span class="text-muted small">
                        <?php echo t('Required') ?>
                    </span>
                    <div class="alert alert-info mb-2">
                        <?php echo t('Instructions for the LLM on what to do when this action button is pressed. Example: "Ask the user for their preferred meeting time and suggest available slots."'); ?>
                    </div>
                    <?php echo $form->textarea(
                        "responseInstruction",
                        $responseInstruction,
                        [
                            "class" => "form-control",
                            "required" => "required",
                            "rows" => "3",
                            "placeholder" => t("e.g., Ask the user for their preferred meeting time and suggest available slots.")
                        ]
                    ); ?>
                </div>

            </fieldset>

        </div>
        <div class="col-md-3">
            <legend>Settings</legend>


            <script type="text/javascript">
                $(function () {


                    Concrete.Vue.activateContext('cms', function (Vue, config) {
                        new Vue({
                            el: '#ccm-icon-selector-<?= h($bID) ?>',
                            components: config.components
                        })
                    })
                });
            </script>

            <div class="mb-3 ccm-block-select-icon">
                <?php echo $form->label('icon', t('Icon')) ?>
                <div id="ccm-icon-selector-<?= h($bID) ?>">
                    <icon-selector name="icon" selected="<?= h($icon) ?>" title="<?= t('Choose Icon') ?>"
                        empty-option-label="<?= h(tc('Icon', '** None Selected')) ?>" />
                </div>

                <style type="text/css">
                    div.ccm-block-select-icon .input-group-addon {
                        min-width: 70px;
                    }

                    div.ccm-block-select-icon i {
                        font-size: 22px;
                    }

                </style>

            </div>



            </fieldset>
        </div>
    </div>

    <div class="ccm-dashboard-form-actions-wrapper">

        <?php if ($controller->getAction() != 'add' && !empty($createdByName)) { ?>
            <fieldset class="pb-2 text-end" style="padding-right:25px;">
                <small class="form-text text-muted">
                    Created by <a
                        href="/dashboard/users/search/view/<?php echo $createdBy; ?>"><?php echo $createdByName; ?></a>
                    | <?php echo $createdDate; ?>.
                </small>
            </fieldset>
        <?php } ?>

        <div class="ccm-dashboard-form-actions">
            <a href="<?php echo Url::to("/dashboard/katalysis_ai_chat_bot/actions"); ?>" class="btn btn-secondary">
                <i class="fa fa-chevron-left"></i> <?php echo t('Back'); ?>
            </a>

            <div class="float-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save" aria-hidden="true"></i> <?php echo t('Save'); ?>
                </button>
            </div>
        </div>
    </div>
</form>
