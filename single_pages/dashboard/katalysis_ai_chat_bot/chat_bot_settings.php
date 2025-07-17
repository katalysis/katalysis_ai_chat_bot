<?php
defined('C5_EXECUTE') or die('Access Denied.');
?>

<form method="post" enctype="multipart/form-data" action="<?= $controller->action('save') ?>">
    <?php $token->output('ai.settings'); ?>
    <div id="ccm-dashboard-content-inner">

        <script type="module" src="/packages/katalysis_ai_chat_bot/js/scrolly-rail.js"></script>

        <div class="row mb-5 justify-content-between">

            <div class="col-12 col-md-8 col-lg-6">
                <div class="alert alert-primary mb-5">
                    <h5><i class="fas fa-robot"></i> <?php echo t('AI Chat Bot System Overview'); ?></h5>
                    <p class="mb-3"><?php echo t('This system provides an intelligent AI chatbot that can understand your website content and provide contextual responses. Here\'s how it works:'); ?></p>
                    <ul class="mb-0">
                        <li><strong><?php echo t('Content Indexing'); ?></strong> - <?php echo t('Your website pages are automatically indexed, including key attributes like page titles, page types, URLs, and content.'); ?></li>
                        <li><strong><?php echo t('Context Awareness'); ?></strong> - <?php echo t('The AI uses this indexed information to understand what page the user is on and provide relevant responses.'); ?></li>
                        <li><strong><?php echo t('Dynamic Responses'); ?></strong> - <?php echo t('AI generates personalized welcome messages and intelligent responses based on the user\'s context and your content.'); ?></li>
                        <li><strong><?php echo t('Smart Link Selection'); ?></strong> - <?php echo t('The system intelligently selects the most relevant links to include with responses, helping users find related information.'); ?></li>
                    </ul>
                </div>

                <fieldset class="mb-5">
                    <legend><?php echo t('Chat Bot Settings'); ?></legend>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <?php echo $form->label(
                                    "instructions",
                                    t("Main AI Instructions"),
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
                        <h6><?php echo t('Main AI Instructions'); ?></h6>
                        <p><?php echo t('These instructions define how the AI responds to user questions. They guide the AI\'s personality, tone, and approach to providing information.'); ?></p>
                        
                        <h6><?php echo t('Available Context Placeholders'); ?></h6>
                        <p><?php echo t('You can use these placeholders to make responses context-aware:'); ?></p>
                        <ul>
                            <li><code>{page_type}</code> - <?php echo t('The page type of the current page (e.g., location, service, blog, page)'); ?></li>
                            <li><code>{page_title}</code> - <?php echo t('The title of the current page'); ?></li>
                            <li><code>{page_url}</code> - <?php echo t('The URL of the current page'); ?></li>
                        </ul>

                        <h6><?php echo t('Example Instructions'); ?></h6>
                        <div class="alert alert-success">
                            <p><strong><?php echo t('Location-specific responses:'); ?></strong></p>
                            <p><code><?php echo t('If the page type is "location", mention that we are based in your local area and can provide on-site services. Always include local contact information.'); ?></code></p>
                            
                            <p><strong><?php echo t('Service page responses:'); ?></strong></p>
                            <p><code><?php echo t('If the page type is "service", focus on the specific service mentioned in {page_title} and provide detailed information about our expertise in this area.'); ?></code></p>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="restoreDefaultInstructions()">
                            <i class="fas fa-undo"></i> <?php echo t('Restore Default Instructions'); ?>
                        </button>
                    </div>
                </fieldset>

                <fieldset class="mb-5">
                    <legend><?php echo t('Welcome Message Prompt'); ?></legend>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <?php echo $form->label(
                                    "welcome_message_prompt",
                                    t("Welcome Message Prompt"),
                                    [
                                        "class" => "control-label"
                                    ]
                                ); ?>

                                <?php echo $form->textarea(
                                    "welcome_message_prompt",
                                    $welcomeMessagePrompt,
                                    [
                                        "class" => "form-control",
                                        "max-length" => "10000",
                                        "style" => "field-sizing: content;",
                                        "rows" => "12"
                                    ]
                                ); ?>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <h6><?php echo t('Welcome Message Generation'); ?></h6>
                        <p><?php echo t('This prompt controls how the AI generates personalized welcome messages when users first visit the chat. The AI uses this prompt along with current context to create dynamic, relevant greetings.'); ?></p>
                        
                        <h6><?php echo t('Context Placeholders'); ?></h6>
                        <p><?php echo t('Use these placeholders to include dynamic information:'); ?></p>
                        <ul>
                            <li><code>{time_of_day}</code> - <?php echo t('Automatically replaced with "morning", "afternoon", or "evening" based on current time'); ?></li>
                            <li><code>{page_title}</code> - <?php echo t('The title of the page the user is currently viewing'); ?></li>
                            <li><code>{page_url}</code> - <?php echo t('The URL of the current page'); ?></li>
                        </ul>

                        <h6><?php echo t('Example Usage'); ?></h6>
                        <p><code><?php echo t('Good {time_of_day}! Welcome to our {page_title} page. How can we help you today?'); ?></code></p>
                        <p><small class="text-muted"><?php echo t('This would generate: "Good morning! Welcome to our Legal Services page. How can we help you today?"'); ?></small></p>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="restoreDefaultWelcomePrompt()">
                            <i class="fas fa-undo"></i> <?php echo t('Restore Default Prompt'); ?>
                        </button>
                    </div>
                </fieldset>

                <fieldset class="mb-5">
                    <legend><?php echo t('AI Link Selection Rules'); ?></legend>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <?php echo $form->label(
                                    "link_selection_rules",
                                    t("Link Selection Rules"),
                                    [
                                        "class" => "control-label"
                                    ]
                                ); ?>

                                <?php echo $form->textarea(
                                    "link_selection_rules",
                                    $linkSelectionRules,
                                    [
                                        "class" => "form-control",
                                        "max-length" => "10000",
                                        "style" => "field-sizing: content;",
                                        "rows" => "15"
                                    ]
                                ); ?>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <h6><?php echo t('Intelligent Link Selection'); ?></h6>
                        <p><?php echo t('These rules guide the AI when selecting which links to include with responses. Instead of showing all available links, the AI intelligently chooses the most relevant ones based on the user\'s question and context.'); ?></p>
                        
                        <h6><?php echo t('How It Works'); ?></h6>
                        <ul>
                            <li><?php echo t('The system searches your indexed content for relevant documents'); ?></li>
                            <li><?php echo t('The AI analyzes the user\'s question and available documents'); ?></li>
                            <li><?php echo t('Using these rules, the AI selects 1-3 most relevant links'); ?></li>
                            <li><?php echo t('Links are displayed as "More Information" buttons below responses'); ?></li>
                        </ul>

                        <h6><?php echo t('Page Type Context'); ?></h6>
                        <p><?php echo t('Available page types in your system include:'); ?> 
                        <?php 
                        $pageTypeNames = array_map(function($pt) { return $pt['name']; }, $pageTypes);
                        echo implode(', ', array_slice($pageTypeNames, 0, 5));
                        if (count($pageTypeNames) > 5) {
                            echo ' and ' . (count($pageTypeNames) - 5) . ' more';
                        }
                        ?>.</p>
                        
                        <p><small class="text-muted"><?php echo t('You can reference specific page types in your rules to control link selection behavior.'); ?></small></p>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="restoreDefaultLinkRules()">
                            <i class="fas fa-undo"></i> <?php echo t('Restore Default Rules'); ?>
                        </button>
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

                        // Get the configurable welcome message prompt
                        let welcomePrompt = `<?php echo addslashes($welcomeMessagePrompt); ?>`;
                        
                        // Replace placeholders with actual values
                        welcomePrompt = welcomePrompt.replace(/{time_of_day}/g, hour < 12 ? 'morning' : hour < 17 ? 'afternoon' : 'evening');
                        welcomePrompt = welcomePrompt.replace(/{page_title}/g, pageTitle);
                        welcomePrompt = welcomePrompt.replace(/{page_url}/g, pageUrl);
                        
                        // Append essential formatting instructions
                        welcomePrompt += `<?php echo addslashes($essentialWelcomeMessageInstructions); ?>`;

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
                                    setTimeout(function () {
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
                                    setTimeout(function () {
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
                            setTimeout(function () {
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
                            setTimeout(function () {
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
                                    <p class="text-center mx-3 mb-0 color-muted">Today</p>
                                </div>
                                <div class="ai-response" id="welcome-response" style="display: none;">
                                    <img src="https://d7keiwzj12p9.cloudfront.net/avatars/katalysis-bot-icon-1748356162310.webp"
                                        alt="Katalysis Bot">
                                    <div id="welcome-message" class="font-weight-bold">Generating welcome message...</div>
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

                    <script>
                        window.katalysisAIDebugMode = <?php echo $debugMode ? 'true' : 'false'; ?>;
                        
                        // Default instructions and link selection rules
                        const defaultInstructions = `<?php echo addslashes($defaultInstructions); ?>`;
                        const defaultLinkRules = `<?php echo addslashes($defaultLinkSelectionRules); ?>`;
                        const defaultWelcomePrompt = `<?php echo addslashes($defaultWelcomeMessagePrompt); ?>`;
                        
                        // Function to restore default instructions
                        function restoreDefaultInstructions() {
                            if (confirm('<?php echo t('Are you sure you want to restore the default instructions? This will replace your current instructions.'); ?>')) {
                                document.getElementById('instructions').value = defaultInstructions;
                                updateInstructionsModifiedStatus();
                            }
                        }
                        
                        // Function to restore default link selection rules
                        function restoreDefaultLinkRules() {
                            if (confirm('<?php echo t('Are you sure you want to restore the default link selection rules? This will replace your current rules.'); ?>')) {
                                document.getElementById('link_selection_rules').value = defaultLinkRules;
                                updateRulesModifiedStatus();
                            }
                        }

                        // Function to restore default welcome prompt
                        function restoreDefaultWelcomePrompt() {
                            if (confirm('<?php echo t('Are you sure you want to restore the default welcome message prompt? This will replace your current prompt.'); ?>')) {
                                document.getElementById('welcome_message_prompt').value = defaultWelcomePrompt;
                                updateWelcomePromptModifiedStatus();
                            }
                        }
                        
                        // Function to check if instructions have been modified from default
                        function updateInstructionsModifiedStatus() {
                            const currentInstructions = document.getElementById('instructions').value;
                            const isModified = currentInstructions.trim() !== defaultInstructions.trim();
                            
                            const restoreButton = document.querySelector('button[onclick="restoreDefaultInstructions()"]');
                            if (restoreButton) {
                                if (isModified) {
                                    restoreButton.classList.remove('btn-outline-secondary');
                                    restoreButton.classList.add('btn-warning');
                                    restoreButton.disabled = false;
                                    restoreButton.innerHTML = '<i class="fas fa-undo"></i> <?php echo t('Restore Default Instructions'); ?> <span class="badge bg-warning text-dark">Modified</span>';
                                } else {
                                    restoreButton.classList.remove('btn-warning');
                                    restoreButton.classList.add('btn-outline-secondary');
                                    restoreButton.disabled = true;
                                    restoreButton.innerHTML = '<i class="fas fa-undo"></i> <?php echo t('Restore Default Instructions'); ?>';
                                }
                            }
                        }
                        
                        // Function to check if rules have been modified from default
                        function updateRulesModifiedStatus() {
                            const currentRules = document.getElementById('link_selection_rules').value;
                            const isModified = currentRules.trim() !== defaultLinkRules.trim();
                            
                            const restoreButton = document.querySelector('button[onclick="restoreDefaultLinkRules()"]');
                            if (restoreButton) {
                                if (isModified) {
                                    restoreButton.classList.remove('btn-outline-secondary');
                                    restoreButton.classList.add('btn-warning');
                                    restoreButton.disabled = false;
                                    restoreButton.innerHTML = '<i class="fas fa-undo"></i> <?php echo t('Restore Default Rules'); ?> <span class="badge bg-warning text-dark">Modified</span>';
                                } else {
                                    restoreButton.classList.remove('btn-warning');
                                    restoreButton.classList.add('btn-outline-secondary');
                                    restoreButton.disabled = true;
                                    restoreButton.innerHTML = '<i class="fas fa-undo"></i> <?php echo t('Restore Default Rules'); ?>';
                                }
                            }
                        }

                        // Function to check if welcome prompt has been modified from default
                        function updateWelcomePromptModifiedStatus() {
                            const currentPrompt = document.getElementById('welcome_message_prompt').value;
                            const isModified = currentPrompt.trim() !== defaultWelcomePrompt.trim();

                            const restoreButton = document.querySelector('button[onclick="restoreDefaultWelcomePrompt()"]');
                            if (restoreButton) {
                                if (isModified) {
                                    restoreButton.classList.remove('btn-outline-secondary');
                                    restoreButton.classList.add('btn-warning');
                                    restoreButton.disabled = false;
                                    restoreButton.innerHTML = '<i class="fas fa-undo"></i> <?php echo t('Restore Default Prompt'); ?> <span class="badge bg-warning text-dark">Modified</span>';
                                } else {
                                    restoreButton.classList.remove('btn-warning');
                                    restoreButton.classList.add('btn-outline-secondary');
                                    restoreButton.disabled = true;
                                    restoreButton.innerHTML = '<i class="fas fa-undo"></i> <?php echo t('Restore Default Prompt'); ?>';
                                }
                            }
                        }
                        
                        // Check modification status on page load and when textarea changes
                        document.addEventListener('DOMContentLoaded', function() {
                            // Handle instructions textarea
                            const instructionsTextarea = document.getElementById('instructions');
                            if (instructionsTextarea) {
                                updateInstructionsModifiedStatus();
                                instructionsTextarea.addEventListener('input', updateInstructionsModifiedStatus);
                            }
                            
                            // Handle link selection rules textarea
                            const rulesTextarea = document.getElementById('link_selection_rules');
                            if (rulesTextarea) {
                                updateRulesModifiedStatus();
                                rulesTextarea.addEventListener('input', updateRulesModifiedStatus);
                            }

                            // Handle welcome message prompt textarea
                            const welcomePromptTextarea = document.getElementById('welcome_message_prompt');
                            if (welcomePromptTextarea) {
                                updateWelcomePromptModifiedStatus();
                                welcomePromptTextarea.addEventListener('input', updateWelcomePromptModifiedStatus);
                            }
                        });
                    </script>

                    <!-- Debug Context Fields -->
                    <fieldset class="mb-4" id="debugContextFields"
                        style="<?php echo $debugMode ? '' : 'display: none;'; ?>">
                        <legend><?php echo t('Debug Context Fields'); ?></legend>

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
                        <div class="alert alert-info">
                            <?php echo t('These fields allow you to test the AI with specific page context. They will be used when sending messages in debug mode.'); ?>
                        </div>

                    </fieldset>

                    <script>
                        window.katalysisAIDebugMode = <?php echo $debugMode ? 'true' : 'false'; ?>;

                        // Toggle debug context fields visibility
                        document.addEventListener('DOMContentLoaded', function () {
                            const debugModeCheckbox = document.getElementById('debug_mode');
                            const debugContextFields = document.getElementById('debugContextFields');

                            if (debugModeCheckbox && debugContextFields) {
                                debugModeCheckbox.addEventListener('change', function () {
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
