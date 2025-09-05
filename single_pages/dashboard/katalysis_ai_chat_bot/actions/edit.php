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
$actionType = isset($actionType) ? $actionType : 'basic';
$formSteps = isset($formSteps) ? $formSteps : '';
$formConfig = isset($formConfig) ? $formConfig : '';
$createdBy = isset($createdBy) ? $createdBy : '';
$createdDate = isset($createdDate) ? $createdDate : '';
$createdByName = isset($createdByName) ? $createdByName : '';
?>



<form action="#" method="post">
    <?php echo $token->output("save_katalysis_actions_entity"); ?>

    <div class="row justify-content-between mt-4">
        <div class="col-7">
            <div class="row">
                <fieldset>
                    <legend><?php echo t('Action Details'); ?></legend>
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
                            "actionType",
                            t("Action Type"),
                            [
                                "class" => "control-label"
                            ]
                        ); ?>
                        <span class="text-muted small">
                            <?php echo t('Required') ?>
                        </span>
                        <?php echo $form->select(
                            "actionType",
                            [
                                'basic' => t('Basic Button - AI follows response instruction'),
                                'simple_form' => t(text: 'Form - All Fields at Once'),
                                'form' => t('Form - Static Steps'),
                                'dynamic_form' => t('Form - AI Controlled Steps')
                            ],
                            $actionType,
                            [
                                "class" => "form-control",
                                "id" => "actionType"
                            ]
                        ); ?>

                    </div>
                </fieldset>
                <div class="col-6">
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

                        <?php echo $form->textarea(
                            "triggerInstruction",
                            $triggerInstruction,
                            [
                                "class" => "form-control mb-3",
                                "required" => "required",
                                "rows" => "3",
                                "placeholder" => t("e.g., Show this button when the user expresses interest in booking a meeting or getting work done.")
                            ]
                        ); ?>
                        <div class="alert alert-info mb-2">
                            <?php echo t('Tell the LLM when to show this action button. Example: <em>"Show this button when the user expresses interest in booking a meeting or getting work done."</em>'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-6">
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

                        <?php echo $form->textarea(
                            "responseInstruction",
                            $responseInstruction,
                            [
                                "class" => "form-control mb-3",
                                "required" => "required",
                                "rows" => "3",
                                "placeholder" => t("e.g., Ask the user for their preferred meeting time and suggest available slots.")
                            ]
                        ); ?>
                        <div class="alert alert-info mb-2">
                            <?php echo t('Tell the LLM what to do when this action button is pressed. Example: <em>"Ask the user for their preferred meeting time and suggest available slots."</em>'); ?>
                        </div>
                    </div>
                </div>
            </div>

            </fieldset>

        </div>
        <div class="col-md-3">
            <fieldset>
                <legend>Button Settings</legend>

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

    <!-- Form Builder Section (shown only for form types) -->
    <div id="form-builder-section" class="row justify-content-between mt-4" style="display: none;">
        <div class="col-7">
            <fieldset>
                <legend><?php echo t('Form Builder'); ?></legend>

                <!-- Hidden textarea for form submission - managed by visual editor -->
                <?php echo $form->textarea(
                    "formSteps",
                    $formSteps,
                    [
                        "class" => "form-control d-none",
                        "readonly" => true
                    ]
                ); ?>
                <!-- Form Builder UI -->
                <div id="form-builder-visual">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addFormStep('text')">
                                <i class="fas fa-plus"></i> <?php echo t('Add Text Field'); ?>
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addFormStep('email')">
                                <i class="fas fa-plus"></i> <?php echo t('Add Email Field'); ?>
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="addFormStep('select')">
                                <i class="fas fa-plus"></i> <?php echo t('Add Select Field'); ?>
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="addFormStep('textarea')">
                                <i class="fas fa-plus"></i> <?php echo t('Add Textarea'); ?>
                            </button>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="loadSampleForm('contact')">
                                <?php echo t('Load Contact Form'); ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="loadSampleForm('lead_qualification')">
                                <?php echo t('Load Lead Qualification'); ?>
                            </button>
                        </div>
                    </div>

                    <div id="form-steps-container">
                        <!-- Form steps will be rendered here -->
                    </div>
                </div>
            </fieldset>
        </div>

        <div class="col-md-3">
            <fieldset>
                <legend><?php echo t('Form Settings'); ?></legend>
                <div class="form-group">
                    <div class="form-check">
                        <?php echo $form->checkbox(
                            "showImmediately",
                            1,
                            $showImmediately ?? false,
                            [
                                "class" => "form-check-input"
                            ]
                        ); ?>
                        <?php echo $form->label(
                            "showImmediately",
                            t("Show Form Immediately"),
                            [
                                "class" => "form-check-label"
                            ]
                        ); ?>
                    </div>
                    <div class="alert alert-info mb-2 mt-2">
                        <?php echo t('When this is the highest priority action display the form immediately instead of showing the Further Information list.'); ?>
                    </div>
                </div>
            </fieldset>
        </div>
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

<script>
    $(document).ready(function () {
        // Show/hide form builder based on action type
        function toggleFormBuilder() {
            const actionType = $('#actionType').val();
            if (actionType === 'form' || actionType === 'dynamic_form' || actionType === 'simple_form') {
                $('#form-builder-section').show();
                loadFormStepsFromJson();
            } else {
                $('#form-builder-section').hide();
            }
        }

        // Initial state
        toggleFormBuilder();

        // Listen for action type changes
        $('#actionType').on('change', toggleFormBuilder);

        // Global form steps array
        window.formSteps = [];

        // Load form steps from JSON textarea
        function loadFormStepsFromJson() {
            try {
                const jsonText = $('textarea[name="formSteps"]').val();
                if (jsonText.trim()) {
                    const parsedSteps = JSON.parse(jsonText);

                    // Validate that parsed data is an array
                    if (Array.isArray(parsedSteps)) {
                        // Filter out null/undefined elements and validate structure
                        window.formSteps = parsedSteps.filter(step => step && typeof step === 'object');
                    } else {
                        console.warn('Invalid JSON structure - expected array');
                        window.formSteps = [];
                    }

                    renderFormSteps();
                }
            } catch (e) {
                console.warn('Invalid JSON in form steps:', e);
                window.formSteps = [];
                renderFormSteps(); // Still render to show empty state
            }
        }

        // Save form steps to JSON textarea
        function saveFormStepsToJson() {
            $('textarea[name="formSteps"]').val(JSON.stringify(window.formSteps, null, 2));
        }

        // Render visual form steps
        function renderFormSteps() {
            const container = $('#form-steps-container');
            container.empty();

            // Add validation for formSteps array
            if (!window.formSteps || !Array.isArray(window.formSteps)) {
                console.warn('Invalid formSteps data');
                return;
            }

            window.formSteps.forEach((step, index) => {
                // Skip if step is undefined or null
                if (!step) {
                    console.warn(`Skipping undefined step at index ${index}`);
                    return;
                }

                // Provide default values for missing properties
                const stepKey = step.stepKey || `field_${index + 1}`;
                const fieldType = step.fieldType || 'text';
                const question = step.question || '(No question set)';
                const stepHtml = `
                <div class="card mb-3" data-step-index="${index}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-grip-vertical me-2"></i>
                            Step ${index + 1}: ${fieldType.toUpperCase()} - ${stepKey}
                        </h6>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editFormStep(${index})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFormStep(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Question:</strong> ${question}
                            </div>
                            <div class="col-md-6">
                                <strong>Required:</strong> ${step.validation?.required ? 'Yes' : 'No'}
                            </div>
                        </div>
                        ${step.options ? `<div class="mt-2"><strong>Options:</strong> ${step.options.join(', ')}</div>` : ''}
                        ${step.conditionalLogic ? `<div class="mt-2"><strong>Conditional Logic:</strong> ${JSON.stringify(step.conditionalLogic)}</div>` : ''}
                    </div>
                </div>
            `;
                container.append(stepHtml);
            });
        }

        // Add new form step
        window.addFormStep = function (fieldType) {
            const newStep = {
                stepKey: `field_${window.formSteps.length + 1}`,
                fieldType: fieldType,
                question: `What's your ${fieldType}?`,
                sortOrder: window.formSteps.length + 1,
                validation: { required: true }
            };

            if (fieldType === 'select') {
                newStep.options = ['Option 1', 'Option 2', 'Option 3'];
            }

            window.formSteps.push(newStep);
            saveFormStepsToJson();
            renderFormSteps();
        };

        // Remove form step
        window.removeFormStep = function (index) {
            if (confirm('Are you sure you want to remove this step?')) {
                window.formSteps.splice(index, 1);
                // Update sort orders
                window.formSteps.forEach((step, idx) => {
                    step.sortOrder = idx + 1;
                });
                saveFormStepsToJson();
                renderFormSteps();
            }
        };

        // Edit form step (simple prompt-based editing)
        window.editFormStep = function (index) {
            console.log('editFormStep called with index:', index);
            console.log('window.formSteps:', window.formSteps);
            console.log('formSteps length:', window.formSteps ? window.formSteps.length : 'undefined');
            console.log('index >= 0:', index >= 0);
            console.log('index < length:', window.formSteps ? index < window.formSteps.length : 'N/A');
            console.log('step exists:', window.formSteps ? !!window.formSteps[index] : 'N/A');

            if (!window.formSteps || index < 0 || index >= window.formSteps.length || !window.formSteps[index]) {
                console.error('Invalid step index or step not found:', index);
                console.error('Validation failed - window.formSteps:', !!window.formSteps);
                console.error('Validation failed - index >= 0:', index >= 0);
                console.error('Validation failed - index < length:', window.formSteps ? index < window.formSteps.length : 'N/A');
                console.error('Validation failed - step exists:', window.formSteps ? !!window.formSteps[index] : 'N/A');
                return;
            }

            const step = window.formSteps[index];
            console.log('Step found:', step);

            // Ensure step has required properties
            if (!step.stepKey) {
                step.stepKey = `field_${index + 1}`;
            }
            if (!step.fieldType) {
                step.fieldType = 'text';
            }
            if (!step.question) {
                step.question = 'Enter your response';
            }

            console.log('Step after validation:', step);

            // Hide all other edit forms first
            $('.form-step-edit-form').remove();
            console.log('Removed existing edit forms');

            // Store original card body content
            const targetElement = $(`.card[data-step-index="${index}"]`);
            const originalCardBody = targetElement.find('.card-body').html();
            targetElement.data('original-body', originalCardBody);
            
            // Auto-generate field key if empty
            let fieldKey = step.stepKey;
            if (!fieldKey) {
                fieldKey = `field_${Date.now()}_${index}`;
                step.stepKey = fieldKey;
            }

            // Create inline edit form content
            const editFormContent = `
                <div class="form-group mb-3">
                    <label for="edit-fieldType-${index}" class="form-label">Field Type</label>
                    <select class="form-control" id="edit-fieldType-${index}">
                        <option value="text" ${step.fieldType === 'text' ? 'selected' : ''}>Text</option>
                        <option value="email" ${step.fieldType === 'email' ? 'selected' : ''}>Email</option>
                        <option value="textarea" ${step.fieldType === 'textarea' ? 'selected' : ''}>Textarea</option>
                        <option value="select" ${step.fieldType === 'select' ? 'selected' : ''}>Select</option>
                    </select>
                </div>
                
                <div class="form-group mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="edit-required-${index}" ${step.validation?.required ? 'checked' : ''} />
                        <label class="form-check-label" for="edit-required-${index}">Required Field</label>
                    </div>
                </div>
            
                <div class="form-group mb-3">
                    <label for="edit-question-${index}" class="form-label">Question</label>
                    <input type="text" class="form-control" id="edit-question-${index}" value="${step.question || ''}" />
                    <small class="form-text text-muted">The question to ask the user</small>
                </div>
                
                <div class="form-group mb-3" id="options-group-${index}" style="${step.fieldType === 'select' ? '' : 'display: none;'}">
                    <label for="edit-options-${index}" class="form-label">Options</label>
                    <textarea class="form-control" id="edit-options-${index}" rows="3" placeholder="Option 1&#10;Option 2&#10;Option 3">${step.options ? step.options.join('\n') : ''}</textarea>
                    <small class="form-text text-muted">One option per line</small>
                </div>
                    
                
                <div class="form-group">
                    <button type="button" class="btn btn-primary" onclick="saveFormStepEdit(${index})">Save Changes</button>
                    <button type="button" class="btn btn-secondary ms-2" onclick="cancelFormStepEdit(${index})">Cancel</button>
                </div>
            `;

            // Replace card body content with edit form
            targetElement.find('.card-body').html(editFormContent);
            targetElement.addClass('form-step-edit-form');
            console.log('Card body replaced with edit form');

            // Verify edit form was added
            const insertedForm = $('.form-step-edit-form');
            console.log('Edit form elements after insertion:', insertedForm.length);

            // Handle field type change to show/hide options
            $(`#edit-fieldType-${index}`).on('change', function () {
                const selectedType = $(this).val();
                if (selectedType === 'select') {
                    $(`#options-group-${index}`).show();
                } else {
                    $(`#options-group-${index}`).hide();
                }
            });
        };

        // Save form step edit
        window.saveFormStepEdit = function (index) {
            const step = window.formSteps[index];

            // Get values from form
            const newQuestion = $(`#edit-question-${index}`).val().trim();
            const newFieldType = $(`#edit-fieldType-${index}`).val();
            const isRequired = $(`#edit-required-${index}`).is(':checked');

            // Validate required fields (field key is auto-generated)
            if (!newQuestion) {
                alert('Question is required');
                $(`#edit-question-${index}`).focus();
                return;
            }

            // Update step (field key remains auto-generated)
            step.question = newQuestion;
            step.fieldType = newFieldType;
            step.validation = { required: isRequired };

            // Handle options for select fields
            if (newFieldType === 'select') {
                const optionsText = $(`#edit-options-${index}`).val().trim();
                if (optionsText) {
                    step.options = optionsText.split('\n').map(opt => opt.trim()).filter(opt => opt);
                } else {
                    step.options = [];
                }
            } else {
                delete step.options;
            }

            // Restore original card body and update display
            const targetElement = $(`.card[data-step-index="${index}"]`);
            targetElement.removeClass('form-step-edit-form');

            // Update JSON and re-render to show updated content
            saveFormStepsToJson();
            renderFormSteps();
        };

        // Cancel form step edit
        window.cancelFormStepEdit = function (index) {
            const targetElement = $(`.card[data-step-index="${index}"]`);
            const originalBody = targetElement.data('original-body');
            
            if (originalBody) {
                // Restore original card body content
                targetElement.find('.card-body').html(originalBody);
                targetElement.removeClass('form-step-edit-form');
            } else {
                // Fallback: re-render the entire form steps
                renderFormSteps();
            }
        };

        // Load sample forms
        window.loadSampleForm = function (type) {
            if (!confirm('This will replace your current form configuration. Continue?')) {
                return;
            }

            let sampleSteps = [];
            let sampleConfig = {};

            if (type === 'contact') {
                sampleSteps = [
                    {
                        stepKey: 'name',
                        fieldType: 'text',
                        question: 'What\'s your name?',
                        validation: { required: true },
                        sortOrder: 1
                    },
                    {
                        stepKey: 'email',
                        fieldType: 'email',
                        question: 'What\'s your email address?',
                        validation: { required: true, email: true },
                        sortOrder: 2
                    },
                    {
                        stepKey: 'message',
                        fieldType: 'textarea',
                        question: 'What can we help you with?',
                        validation: { required: true, min_length: 10 },
                        sortOrder: 3
                    }
                ];
                sampleConfig = {
                    show_immediately: false,
                    progressive: true,
                    completion_message: 'Thank you! We\'ll get back to you within 24 hours.',
                    ai_completion: false
                };
            } else if (type === 'lead_qualification') {
                sampleSteps = [
                    {
                        stepKey: 'name',
                        fieldType: 'text',
                        question: 'What\'s your name?',
                        validation: { required: true },
                        sortOrder: 1
                    },
                    {
                        stepKey: 'company',
                        fieldType: 'text',
                        question: 'What company do you work for?',
                        validation: { required: true },
                        sortOrder: 2
                    },
                    {
                        stepKey: 'company_size',
                        fieldType: 'select',
                        question: 'How many employees does your company have?',
                        options: ['1-10', '11-50', '51-200', '201-1000', '1000+'],
                        sortOrder: 3,
                        conditionalLogic: {
                            ai_decides: true,
                            decision_prompt: 'Ask about company size unless it\'s a well-known large company'
                        }
                    },
                    {
                        stepKey: 'budget',
                        fieldType: 'select',
                        question: 'What\'s your approximate budget range?',
                        options: ['Under $1k', '$1k-$5k', '$5k-$25k', '$25k+'],
                        sortOrder: 4,
                        conditionalLogic: {
                            ai_decides: true,
                            decision_prompt: 'Ask about budget if they seem like a qualified prospect'
                        }
                    }
                ];
                sampleConfig = {
                    show_immediately: false,
                    progressive: true,
                    ai_completion: true,
                    completion_prompt: 'Determine best next action based on qualification level'
                };
            }

            window.formSteps = sampleSteps;
            $('textarea[name="formSteps"]').val(JSON.stringify(sampleSteps, null, 2));
            $('textarea[name="formConfig"]').val(JSON.stringify(sampleConfig, null, 2));
            renderFormSteps();
        };

        // Sync JSON changes back to visual builder
        $('textarea[name="formSteps"]').on('blur', function () {
            loadFormStepsFromJson();
        });

        // Initialize form steps on page load
        $(document).ready(function () {
            console.log('Initializing form steps editor...');
            loadFormStepsFromJson();
            console.log('Form steps loaded:', window.formSteps);
        });
    });
</script>
