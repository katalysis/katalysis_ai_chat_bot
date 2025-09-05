<?php
defined('C5_EXECUTE') or die('Access denied');

use KatalysisAiChatBot\Entity\Action;
use Concrete\Core\Support\Facade\Application;
use Doctrine\ORM\EntityManagerInterface;

$app = Application::getFacadeApplication();
$entityManager = $app->make(EntityManagerInterface::class);

// Check if sample forms already exist
$existingForms = $entityManager->getRepository(Action::class)->findBy(['actionType' => 'form']);
$existingDynamicForms = $entityManager->getRepository(Action::class)->findBy(['actionType' => 'dynamic_form']);

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1><?php echo t('Create Sample Form Actions'); ?></h1>
            <p class="lead"><?php echo t('This page helps you create sample AI-driven form actions to test the form system.'); ?></p>
            
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle"></i> <?php echo t('About Form Actions'); ?></h5>
                <ul class="mb-0">
                    <li><strong><?php echo t('Basic Forms'); ?>:</strong> <?php echo t('Static form steps that always show in the same order'); ?></li>
                    <li><strong><?php echo t('AI Dynamic Forms'); ?>:</strong> <?php echo t('Smart forms where AI decides which questions to ask based on user responses'); ?></li>
                </ul>
            </div>

            <?php if (!empty($existingForms) || !empty($existingDynamicForms)): ?>
                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle"></i> <?php echo t('Existing Forms Detected'); ?></h5>
                    <p><?php echo t('You already have %d form actions and %d dynamic form actions.', count($existingForms), count($existingDynamicForms)); ?></p>
                    <p class="mb-0"><?php echo t('Creating sample forms will add more actions to your system.'); ?></p>
                </div>
            <?php endif; ?>

            <div class="card-deck">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-envelope"></i> <?php echo t('Contact Form'); ?></h5>
                    </div>
                    <div class="card-body">
                        <p><?php echo t('A simple 3-step contact form with name, email, and message fields.'); ?></p>
                        <ul class="small">
                            <li><?php echo t('Progressive steps (one field at a time)'); ?></li>
                            <li><?php echo t('Static completion message'); ?></li>
                            <li><?php echo t('Basic validation rules'); ?></li>
                        </ul>
                        <button type="button" class="btn btn-info" onclick="createSampleForm('contact')">
                            <?php echo t('Create Contact Form'); ?>
                        </button>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-magic"></i> <?php echo t('AI Lead Qualification'); ?></h5>
                    </div>
                    <div class="card-body">
                        <p><?php echo t('Smart qualification form that adapts based on user responses.'); ?></p>
                        <ul class="small">
                            <li><?php echo t('AI decides which questions to ask'); ?></li>
                            <li><?php echo t('Skips questions for well-known companies'); ?></li>
                            <li><?php echo t('AI determines next action after completion'); ?></li>
                        </ul>
                        <button type="button" class="btn btn-primary" onclick="createSampleForm('lead_qualification')">
                            <?php echo t('Create AI Qualification Form'); ?>
                        </button>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> <?php echo t('Demo Request'); ?></h5>
                    </div>
                    <div class="card-body">
                        <p><?php echo t('Smart demo booking form with role-based logic.'); ?></p>
                        <ul class="small">
                            <li><?php echo t('Asks different questions based on role'); ?></li>
                            <li><?php echo t('AI generates custom questions'); ?></li>
                            <li><?php echo t('Routes to appropriate demo type'); ?></li>
                        </ul>
                        <button type="button" class="btn btn-success" onclick="createSampleForm('demo_request')">
                            <?php echo t('Create Demo Form'); ?>
                        </button>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-life-ring"></i> <?php echo t('Support Request'); ?></h5>
                    </div>
                    <div class="card-body">
                        <p><?php echo t('Support request form with conditional urgency field.'); ?></p>
                        <ul class="small">
                            <li><?php echo t('Conditional urgency for technical issues'); ?></li>
                            <li><?php echo t('Issue type selection'); ?></li>
                            <li><?php echo t('Detailed description field'); ?></li>
                        </ul>
                        <button type="button" class="btn btn-warning" onclick="createSampleForm('support_request')">
                            <?php echo t('Create Support Form'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo t('Create All Sample Forms'); ?></h5>
                    </div>
                    <div class="card-body">
                        <p><?php echo t('Create all four sample forms at once to quickly set up a complete form system for testing.'); ?></p>
                        <button type="button" class="btn btn-lg btn-outline-primary" onclick="createAllSampleForms()">
                            <i class="fas fa-rocket"></i> <?php echo t('Create All Sample Forms'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <div id="results" class="mt-4" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo t('Results'); ?></h5>
                    </div>
                    <div class="card-body" id="results-content">
                        <!-- Results will be shown here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function createSampleForm(type) {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    
    fetch('<?php echo $this->action("create_sample_form"); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ 
            type: type,
            ccm_token: '<?php echo \Core::make("token")->generate("create_sample_form"); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        button.disabled = false;
        button.innerHTML = originalText;
        
        showResult(data);
        
        if (data.success) {
            button.classList.remove('btn-primary', 'btn-info', 'btn-success', 'btn-warning');
            button.classList.add('btn-outline-success');
            button.innerHTML = '<i class="fas fa-check"></i> Created';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        button.disabled = false;
        button.innerHTML = originalText;
        showResult({
            success: false,
            message: 'Error creating form: ' + error.message
        });
    });
}

function createAllSampleForms() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating All Forms...';
    
    fetch('<?php echo $this->action("create_all_sample_forms"); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ 
            ccm_token: '<?php echo \Core::make("token")->generate("create_all_sample_forms"); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        button.disabled = false;
        button.innerHTML = originalText;
        
        showResult(data);
        
        if (data.success) {
            // Update all individual buttons
            document.querySelectorAll('.card .btn').forEach(btn => {
                if (!btn.classList.contains('btn-lg')) {
                    btn.classList.remove('btn-primary', 'btn-info', 'btn-success', 'btn-warning');
                    btn.classList.add('btn-outline-success');
                    btn.innerHTML = '<i class="fas fa-check"></i> Created';
                    btn.disabled = true;
                }
            });
            
            button.classList.add('btn-success');
            button.innerHTML = '<i class="fas fa-check"></i> All Forms Created';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        button.disabled = false;
        button.innerHTML = originalText;
        showResult({
            success: false,
            message: 'Error creating forms: ' + error.message
        });
    });
}

function showResult(data) {
    const resultsDiv = document.getElementById('results');
    const resultsContent = document.getElementById('results-content');
    
    const alertClass = data.success ? 'alert-success' : 'alert-danger';
    const icon = data.success ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    resultsContent.innerHTML = `
        <div class="alert ${alertClass}">
            <h6><i class="fas ${icon}"></i> ${data.success ? '<?php echo t("Success"); ?>' : '<?php echo t("Error"); ?>'}</h6>
            <p class="mb-0">${data.message}</p>
            ${data.details ? `<hr><small>${data.details}</small>` : ''}
        </div>
    `;
    
    resultsDiv.style.display = 'block';
    resultsDiv.scrollIntoView({ behavior: 'smooth' });
}
</script>