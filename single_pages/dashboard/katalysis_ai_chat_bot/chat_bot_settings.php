<?php
defined('C5_EXECUTE') or die('Access Denied.');
?>

<form method="post" enctype="multipart/form-data" action="<?= $controller->action('save') ?>">
    <?php $token->output('ai.settings'); ?>
    <div id="ccm-dashboard-content-inner">

        <script type="module" src="/packages/katalysis_ai_chat_bot/js/scrolly-rail.js"></script>

        <div class="row mb-5 justify-content-between">

            <div class="col-12 col-md-8 col-lg-6">
                <fieldset class="mb-5">
                    <legend><?php echo t('Chat Bot Settings'); ?></legend>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <?php echo $form->label(
                                    "instructions",
                                    t("Instructions"),
                                    [
                                        "class" => "control-label"
                                    ]
                                ); ?>

                                <?php echo $form->textarea(
                                    "instructions",
                                    $instructions,
                                    [
                                        "class" => "form-control",
                                        "max-length" => "10000",
                                        "style" => "field-sizing: content;"
                                    ]
                                ); ?>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <p>The instructions you enter here will be used to guide the AI when it is answering questions.
                            You can use the following placeholders:</p>
                        <ul>
                            <li><code>{page_type}</code> - The page type of the page the user is on</li>
                            <li><code>{page_title}</code> - The title of the page the user is on</li>
                            <li><code>{page_url}</code> - The URL of the page the user is on</li>
                        </ul>

                        <p>For example, if you want the AI to always include a call to action, you can use the following
                            instruction:</p>

                        <div class="alert alert-success">
                            <h6>Example Instructions:</h6>
                            <ul>
                                <li><strong>Location-specific responses:</strong>
                                    <code>If the page type is "location", mention that we are based in your local area and can provide on-site services. Always include local contact information.</code>
                                </li>
                                <li><strong>Service page responses:</strong>
                                    <code>If the page type is "service", focus on the specific service mentioned in {page_title} and provide detailed information about our expertise in this area.</code>
                                </li>
                                <li><strong>General responses:</strong>
                                    <code>Always be helpful and professional. If the user is on a {page_type} page, tailor your response to be relevant to that type of content.</code>
                                </li>
                            </ul>
                        </div>
                    </div>
                </fieldset>



                <fieldset class="mb-5">
                    <legend><?php echo t('Available Page Types'); ?></legend>
                    <div class="row">
                        <div class="col">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th><?php echo t('ID'); ?></th>
                                            <th><?php echo t('Handle'); ?></th>
                                            <th><?php echo t('Name'); ?></th>
                                            <th><?php echo t('Type'); ?></th>
                                            <th><?php echo t('Frequently Added'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($pageTypes)): ?>
                                            <?php foreach ($pageTypes as $pageType): ?>
                                                <tr>
                                                    <td><?php echo h($pageType['id']); ?></td>
                                                    <td><code><?php echo h($pageType['handle']); ?></code></td>
                                                    <td><?php echo h($pageType['name']); ?></td>
                                                    <td>
                                                        <?php if ($pageType['isInternal']): ?>
                                                            <span class="badge bg-secondary"><?php echo t('Internal'); ?></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-primary"><?php echo t('Public'); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($pageType['isFrequentlyAdded']): ?>
                                                            <span class="badge bg-success"><?php echo t('Yes'); ?></span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning"><?php echo t('No'); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">
                                                    <?php echo t('No page types found.'); ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <?php echo t('This list shows all available page types in your Concrete CMS installation. You can use these page type handles in your RAG system configuration.'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="mb-5">
                    <legend><?php echo t('Page Type Response Tracking'); ?></legend>
                    <div class="row">
                        <div class="col">
                            <div class="alert alert-info">
                                <h6><?php echo t('How Page Type Tracking Works'); ?></h6>
                                <p><?php echo t('The AI system now tracks which page types are used to generate responses. This includes:'); ?>
                                </p>
                                <ul>
                                    <li><strong><?php echo t('Current Page Type'); ?></strong> - The page type where the
                                        user is chatting from</li>
                                    <li><strong><?php echo t('Indexed Page Types'); ?></strong> - Page types from the
                                        RAG system\'s retrieved documents</li>
                                    <li><strong><?php echo t('Response Context'); ?></strong> - Information about how
                                        the AI used this context</li>
                                </ul>

                                <h6><?php echo t('Response Data Structure'); ?></h6>
                                <pre><code>{
  "content": "AI response text",
  "metadata": [...],
  "page_types_used": ["location", "service", "blog"],
  "current_page_type": "location",
  "context_info": {
    "current_page_title": "Harpenden Office",
    "current_page_url": "https://example.com/locations/harpenden",
    "total_documents_retrieved": 8,
    "page_types_from_documents": ["location", "service", "blog"]
  }
}</code></pre>


                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="col-12 col-md-8 col-lg-5" style="max-width:500px;">
                <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
                <style>
                    @keyframes welcomeBounce {
                        0% {
                            opacity: 0;
                            transform: scale(0.8) translateY(10px);
                        }
                        50% {
                            opacity: 1;
                            transform: scale(1.05) translateY(-2px);
                        }
                        100% {
                            opacity: 1;
                            transform: scale(1) translateY(0);
                        }
                    }
                    
                    .welcome-animate {
                        animation: welcomeBounce 0.6s ease-out forwards;
                    }
                </style>
                <script>
                    function renderMarkdown(markdown) {
                        return marked.parse(markdown);
                    }

                    // Function to process AI response content and convert "contact us" to links
                    function processAIResponseContent(content) {
                        if (content && typeof content === 'string') {
                            // Case-insensitive replacement for "contact us" variations
                            return content.replace(/\b(contact us|Contact Us|CONTACT US)\b/g, '<a href="/contact" target="_blank" class="text-primary fw-bold">$1</a>');
                        }
                        return content;
                    }
                </script>
                <script>
                    console.log('=== CHAT SCRIPT LOADED ===');
                    console.log('Current time:', new Date().toISOString());

                    // Initialize currentMode variable
                    let currentMode = 'rag'; // Default to RAG mode

                    // Function to generate dynamic welcome message using AI
                    function generateWelcomeMessage() {
                        const now = new Date();
                        const hour = now.getHours();
                        
                        let timeGreeting = '';
                        if (hour < 12) {
                            timeGreeting = 'Good morning';
                        } else if (hour < 17) {
                            timeGreeting = 'Good afternoon';
                        } else {
                            timeGreeting = 'Good evening';
                        }
                        
                        // Get page information if available
                        let pageInfo = '';
                        try {
                            // Try to get page title from document
                            const pageTitle = document.title || '';
                            if (pageTitle && pageTitle !== '') {
                                pageInfo = `, welcome to our ${pageTitle.toLowerCase().includes('legal') ? 'legal ' : ''}website`;
                            } else {
                                pageInfo = ', welcome to our website';
                            }
                        } catch (e) {
                            pageInfo = ', welcome to our website';
                        }
                        
                        return `${timeGreeting}${pageInfo}. How can we help you today?`;
                    }

                    // Function to generate AI-powered welcome message
                    function generateAIWelcomeMessage() {
                        const now = new Date();
                        const hour = now.getHours();
                        
                        // Get page information - use debug context if available, otherwise use actual page info
                        let pageTitle = document.title || '';
                        let pageUrl = window.location.href;
                        let pageType = '';
                        
                        // Use debug context if available and debug mode is enabled
                        if (window.katalysisAIDebugMode) {
                            const debugPageTitle = document.getElementById('debug_page_title')?.value || '';
                            const debugPageType = document.getElementById('debug_page_type')?.value || '';
                            const debugPageUrl = document.getElementById('debug_page_url')?.value || '';
                            
                            if (debugPageTitle) pageTitle = debugPageTitle;
                            if (debugPageType) pageType = debugPageType;
                            if (debugPageUrl) pageUrl = debugPageUrl;
                        }
                        
                        // Create a prompt for the AI to generate a welcome message
                        const welcomePrompt = `Generate a short, friendly welcome message for a legal services website. 
                        
                        Context:
                        - Time of day: ${hour < 12 ? 'morning' : hour < 17 ? 'afternoon' : 'evening'}
                        - Current page: ${pageTitle}
                        - Page URL: ${pageUrl}
                        
                        Requirements:
                        - Include time-based greeting (Good morning/afternoon/evening)
                        - Keep it very brief (1 sentence maximum)
                        - Be welcoming, appreciative and professional
                        - End with "How can we help?"
                        - Maximum 15-20 words total
                        
                        Generate only the welcome message text, no additional formatting.`;
                        
                        // Prepare request data with debug context if enabled
                        let requestData = {
                            message: welcomePrompt,
                            mode: 'basic' // Use basic mode for welcome message generation
                        };

                        // Add debug context if debug mode is enabled and debug fields have values
                        if (window.katalysisAIDebugMode) {
                            const debugPageTitle = document.getElementById('debug_page_title')?.value || '';
                            const debugPageType = document.getElementById('debug_page_type')?.value || '';
                            const debugPageUrl = document.getElementById('debug_page_url')?.value || '';
                            
                            if (debugPageTitle || debugPageType || debugPageUrl) {
                                requestData.debug_context = {
                                    page_title: debugPageTitle,
                                    page_type: debugPageType,
                                    page_url: debugPageUrl
                                };
                                console.log('Adding debug context to welcome message request:', requestData.debug_context);
                            }
                        }
                        
                        // Use the existing AI system to generate the welcome message
                        $.ajax({
                            type: "POST",
                            url: "<?= $controller->action('ask_ai') ?>",
                            data: JSON.stringify(requestData),
                            contentType: "application/json",
                            headers: {
                                'X-CSRF-TOKEN': '<?= $token->generate('ai.settings') ?>'
                            },
                            success: function (data) {
                                console.log('AI Welcome Response:', data);
                                
                                let welcomeText = '';
                                
                                if (typeof data === 'object' && data.content) {
                                    welcomeText = data.content;
                                } else if (typeof data === 'string') {
                                    welcomeText = data;
                                }
                                
                                // Show and animate the welcome response
                                const welcomeResponse = document.getElementById('welcome-response');
                                const welcomeElement = document.getElementById('welcome-message');
                                
                                if (welcomeResponse && welcomeElement) {
                                    // Set the message text
                                    if (welcomeText && welcomeText.trim()) {
                                        welcomeElement.textContent = welcomeText;
                                    } else {
                                        // If AI response is empty, show fallback
                                        welcomeElement.textContent = 'Hi, How can we help today?';
                                    }
                                    
                                    // Show the response div
                                    welcomeResponse.style.display = 'flex';
                                    
                                    // Add animation class
                                    welcomeResponse.classList.add('welcome-animate');
                                    
                                    // Remove animation class after animation completes
                                    setTimeout(function() {
                                        welcomeResponse.classList.remove('welcome-animate');
                                    }, 600);
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error('Error generating AI welcome message:', error);
                                // Show fallback with animation
                                const welcomeResponse = document.getElementById('welcome-response');
                                const welcomeElement = document.getElementById('welcome-message');
                                
                                if (welcomeResponse && welcomeElement) {
                                    welcomeElement.textContent = 'Hi, How can we help today?';
                                    
                                    // Show the response div
                                    welcomeResponse.style.display = 'flex';
                                    
                                    // Add animation class
                                    welcomeResponse.classList.add('welcome-animate');
                                    
                                    // Remove animation class after animation completes
                                    setTimeout(function() {
                                        welcomeResponse.classList.remove('welcome-animate');
                                    }, 600);
                                }
                            }
                        });
                    }

                    // Mode toggle functionality
                    document.addEventListener('DOMContentLoaded', function () {
                        const ragModeToggle = document.getElementById('ragModeToggle');
                        const modeDescription = document.getElementById('modeDescription');

                        if (ragModeToggle) {
                            // Set initial state
                            currentMode = ragModeToggle.checked ? 'rag' : 'basic';
                            updateModeDescription();

                            // Add event listener
                            ragModeToggle.addEventListener('change', function () {
                                currentMode = this.checked ? 'rag' : 'basic';
                                updateModeDescription();
                                console.log('Mode changed to:', currentMode);
                            });
                        }

                        function updateModeDescription() {
                            if (modeDescription) {
                                if (currentMode === 'rag') {
                                    modeDescription.textContent = 'RAG Mode: AI will search your indexed content to provide relevant answers.';
                                } else {
                                    modeDescription.textContent = 'Basic Mode: AI will provide general responses without searching indexed content.';
                                }
                            }
                        }
                    });

                    // Chat persistence functions
                    function saveChatHistory() {
                        console.log('Saving chat history...');

                        const chatContainer = document.getElementById('chat');
                        if (!chatContainer) {
                            console.error('Chat container not found!');
                            return;
                        }

                        const chatHistory = chatContainer.innerHTML;
                        console.log('Chat history length:', chatHistory.length);

                        try {
                            localStorage.setItem('katalysis_chat_history', chatHistory);
                            localStorage.setItem('katalysis_chat_timestamp', Date.now().toString());
                            console.log('Chat history saved successfully');
                        } catch (e) {
                            console.error('Error saving chat history:', e);
                        }
                    }

                    function loadChatHistory() {
                        console.log('Loading chat history...');

                        const chatContainer = document.getElementById('chat');
                        if (!chatContainer) {
                            console.error('Chat container not found!');
                            return;
                        }

                        try {
                            const savedHistory = localStorage.getItem('katalysis_chat_history');
                            const timestamp = localStorage.getItem('katalysis_chat_timestamp');

                            console.log('Saved history exists:', !!savedHistory);
                            console.log('Timestamp exists:', !!timestamp);

                            if (savedHistory && timestamp) {
                                const age = Date.now() - parseInt(timestamp);
                                const maxAge = 24 * 60 * 60 * 1000; // 24 hours

                                console.log('Chat age:', age, 'ms');

                                if (age < maxAge) {
                                    console.log('Loading saved chat history...');

                                    // Replace the entire chat container content
                                    chatContainer.innerHTML = savedHistory;

                                    console.log('Chat history loaded successfully');

                                    // Scroll to bottom after loading
                                    setTimeout(function () {
                                        scrollToBottom();
                                    }, 100);
                                } else {
                                    console.log('Chat history is too old, clearing...');
                                    clearChatHistory();
                                }
                            } else {
                                console.log('No saved chat history found');
                            }
                        } catch (e) {
                            console.error('Error loading chat history:', e);
                        }
                    }

                    function clearChatHistory() {
                        console.log('Clearing chat history...');

                        // Clear browser localStorage
                        localStorage.removeItem('katalysis_chat_history');
                        localStorage.removeItem('katalysis_chat_timestamp');

                        // Clear server-side chat files
                        $.ajax({
                            type: "POST",
                            url: "<?= $controller->action('clear_chat_history') ?>",
                            headers: {
                                'X-CSRF-TOKEN': '<?= $token->generate('ai.settings') ?>'
                            },
                            success: function (data) {
                                console.log('Server chat history cleared:', data);
                                location.reload();
                            },
                            error: function (xhr, status, error) {
                                console.error('Error clearing server chat history:', error);
                                // Still reload even if server clear fails
                                location.reload();
                            }
                        });

                    }

                    function scrollToBottom() {
                        const chatContainer = document.getElementById('chat');
                        if (chatContainer) {
                            console.log('Scrolling to bottom...');
                            console.log('Scroll height:', chatContainer.scrollHeight);
                            console.log('Client height:', chatContainer.clientHeight);

                            // Force scroll to bottom
                            chatContainer.scrollTop = chatContainer.scrollHeight;

                            // Also try with a small delay
                            setTimeout(function () {
                                chatContainer.scrollTop = chatContainer.scrollHeight;
                            }, 50);
                        }
                    }

                    // Track if welcome message has been generated to prevent duplicates
                    let welcomeMessageGenerated = false;

                    // Load chat history when page loads
                    $(document).ready(function () {
                        console.log('jQuery ready - loading chat history...');
                        loadChatHistory();
                        
                        // Generate AI welcome message only once
                        if (!welcomeMessageGenerated) {
                            welcomeMessageGenerated = true;
                            setTimeout(function() {
                                generateAIWelcomeMessage();
                            }, 500); // Small delay to ensure everything is loaded
                        }
                    });

                    // Also try loading with vanilla JS
                    document.addEventListener('DOMContentLoaded', function () {
                        console.log('DOM loaded - loading chat history...');
                        loadChatHistory();
                        
                        // Generate AI welcome message only once
                        if (!welcomeMessageGenerated) {
                            welcomeMessageGenerated = true;
                            setTimeout(function() {
                                generateAIWelcomeMessage();
                            }, 500); // Small delay to ensure everything is loaded
                        }
                    });

                    // Your existing addMessage function
                    function addMessage() {
                        var messageValue = document.getElementById('message').value;
                        if (!messageValue.trim()) {
                            alert('Please enter a message');
                            return;
                        } else {
                            $("#chat").append('<div class="user-message">' + messageValue + '</div>');
                            saveChatHistory(); // Save after user message
                            scrollToBottom();
                        }

                        $("#chat").append('<div class="ai-loading">AI is thinking...</div>');
                        saveChatHistory(); // Save after loading indicator
                        scrollToBottom();

                        // Prepare request data with debug context if enabled
                        let requestData = {
                            message: messageValue,
                            mode: currentMode
                        };

                        // Add debug context if debug mode is enabled
                        if (window.katalysisAIDebugMode) {
                            const debugPageTitle = document.getElementById('debug_page_title')?.value || '';
                            const debugPageType = document.getElementById('debug_page_type')?.value || '';
                            const debugPageUrl = document.getElementById('debug_page_url')?.value || '';
                            
                            if (debugPageTitle || debugPageType || debugPageUrl) {
                                requestData.debug_context = {
                                    page_title: debugPageTitle,
                                    page_type: debugPageType,
                                    page_url: debugPageUrl
                                };
                                console.log('Adding debug context to request:', requestData.debug_context);
                            }
                        }

                        $.ajax({
                            type: "POST",
                            url: "<?= $controller->action('ask_ai') ?>",
                            data: JSON.stringify(requestData),
                            contentType: "application/json",
                            headers: {
                                'X-CSRF-TOKEN': '<?= $token->generate('ai.settings') ?>'
                            },
                            success: function (data) {
                                console.log('Response:', data);
                                $(".ai-loading").remove();

                                // Handle new response format with metadata
                                let responseContent = data;
                                let metadata = [];

                                if (typeof data === 'object' && data.content) {
                                    responseContent = data.content;
                                    metadata = data.metadata || [];
                                }

                                // Process the response content to convert "contact us" to links
                                let processedContent = processAIResponseContent(responseContent);

                                let responseHtml = '<div class="ai-response"><img src="https://d7keiwzj12p9.cloudfront.net/avatars/katalysis-bot-icon-1748356162310.webp" alt="Katalysis Bot"><div>' + renderMarkdown(processedContent);

                                // Add "More Info" links if metadata is available
                                if (metadata && metadata.length > 0) {
                                    responseHtml += '<div class="more-info-links mt-3"><strong>More Information:</strong><ul class="list-unstyled mt-2">';
                                    metadata.forEach(function (link) {
                                        responseHtml += '<li><a href="' + link.url + '" target="_blank" class="btn btn-sm btn-outline-primary me-2 mb-1">' + link.title + '</a></li>';
                                    });
                                    responseHtml += '</ul></div>';
                                }

                                // Show debug info if enabled
                                if (window.katalysisAIDebugMode) {
                                    responseHtml += displayPageTypesInfo(data);
                                }

                                responseHtml += '</div></div>';
                                $("#chat").append(responseHtml);

                                saveChatHistory(); // Save after AI response
                                scrollToBottom();
                                document.getElementById('message').value = '';
                            },
                            error: function (xhr, status, error) {
                                console.error('Error:', error);
                                $(".ai-loading").remove();
                                $("#chat").append('<div class="ai-error">Error: ' + error + '</div>');
                                saveChatHistory(); // Save after error
                                scrollToBottom();
                            }
                        });
                    }

                    // Update the addMessageWithMode function as well
                    function addMessageWithMode(message) {
                        var messageValue = message || document.getElementById('message').value;
                        if (!messageValue.trim()) {
                            alert('Please enter a message');
                            return;
                        } else {
                            $("#chat").append('<div class="user-message">' + messageValue + '</div>');
                            saveChatHistory(); // Save after each message
                            scrollToBottom(); // Scroll after adding user message
                        }

                        $("#chat").append('<div class="ai-loading">AI is thinking...</div>');
                        saveChatHistory(); // Save after adding loading indicator

                        // Prepare request data with debug context if enabled
                        let requestData = {
                            message: messageValue,
                            mode: currentMode
                        };

                        // Add debug context if debug mode is enabled
                        if (window.katalysisAIDebugMode) {
                            const debugPageTitle = document.getElementById('debug_page_title')?.value || '';
                            const debugPageType = document.getElementById('debug_page_type')?.value || '';
                            const debugPageUrl = document.getElementById('debug_page_url')?.value || '';
                            
                            if (debugPageTitle || debugPageType || debugPageUrl) {
                                requestData.debug_context = {
                                    page_title: debugPageTitle,
                                    page_type: debugPageType,
                                    page_url: debugPageUrl
                                };
                                console.log('Adding debug context to request:', requestData.debug_context);
                            }
                        }

                        $.ajax({
                            type: "POST",
                            url: "<?= $controller->action('ask_ai') ?>",
                            data: JSON.stringify(requestData),
                            contentType: "application/json",
                            headers: {
                                'X-CSRF-TOKEN': '<?= $token->generate('ai.settings') ?>'
                            },
                            success: function (data) {
                                console.log('Response:', data);
                                $(".ai-loading").remove();

                                // Handle new response format with metadata
                                let responseContent = data;
                                let metadata = [];

                                if (typeof data === 'object' && data.content) {
                                    responseContent = data.content;
                                    metadata = data.metadata || [];
                                }

                                // Process the response content to convert "contact us" to links
                                let processedContent = processAIResponseContent(responseContent);

                                let responseHtml = '<div class="ai-response"><img src="https://d7keiwzj12p9.cloudfront.net/avatars/katalysis-bot-icon-1748356162310.webp" alt="Katalysis Bot"><div>' + renderMarkdown(processedContent);

                                // Add "More Info" links if metadata is available
                                if (metadata && metadata.length > 0) {
                                    responseHtml += '<div class="more-info-links mt-3"><strong>More Information:</strong><ul class="list-unstyled mt-2">';
                                    metadata.forEach(function (link) {
                                        responseHtml += '<li><a href="' + link.url + '" target="_blank" class="btn btn-sm btn-outline-primary me-2 mb-1">' + link.title + '</a></li>';
                                    });
                                    responseHtml += '</ul></div>';
                                }

                                // Show debug info if enabled
                                if (window.katalysisAIDebugMode) {
                                    responseHtml += displayPageTypesInfo(data);
                                }

                                responseHtml += '</div></div>';
                                $("#chat").append(responseHtml);

                                saveChatHistory(); // Save after AI response
                                scrollToBottom(); // Scroll after adding AI response
                                document.getElementById('message').value = '';
                            },
                            error: function (xhr, status, error) {
                                console.error('Error:', error);
                                $(".ai-loading").remove();
                                $("#chat").append('<div class="ai-error">Error: ' + error + '</div>');
                                saveChatHistory();
                                scrollToBottom(); // Scroll after adding error message
                            }
                        });
                    }

                    // Add smooth scrolling for better UX
                    function scrollToBottomSmooth() {
                        const chatContainer = document.getElementById('chat');
                        chatContainer.scrollTo({
                            top: chatContainer.scrollHeight,
                            behavior: 'smooth'
                        });
                    }

                    // Optional: Auto-scroll on window resize
                    window.addEventListener('resize', function () {
                        scrollToBottom();
                    });

                    // Function to display page types information
                    function displayPageTypesInfo(data) {
                        let infoHtml = '<div class="page-types-debug mt-2 p-2 bg-light border rounded">';

                        // Page Types Information
                        if (data.page_types_used && data.page_types_used.length > 0) {
                            infoHtml += '<small class="text-muted"><strong>Page Types Used:</strong> ' + data.page_types_used.join(', ') + '</small>';

                            if (data.context_info) {
                                infoHtml += '<br><small class="text-muted"><strong>Documents Retrieved:</strong> ' + data.context_info.total_documents_retrieved + '</small>';
                                if (data.context_info.page_types_from_documents && data.context_info.page_types_from_documents.length > 0) {
                                    infoHtml += '<br><small class="text-muted"><strong>From Documents:</strong> ' + data.context_info.page_types_from_documents.join(', ') + '</small>';
                                }
                            }
                        }

                        // Link Selection Debug Information
                        if (data.debug_info && data.debug_info.link_selection) {
                            const linkInfo = data.debug_info.link_selection;
                            infoHtml += '<hr class="my-2">';
                            infoHtml += '<small class="text-muted"><strong>Link Selection:</strong></small><br>';
                            infoHtml += '<small class="text-muted">• Total documents processed: ' + linkInfo.total_documents_processed + '</small><br>';
                            infoHtml += '<small class="text-muted">• Documents with URLs: ' + linkInfo.documents_with_urls + '</small><br>';
                            infoHtml += '<small class="text-muted">• Candidate documents: ' + linkInfo.candidate_documents + '</small><br>';
                            infoHtml += '<small class="text-muted">• Selected links: ' + linkInfo.ai_selected_links + '</small><br>';
                        }

                        // Link Details
                        if (data.debug_info && data.debug_info.scoring_details && data.debug_info.scoring_details.length > 0) {
                            infoHtml += '<hr class="my-2">';
                            infoHtml += '<small class="text-muted"><strong>Selected Links:</strong></small><br>';

                            data.debug_info.scoring_details.forEach(function (link, index) {
                                infoHtml += '<div class="mt-1 p-1 bg-white border rounded">';
                                infoHtml += '<small class="text-muted"><strong>' + (index + 1) + '. ' + link.title + '</strong></small><br>';
                                infoHtml += '<small class="text-muted">• Score: ' + link.final_score.toFixed(3) + '</small><br>';
                                if (link.selection_reason && link.selection_reason !== 'AI chose this as most relevant to the user\'s question') {
                                    infoHtml += '<small class="text-muted">• Note: ' + link.selection_reason + '</small><br>';
                                }
                                infoHtml += '</div>';
                            });
                        }

                        infoHtml += '</div>';
                        return infoHtml;
                    }
                </script>
                <section>
                    <div class="card border rounded-3 mb-5">
                        <div class="card-body">
                            <div id="chat">
                                <div class="divider d-flex align-items-center mb-4">
                                    <p class="text-center mx-3 mb-0" style="color: #a2aab7;">Today</p>
                                </div>
                                <div class="ai-response" id="welcome-response" style="display: none;">
                                    <img src="https://d7keiwzj12p9.cloudfront.net/avatars/katalysis-bot-icon-1748356162310.webp"
                                        alt="Katalysis Bot">
                                    <div id="welcome-message" style="font-weight: bold;">Generating welcome message...</div>
                                </div>
                            </div>
                        </div>

                        <div class="suggestions-container bg-primary-subtle d-flex align-items-center overflow-x-auto">
                            <button type="button" data-bound
                                class="bg-primary btn-scrolly-rail btn-scrolly-rail--previous animate-fade"
                                id="collection-2-btn-previous">
                                <span class="visually-hidden">Scroll previous items into view</span>
                                <i class="icon fas fa-arrow-left"></i>
                            </button>
                            <div class="scrolly-rail-wrapper">
                                <scrolly-rail data-control-previous="collection-2-btn-previous"
                                    data-control-next="collection-2-btn-next">
                                    <div class="collection-list">
                                        <button class="btn btn-outline-secondary btn-sm flex-shrink-0" dir="auto"
                                            type="button" onclick="addMessageWithMode('Arrange a meeting')">Arrange
                                            a
                                            meeting</button>
                                        <button class="btn btn-outline-secondary btn-sm flex-shrink-0" dir="auto"
                                            type="button" onclick="addMessageWithMode('Request a proposal')">Request
                                            a
                                            proposal</button>
                                        <button class="btn btn-outline-secondary btn-sm flex-shrink-0" dir="auto"
                                            type="button"
                                            onclick="addMessageWithMode('Arrange a FREE Strategy Session')">Arrange
                                            a
                                            FREE Strategy Session</button>
                                        <button class="btn btn-outline-secondary btn-sm flex-shrink-0" dir="auto"
                                            type="button"
                                            onclick="addMessageWithMode('Arrange a Pro LawSite Demo')">Arrange a Pro
                                            LawSite Demo</button>
                                        <button class="btn btn-outline-secondary btn-sm flex-shrink-0" dir="auto"
                                            type="button" onclick="addMessageWithMode('Arrange a meeting')">Arrange
                                            a
                                            meeting</button>
                                        <button class="btn btn-outline-secondary btn-sm flex-shrink-0" dir="auto"
                                            type="button" onclick="addMessageWithMode('Request a proposal')">Request
                                            a
                                            proposal</button>
                                        <button class="btn btn-outline-secondary btn-sm flex-shrink-0" dir="auto"
                                            type="button"
                                            onclick="addMessageWithMode('Arrange a FREE Strategy Session')">Arrange
                                            a
                                            FREE Strategy Session</button>
                                        <button class="btn btn-outline-secondary btn-sm flex-shrink-0" dir="auto"
                                            type="button"
                                            onclick="addMessageWithMode('Arrange a Pro LawSite Demo')">Arrange a Pro
                                            LawSite Demo</button>
                                        <button class="btn btn-outline-secondary btn-sm flex-shrink-0" dir="auto"
                                            type="button" onclick="addMessageWithMode('Arrange a meeting')">Arrange
                                            a
                                            meeting</button>
                                        <button class="btn btn-outline-secondary btn-sm flex-shrink-0" dir="auto"
                                            type="button" onclick="addMessageWithMode('Request a proposal')">Request
                                            a
                                            proposal</button>
                                        <button class="btn btn-outline-secondary btn-sm flex-shrink-0" dir="auto"
                                            type="button"
                                            onclick="addMessageWithMode('Arrange a FREE Strategy Session')">Arrange
                                            a
                                            FREE Strategy Session</button>
                                        <button class="btn btn-outline-secondary btn-sm flex-shrink-0" dir="auto"
                                            type="button"
                                            onclick="addMessageWithMode('Arrange a Pro LawSite Demo')">Arrange a Pro
                                            LawSite Demo</button>
                                    </div>
                                </scrolly-rail>
                            </div>
                            <button type="button"
                                class="bg-primary btn-scrolly-rail btn-scrolly-rail--next animate-fade"
                                id="collection-2-btn-next">
                                <span class="visually-hidden">Scroll next items into view</span>
                                <i class="icon fas fa-arrow-right"></i>
                                <path
                                    d="M8.14645 3.14645C8.34171 2.95118 8.65829 2.95118 8.85355 3.14645L12.8536 7.14645c3.0488 7.34171 13.0488 7.65829 12.8536 7.85355L8.85355 11.8536C8.65829 12.0488 8.34171 12.0488 8.14645 11.8536C7.95118 11.6583 7.95118 11.3417 8.14645 11.1464L11.2929 8H2.5C2.22386 8 2 7.77614 2 7.5C2 7.22386 2.22386 7 2.5 7H11.2929L8.14645 3.85355C7.95118 3.65829 7.95118 3.34171 8.14645 3.14645Z"
                                    fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>

                        <div
                            class="card-footer bg-dark rounded-bottom-3 text-muted d-flex justify-content-start align-items-center p-3">
                            <input id="message" tabindex="0" name="message"
                                class="form-control form-control-lg border-0 bg-white px-3 py-2 text-base focus:border-primary focus:outline-none disabled:bg-secondary ltr-placeholder "
                                maxlength="10000" placeholder="Add a message" autocomplete="off" aria-label="question"
                                dir="auto" enterkeyhint="enter"
                                style="height: 42px; border-radius: 0.875rem; min-height: 40px;" />


                            <button type="button" class="btn btn-light ms-2 text-muted" onclick="clearChatHistory()">
                                <i class="fas fa-trash"></i>
                            </button>

                            <button class="btn btn-primary ms-2" onclick="addMessage()" type="button"
                                aria-label="send message"><i class="fas fa-paper-plane"></i></button>
                        </div>

                    </div>
                    <div class="form-group mb-4">
                        <div class="form-check">
                            <?php echo $form->checkbox('debug_mode', 1, $debugMode, ['class' => 'form-check-input', 'id' => 'debug_mode']); ?>
                            <?php echo $form->label('debug_mode', t('Enable Debug Mode (show page type info under each response)'), ['class' => 'form-check-label']); ?>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <div class="alert alert-info">
                            <h6><?php echo t('AI-Powered Link Selection'); ?></h6>
                            <p><?php echo t('The system now uses AI to intelligently select the most relevant links for each user question. Instead of rigid scoring algorithms, the AI analyzes the user\'s question context and chooses links that would be most helpful.'); ?></p>
                            <ul>
                                <li><strong><?php echo t('Intelligent Selection'); ?></strong> - AI considers the user's question context when choosing links</li>
                                <li><strong><?php echo t('Contextual Relevance'); ?></strong> - Links are selected based on how well they address the specific user need</li>
                                <li><strong><?php echo t('Quality Control'); ?></strong> - Only documents with reasonable relevance scores (≥0.3) are considered</li>
                                <li><strong><?php echo t('Fallback Protection'); ?></strong> - If AI selection fails, falls back to top-scoring documents</li>
                            </ul>
                        </div>
                    </div>

                    <script>window.katalysisAIDebugMode = <?php echo $debugMode ? 'true' : 'false'; ?>;</script>

                    <!-- Debug Context Fields -->
                    <fieldset class="mb-4" id="debugContextFields" style="<?php echo $debugMode ? '' : 'display: none;'; ?>">
                        <legend><?php echo t('Debug Context Fields'); ?></legend>
                        <div class="alert alert-info">
                            <p><?php echo t('These fields allow you to test the AI with specific page context. They will be used when sending messages in debug mode.'); ?></p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?php echo $form->label('debug_page_title', t('Page Title'), ['class' => 'control-label']); ?>
                                    <?php echo $form->text('debug_page_title', $debugPageTitle ?? '', [
                                        'class' => 'form-control',
                                        'placeholder' => 'e.g., About Katalysis'
                                    ]); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?php echo $form->label('debug_page_type', t('Page Type'), ['class' => 'control-label']); ?>
                                    <?php echo $form->text('debug_page_type', $debugPageType ?? '', [
                                        'class' => 'form-control',
                                        'placeholder' => 'e.g., page, location, service'
                                    ]); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?php echo $form->label('debug_page_url', t('Page URL'), ['class' => 'control-label']); ?>
                                    <?php echo $form->text('debug_page_url', $debugPageUrl ?? '', [
                                        'class' => 'form-control',
                                        'placeholder' => 'e.g., /about, /locations/harpenden'
                                    ]); ?>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <script>
                        window.katalysisAIDebugMode = <?php echo $debugMode ? 'true' : 'false'; ?>;
                        
                        // Toggle debug context fields visibility
                        document.addEventListener('DOMContentLoaded', function() {
                            const debugModeCheckbox = document.getElementById('debug_mode');
                            const debugContextFields = document.getElementById('debugContextFields');
                            
                            if (debugModeCheckbox && debugContextFields) {
                                debugModeCheckbox.addEventListener('change', function() {
                                    debugContextFields.style.display = this.checked ? 'block' : 'none';
                                    window.katalysisAIDebugMode = this.checked;
                                });
                            }
                        });
                    </script>

                </section>
            </div>
        </div>

    </div>

    </div>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <div class="float-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save" aria-hidden="true"></i> <?php echo t('Save'); ?>
                </button>
            </div>
        </div>
    </div>
</form>
