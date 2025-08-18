<?php
defined('C5_EXECUTE') or die("Access Denied.");

// Only show if AI is configured
if (empty($openaiKey) || empty($openaiModel)) {
    return;
}

$blockID = $b->getBlockID();
$uniqueID = 'chatbot-' . $blockID;
?>

<div id="<?php echo $uniqueID; ?>" class="katalysis-ai-chatbot-block" 
     data-position="<?php echo htmlspecialchars($chatbotPosition ?? 'bottom-right'); ?>"
     data-theme="<?php echo htmlspecialchars($theme ?? 'light'); ?>">
    
    
    <div class="chatbot-container">
        <div class="chatbot-toggle" onclick="toggleChatbot('<?php echo $uniqueID; ?>')">
            <i class="fa fa-comments"></i>
            <span class="toggle-text"><?php echo t('Chat with us'); ?></span>
        </div>
        
        <div class="chatbot-interface" style="display: none;">
            <div class="chatbot-header">
                <div class="chatbot-header-title">
                    <i class="fa fa-robot"></i> <span id="<?php echo $uniqueID; ?>-ai-header-greeting" class="ai-header-greeting"><?php echo t('AI Assistant'); ?></span>
                </div>
                <div class="chatbot-header-actions">
                    <button class="chatbot-clear" onclick="clearChatHistory('<?php echo $uniqueID; ?>')" title="<?php echo t('Clear Chat'); ?>">
                        <i class="fa fa-trash"></i>
                    </button>
                    <button class="chatbot-close" onclick="toggleChatbot('<?php echo $uniqueID; ?>')">
                        <i class="fa fa-chevron-down"></i>
                    </button>
                </div>
            </div>
            
            <div class="chatbot-messages" id="<?php echo $uniqueID; ?>-messages">
                <!-- Messages will be populated here -->
            </div>
            
            <div class="chatbot-input">
                <input type="text" class="chatbot-input-field" 
                       id="<?php echo $uniqueID; ?>-input" 
                       placeholder="<?php echo t('Type your message...'); ?>"
                       onkeypress="handleChatInput(event, '<?php echo $uniqueID; ?>')">
                <button class="chatbot-send-btn" 
                        onclick="sendChatMessage('<?php echo $uniqueID; ?>')">
                    <i class="fa fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize chatbot when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeChatbot('<?php echo $uniqueID; ?>', {
        pageTitle: <?php echo json_encode($pageTitle ?? ''); ?>,
        pageUrl: <?php echo json_encode($pageUrl ?? ''); ?>,
        pageType: <?php echo json_encode($pageType ?? ''); ?>,
        welcomePrompt: <?php echo json_encode($welcomePrompt ?? ''); ?>
    });
});

function initializeChatbot(chatbotId, config) {
    // Store config globally
    window.chatbotConfigs = window.chatbotConfigs || {};
    window.chatbotConfigs[chatbotId] = config;
    
    // Check if we have an existing chat session for this user across all pages
    let sessionId = localStorage.getItem('chatbot_global_session_id');
    let sessionTimestamp = localStorage.getItem('chatbot_global_session_timestamp');
    
    // Check if session has expired (24 hours)
    const sessionExpiry = 24 * 60 * 60 * 1000; // 24 hours in milliseconds
    const now = Date.now();
    
    if (sessionId && sessionTimestamp && (now - parseInt(sessionTimestamp)) > sessionExpiry) {
        // Session expired, clear it and start fresh
        console.log('Session expired, starting fresh');
        localStorage.removeItem('chatbot_global_session_id');
        localStorage.removeItem(`chatbot_chat_id_${sessionId}`);
        localStorage.removeItem(`chatbot_global_history_${sessionId}`);
        sessionId = null;
        sessionTimestamp = null;
    }
    
    if (!sessionId) {
        // Generate a new global session ID if none exists
        sessionId = generateSessionId();
        localStorage.setItem('chatbot_global_session_id', sessionId);
        localStorage.setItem('chatbot_global_session_timestamp', now.toString());
    }
    
    // Check if we have an existing chat ID for this global session
    const existingChatId = localStorage.getItem(`chatbot_chat_id_${sessionId}`);
    
    // Set the session ID and existing chat ID in the config
    window.chatbotConfigs[chatbotId].sessionId = sessionId;
    if (existingChatId) {
        window.chatbotConfigs[chatbotId].existingChatId = parseInt(existingChatId);
        console.log('Restored existing chat ID:', existingChatId, 'for session:', sessionId);
    }
    
    // Add page unload listener to log conversation when user leaves
    window.addEventListener('beforeunload', () => {
        logCompleteConversationToDatabase(chatbotId);
    });
    
    // Load chat history first
    const hasHistory = loadChatHistory(chatbotId);
    
    // Try to restore welcome message from separate storage
    const savedWelcomeMessage = localStorage.getItem(`chatbot_welcome_${chatbotId}`);
    if (savedWelcomeMessage) {
        console.log('Found saved welcome message:', savedWelcomeMessage);
        const cleanHeaderText = cleanTextForHeader(savedWelcomeMessage);
        console.log('Clean header text:', cleanHeaderText);
        updateAIHeaderGreeting(chatbotId, cleanHeaderText);
    } else {
        console.log('No saved welcome message found, using default');
        // Set default header greeting until welcome message is generated
        updateAIHeaderGreeting(chatbotId, 'AI Assistant');
    }
    
    // Determine initial interface state based on existing chat and user preference
    const isMinimized = localStorage.getItem(`chatbot_minimized_${chatbotId}`) === 'true';
    
    if (isMinimized) {
        // User has minimized the chat, show button only
        showChatButton(chatbotId);
    } else if (hasHistory && existingChatId) {
        // Check if there are actual user messages (not just system messages)
        const messagesContainer = document.getElementById(`${chatbotId}-messages`);
        const hasUserMessages = messagesContainer && messagesContainer.querySelectorAll('.chatbot-message-user').length > 0;
        
        if (hasUserMessages) {
            // Existing conversation with user messages - show open chat interface
            showOpenChatInterface(chatbotId);
        } else {
            // Existing chat ID but no user messages - show welcome interface
            showWelcomeInterface(chatbotId);
            
            // Generate welcome message if not already generated
            if (!savedWelcomeMessage) {
                setTimeout(() => {
                    generateWelcomeMessage(chatbotId, config);
                }, 100);
            }
        }
    } else {
        // New conversation - show button initially, then welcome message and input
        showChatButton(chatbotId);
        
        // Generate welcome message and show input field
        setTimeout(() => {
            generateWelcomeMessage(chatbotId, config);
        }, 100);
    }
}

/**
 * Generate a unique session ID for tracking individual chat conversations
 */
function generateSessionId() {
    return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

function toggleChatbot(chatbotId) {
    const interface = document.querySelector(`#${chatbotId} .chatbot-interface`);
    const toggle = document.querySelector(`#${chatbotId} .chatbot-toggle`);
    
    if (interface.style.display === 'none') {
        // Opening the chat - use the new logic
        handleChatButtonClick(chatbotId);
        
        // Save that chat is not minimized
        localStorage.setItem(`chatbot_minimized_${chatbotId}`, 'false');
    } else {
        // Minimizing the chat
        interface.style.display = 'none';
        toggle.style.display = 'block';
        
        // Save that chat is minimized
        localStorage.setItem(`chatbot_minimized_${chatbotId}`, 'true');
    }
}

function handleChatInput(event, chatbotId) {
    if (event.key === 'Enter') {
        // Check if this is the first message in a new conversation
        const config = window.chatbotConfigs[chatbotId];
        const messagesContainer = document.getElementById(`${chatbotId}-messages`);
        const hasExistingMessages = messagesContainer && messagesContainer.children.length > 0;
        
        if (!hasExistingMessages && config && !config.existingChatId) {
            // First message in new conversation, ensure welcome interface is shown
            showWelcomeInterface(chatbotId);
        }
        
        sendChatMessage(chatbotId);
    }
}

function sendChatMessage(chatbotId) {
    const input = document.getElementById(`${chatbotId}-input`);
    const message = input.value.trim();
    
    if (!message) return;
    
    // Check if this is the first message in a new conversation
    const config = window.chatbotConfigs[chatbotId];
    const messagesContainer = document.getElementById(`${chatbotId}-messages`);
    const hasExistingMessages = messagesContainer && messagesContainer.children.length > 0;
    
    if (!hasExistingMessages && config && !config.existingChatId) {
        // First message in new conversation, ensure welcome interface is shown
        showWelcomeInterface(chatbotId);
    }
    
    // Add user message
    addChatMessage(chatbotId, message, 'user');
    input.value = '';
    
    // Send to AI
    sendToAI(chatbotId, message);
}

function addChatMessage(chatbotId, message, sender) {
    const messagesContainer = document.getElementById(`${chatbotId}-messages`);
    const messageDiv = document.createElement('div');
    messageDiv.className = `chatbot-message chatbot-message-${sender}`;
    
    const icon = sender === 'user' ? 'fa-user' : 'fa-robot';
    
    // Check if message contains HTML tags
    const containsHTML = /<[^>]*>/g.test(message);
    
    if (containsHTML) {
        // For HTML content (like AI responses with buttons), render as HTML
        messageDiv.innerHTML = `
            <div class="message-content">
                <i class="fa ${icon}"></i>
                <span>${message}</span>
            </div>
        `;
    } else {
        // For plain text (like user messages), escape HTML for security
        messageDiv.innerHTML = `
            <div class="message-content">
                <i class="fa ${icon}"></i>
                <span>${escapeHtml(message)}</span>
            </div>
        `;
    }
    
    messagesContainer.appendChild(messageDiv);
    
    // Show messages container when first message is added
    if (!messagesContainer.classList.contains('has-messages')) {
        messagesContainer.classList.add('has-messages');
    }
    
    // Ensure the messages container is visible
    messagesContainer.style.display = 'block';
    messagesContainer.style.opacity = '1';
    
    // Save to localStorage only (don't log to database yet)
    saveChatHistoryToLocalStorage(chatbotId);
    
    // Scroll to bottom to show the full last message
    setTimeout(() => {
        scrollToBottom(chatbotId);
    }, 10);
}

function saveChatHistory(chatbotId) {
    try {
        const messagesContainer = document.getElementById(`${chatbotId}-messages`);
        const messages = messagesContainer.querySelectorAll('.chatbot-message:not(.typing-indicator)');
        
        const chatHistory = [];
        messages.forEach(message => {
            const sender = message.classList.contains('chatbot-message-user') ? 'user' : 'ai';
            const content = message.querySelector('.message-content span').innerHTML;
            
            // Filter out welcome messages to prevent them from being stored
            const isWelcomeMessage = sender === 'ai' && 
                (content.includes('Welcome') || 
                 content.includes('Hello') || 
                 content.includes('How can I help') ||
                 content.includes('How can we help'));
            
            if (!isWelcomeMessage) {
                chatHistory.push({ sender, content, timestamp: Date.now() });
            }
        });
        
        // Save to localStorage
        localStorage.setItem(`chatbot_history_${chatbotId}`, JSON.stringify(chatHistory));
        
        // Handle database logging based on whether this is a new session or existing one
        const config = window.chatbotConfigs[chatbotId];
        if (config) {
            console.log('Current config state:', config);
            console.log('Session ID:', config.sessionId);
            console.log('Existing Chat ID:', config.existingChatId);
            
            // Check if we already have a chat record for this session
            const existingChatId = config.existingChatId;
            
            // Check if we're currently in the process of creating a chat record
            if (config.isCreatingChat) {
                console.log('Chat creation in progress, skipping database logging');
                return;
            }
            
            if (existingChatId) {
                // Update existing chat record
                console.log('Updating existing chat record with ID:', existingChatId);
                updateChatInDatabase(chatbotId, chatHistory);
            } else {
                // Create new chat record for this session
                console.log('Creating new chat record for session:', config.sessionId);
                config.isCreatingChat = true; // Set flag to prevent multiple calls
                logChatToDatabase(chatbotId, chatHistory);
            }
        } else {
            console.error('No config found for chatbot:', chatbotId);
        }
        
    } catch (error) {
        console.error('Error saving chat history:', error);
    }
}

/**
 * Save chat history to localStorage only (no database logging)
 */
function saveChatHistoryToLocalStorage(chatbotId) {
    try {
        const messagesContainer = document.getElementById(`${chatbotId}-messages`);
        const messages = messagesContainer.querySelectorAll('.chatbot-message:not(.typing-indicator)');
        
        const chatHistory = [];
        messages.forEach(message => {
            const sender = message.classList.contains('chatbot-message-user') ? 'user' : 'ai';
            const content = message.querySelector('.message-content span').innerHTML;
            
            // Filter out welcome messages to prevent them from being stored
            const isWelcomeMessage = sender === 'ai' && 
                (content.includes('Welcome') || 
                 content.includes('Hello') || 
                 content.includes('How can I help') ||
                 content.includes('How can we help'));
            
            if (!isWelcomeMessage) {
                chatHistory.push({ sender, content, timestamp: Date.now() });
            }
        });
        
        // Save to both local and global storage for persistence across page navigation
        localStorage.setItem(`chatbot_history_${chatbotId}`, JSON.stringify(chatHistory));
        
        // Also save to global session storage
        const config = window.chatbotConfigs[chatbotId];
        if (config && config.sessionId) {
            const globalHistoryKey = `chatbot_global_history_${config.sessionId}`;
            localStorage.setItem(globalHistoryKey, JSON.stringify(chatHistory));
            console.log('Saved chat history to global session:', config.sessionId);
        }
        
    } catch (error) {
        console.error('Error saving chat history to localStorage:', error);
    }
}

/**
 * Log the complete conversation to database (called when conversation ends or explicitly requested)
 */
function logCompleteConversationToDatabase(chatbotId) {
    try {
        const messagesContainer = document.getElementById(`${chatbotId}-messages`);
        const messages = messagesContainer.querySelectorAll('.chatbot-message:not(.typing-indicator)');
        
        const chatHistory = [];
        messages.forEach(message => {
            const sender = message.classList.contains('chatbot-message-user') ? 'user' : 'ai';
            const content = message.querySelector('.message-content span').innerHTML;
            chatHistory.push({ sender, content, timestamp: Date.now() });
        });
        
        if (chatHistory.length === 0) {
            console.log('No messages to log to database');
            return;
        }
        
        // Only log to database if there are actual user messages (not just welcome messages)
        const hasUserMessages = chatHistory.some(msg => msg.sender === 'user');
        if (!hasUserMessages) {
            console.log('No user messages found, skipping database logging to prevent welcome message records');
            return;
        }
        
        // Handle database logging based on whether this is a new session or existing one
        const config = window.chatbotConfigs[chatbotId];
        if (config) {
            console.log('Current config state:', config);
            console.log('Session ID:', config.sessionId);
            console.log('Existing Chat ID:', config.existingChatId);
            
            // Check if we already have a chat record for this session
            const existingChatId = config.existingChatId;
            
            if (existingChatId) {
                // Update existing chat record
                console.log('Updating existing chat record with ID:', existingChatId);
                updateChatInDatabase(chatbotId, chatHistory);
            } else {
                // Create new chat record for this session
                console.log('Creating new chat record for session:', config.sessionId);
                logChatToDatabase(chatbotId, chatHistory);
            }
        } else {
            console.error('No config found for chatbot:', chatbotId);
        }
        
        console.log('Complete conversation logged to database successfully');
    } catch (error) {
        console.error('Error logging complete conversation to database:', error);
    }
}

function loadChatHistory(chatbotId) {
    try {
        const config = window.chatbotConfigs[chatbotId];
        if (!config || !config.sessionId) {
            console.log('No session ID available, cannot load chat history');
            return false;
        }
        
        // First try to load from global session storage
        const globalHistoryKey = `chatbot_global_history_${config.sessionId}`;
        let savedHistory = localStorage.getItem(globalHistoryKey);
        
        // Fallback to page-specific history if no global history exists
        if (!savedHistory) {
            savedHistory = localStorage.getItem(`chatbot_history_${chatbotId}`);
        }
        
        if (savedHistory) {
            const chatHistory = JSON.parse(savedHistory);
            const messagesContainer = document.getElementById(`${chatbotId}-messages`);
            
            console.log('Loading chat history:', chatHistory);
            console.log('Number of messages:', chatHistory.length);
            
            // Filter out welcome messages - only show actual conversation messages
            const conversationMessages = chatHistory.filter(msg => {
                // Skip messages that look like welcome messages
                const isWelcomeMessage = msg.sender === 'ai' && 
                    (msg.content.includes('Welcome') || 
                     msg.content.includes('Hello') || 
                     msg.content.includes('How can I help') ||
                     msg.content.includes('How can we help'));
                return !isWelcomeMessage;
            });
            
            console.log('Filtered conversation messages:', conversationMessages.length);
            console.log('User messages:', conversationMessages.filter(msg => msg.sender === 'user').length);
            console.log('AI messages:', conversationMessages.filter(msg => msg.sender === 'ai').length);
            
            // Clear existing messages
            messagesContainer.innerHTML = '';
            
            // Only restore actual conversation messages, not welcome messages
            if (conversationMessages.length > 0) {
                conversationMessages.forEach(msg => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `chatbot-message chatbot-message-${msg.sender}`;
                    
                    const icon = msg.sender === 'user' ? 'fa-user' : 'fa-robot';
                    const containsHTML = /<[^>]*>/g.test(msg.content);
                    
                    if (containsHTML) {
                        messageDiv.innerHTML = `
                            <div class="message-content">
                                <i class="fa ${icon}"></i>
                                <span>${msg.content}</span>
                            </div>
                        `;
                    } else {
                        messageDiv.innerHTML = `
                            <div class="message-content">
                                <i class="fa ${icon}"></i>
                                <span>${escapeHtml(msg.content)}</span>
                            </div>
                        `;
                    }
                    
                    messagesContainer.appendChild(messageDiv);
                });
                
                // Show messages container only if there are actual conversation messages
                const hasUserMessages = conversationMessages.some(msg => msg.sender === 'user');
                if (hasUserMessages) {
                    messagesContainer.classList.add('has-messages');
                }
                
                // Scroll to bottom
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                
                // Ensure we're at the very bottom to show the full last message
                setTimeout(() => {
                    scrollToBottom(chatbotId);
                }, 10);
                
                console.log('Loaded conversation messages from global session:', config.sessionId);
                
                // Only show open chat interface if there are actual user messages
                if (hasUserMessages) {
                    const isMinimized = localStorage.getItem(`chatbot_minimized_${chatbotId}`) === 'true';
                    if (!isMinimized) {
                        showOpenChatInterface(chatbotId);
                    }
                }
            }
            
            // Return true if we had any messages (even if filtered out)
            return chatHistory.length > 0;
        }
        return false; // Indicate that no history was loaded
    } catch (error) {
        console.error('Error loading chat history:', error);
        return false; // Indicate that history loading failed
    }
}

function logChatToDatabase(chatbotId, chatHistory) {
    const config = window.chatbotConfigs[chatbotId];
    
    // Prepare chat data for database logging
    const chatData = {
        chatbot_id: chatbotId,
        session_id: config.sessionId || 'unknown',
        page_title: config.pageTitle || '',
        page_url: config.pageUrl || '',
        page_type: config.pageType || '',
        messages: chatHistory,
        timestamp: Date.now()
    };
    
    // Send to backend endpoint for database logging
    fetch('/index.php/dashboard/katalysis_ai_chat_bot/chat_bot_settings/log_chat/', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(chatData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Chat logged to database successfully');
            // Get chat ID directly from response
            if (data.chat_id) {
                const chatId = parseInt(data.chat_id);
                const config = window.chatbotConfigs[chatbotId];
                
                // Store chat ID in both config and localStorage for persistence across page navigation
                config.existingChatId = chatId;
                config.isCreatingChat = false; // Clear the flag
                
                // Store chat ID in localStorage using session ID as key
                if (config.sessionId) {
                    localStorage.setItem(`chatbot_chat_id_${config.sessionId}`, chatId.toString());
                    console.log('Stored chat ID in localStorage:', chatId, 'for session:', config.sessionId);
                }
                
                console.log('Stored chat ID:', chatId, 'for chatbot:', chatbotId);
                console.log('Config after storing chat ID:', config);
                
                // Verify the chat ID is stored correctly
                setTimeout(() => {
                    const currentConfig = window.chatbotConfigs[chatbotId];
                    console.log('Verification - Config after timeout:', currentConfig);
                    console.log('Verification - existingChatId:', currentConfig.existingChatId);
                    console.log('Verification - sessionId:', currentConfig.sessionId);
                }, 100);
            } else {
                console.warn('No chat_id in response:', data);
                window.chatbotConfigs[chatbotId].isCreatingChat = false; // Clear the flag on error too
            }
        } else {
            console.warn('Failed to log chat to database:', data.error);
            window.chatbotConfigs[chatbotId].isCreatingChat = false; // Clear the flag on error
        }
    })
    .catch(error => {
        console.error('Error logging chat to database:', error);
        // Clear the flag on error
        if (window.chatbotConfigs[chatbotId]) {
            window.chatbotConfigs[chatbotId].isCreatingChat = false;
        }
    });
}

/**
 * Update existing chat record in database with new messages
 */
function updateChatInDatabase(chatbotId, chatHistory) {
    const config = window.chatbotConfigs[chatbotId];
    
    if (!config || !config.existingChatId) {
        console.error('Cannot update chat: no existing chat ID found for chatbot:', chatbotId);
        console.log('Config:', config);
        return;
    }
    
    console.log('Updating chat in database with ID:', config.existingChatId);
    console.log('Chat history length:', chatHistory.length);
    
    // Prepare chat data for updating
    const chatData = {
        chat_id: config.existingChatId,
        messages: chatHistory,
        timestamp: Date.now()
    };
    
    // Send to backend endpoint for updating chat
    fetch('/index.php/dashboard/katalysis_ai_chat_bot/chat_bot_settings/update_chat/', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(chatData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Chat updated in database successfully');
        } else {
            console.warn('Failed to update chat in database:', data.error);
        }
    })
    .catch(error => {
        console.error('Error updating chat in database:', error);
    });
}

function clearChatHistory(chatbotId) {
    if (confirm('Are you sure you want to clear the chat history? This cannot be undone.')) {
        try {
            // Clear from localStorage
            localStorage.removeItem(`chatbot_history_${chatbotId}`);
            localStorage.removeItem(`chatbot_welcome_${chatbotId}`);
            
            // Clear global session storage
            const config = window.chatbotConfigs[chatbotId];
            if (config && config.sessionId) {
                localStorage.removeItem(`chatbot_global_history_${config.sessionId}`);
                localStorage.removeItem(`chatbot_chat_id_${config.sessionId}`);
            }
            
            // Clear global session data
            localStorage.removeItem('chatbot_global_session_id');
            localStorage.removeItem('chatbot_global_session_timestamp');
            
            // Clear from display
            const messagesContainer = document.getElementById(`${chatbotId}-messages`);
            if (messagesContainer) {
                messagesContainer.innerHTML = '';
                messagesContainer.classList.remove('has-messages'); // Ensure class is removed
            }
            
            // Generate a new session ID for the new conversation
            const newSessionId = generateSessionId();
            window.chatbotConfigs[chatbotId].sessionId = newSessionId;
            
            // Store the new session ID globally
            localStorage.setItem('chatbot_global_session_id', newSessionId);
            localStorage.setItem('chatbot_global_session_timestamp', Date.now().toString());
            
            // Clear the old chat ID from localStorage
            const oldSessionId = localStorage.getItem('chatbot_global_session_id');
            if (oldSessionId) {
                localStorage.removeItem(`chatbot_chat_id_${oldSessionId}`);
            }
            
            // Reset the existing chat ID to force creation of new chat record
            window.chatbotConfigs[chatbotId].existingChatId = null;
            
            // Clear any creation flags
            window.chatbotConfigs[chatbotId].isCreatingChat = false;
            
            // Reset header greeting to default temporarily
            updateAIHeaderGreeting(chatbotId, 'AI Assistant');
            
            // Reset minimized state and show welcome interface
            localStorage.setItem(`chatbot_minimized_${chatbotId}`, 'false');
            showWelcomeInterface(chatbotId);
            
            // Generate new welcome message for fresh start
            const currentConfig = window.chatbotConfigs[chatbotId];
            if (currentConfig) {
                setTimeout(() => {
                    generateWelcomeMessage(chatbotId, currentConfig);
                }, 100);
            }
            // No fallback message needed - welcome message will be generated for header
            
            console.log('Chat history cleared successfully, new session started');
        } catch (error) {
            console.error('Error clearing chat history:', error);
        }
    }
}

function sendToAI(chatbotId, message) {
    const config = window.chatbotConfigs[chatbotId];
    
    // Show typing indicator (don't trigger has-messages class)
    const messagesContainer = document.getElementById(`${chatbotId}-messages`);
    const typingDiv = document.createElement('div');
    typingDiv.className = 'chatbot-message chatbot-message-ai typing-indicator';
    typingDiv.innerHTML = `
        <div class="message-content">
            <i class="fa fa-robot"></i>
            <span>...</span>
        </div>
    `;
    messagesContainer.appendChild(typingDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    fetch('/index.php/dashboard/katalysis_ai_chat_bot/chat_bot_settings/ask_ai/', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            message: message,
            mode: 'rag',
            page_type: config.pageType,
            page_title: config.pageTitle,
            page_url: config.pageUrl
        })
    })
    .then(response => response.json())
    .then(data => {
        // Remove typing indicator
        const messagesContainer = document.getElementById(`${chatbotId}-messages`);
        const typingIndicator = messagesContainer.querySelector('.typing-indicator');
        if (typingIndicator) {
            messagesContainer.removeChild(typingIndicator);
        }
        
        // Hide messages container if no messages remain
        if (messagesContainer.children.length === 0) {
            messagesContainer.classList.remove('has-messages');
        }
        
        if (data.error) {
            // Show more specific error information if available
            let errorMessage = 'Sorry, I encountered an error. Please try again.';
            
            if (data.details) {
                errorMessage = `Error: ${data.details}`;
                if (data.type) {
                    errorMessage += ` (${data.type})`;
                }
            }
            
            addChatMessage(chatbotId, errorMessage, 'ai');
        } else {
            // Create the complete response content including buttons and links
            let responseContent = data.content;
            
            // Process the response content to convert "contact us" to links
            responseContent = processAIResponseContent(responseContent);
            
            // Check if we have actions or links to add
            const hasActions = data.actions && Array.isArray(data.actions) && data.actions.length > 0;
            const hasLinks = (data.more_info_links && Array.isArray(data.more_info_links) && data.more_info_links.length > 0) || 
                             (data.metadata && Array.isArray(data.metadata) && data.metadata.length > 0);
            
            if (hasActions || hasLinks) {
                responseContent += '<div class="more-info-links">';
                
                // Add heading
                responseContent += '<strong class="more-info-header">More Information:</strong>';
                
                // Add action buttons first (if any)
                if (hasActions) {
                    data.actions.forEach(action => {
                        responseContent += `<button class="action-button" onclick="executeAction('${chatbotId}', ${action.id})">`;
                        responseContent += `<i class="${action.icon || 'fas fa-cog'}"></i> ${action.name || 'Action'}`;
                        responseContent += '</button>';
                    });
                }
                
                // Add links list (if any)
                if (hasLinks) {
                    const links = data.more_info_links || data.metadata;
                    links.forEach(link => {
                        if (link && link.url && link.title) {
                            responseContent += `<a href="${link.url}" target="_blank" class="link-button">`;
                            responseContent += '<i class="fas fa-link"></i> ' + link.title;
                            responseContent += '</a>';
                        }
                    });
                }
                
                responseContent += '</div>';
            }
            
            // Add the complete response as one message
            addChatMessage(chatbotId, responseContent, 'ai');
            
            // Ensure we scroll to bottom to show the full response with buttons/links
            setTimeout(() => {
                scrollToBottom(chatbotId);
            }, 50);
            
            // Log the complete conversation to database after AI responds
            setTimeout(() => {
                logCompleteConversationToDatabase(chatbotId);
            }, 500); // Increased delay to ensure existingChatId is set
            
            // Debug: Log the response data to see what we're getting
            console.log('AI Response Data:', data);
        }
    })
    .catch(error => {
        console.error('AI request failed:', error);
        // Store fallback welcome message separately for header restoration
        localStorage.setItem(`chatbot_welcome_${chatbotId}`, 'Hello! How can I help you today?');
        // Only update header with fallback greeting, don't add to chat
        updateAIHeaderGreeting(chatbotId, 'Hello! How can I help you today?');
        // Show welcome interface
        setTimeout(() => {
            showWelcomeInterface(chatbotId);
        }, 50);
    });
}

function generateWelcomeMessage(chatbotId, config) {
    console.log('generateWelcomeMessage called for chatbot:', chatbotId);
    console.log('Config:', config);
    
    if (!config.welcomePrompt) {
        console.log('No welcome prompt, using fallback');
        // Store fallback welcome message separately for header restoration
        localStorage.setItem(`chatbot_welcome_${chatbotId}`, 'Hello! How can I help you today?');
        // Only update header with fallback greeting, don't add to chat
        updateAIHeaderGreeting(chatbotId, 'Hello! How can I help you today?');
        // Show welcome interface even with fallback message
        setTimeout(() => {
            console.log('Showing welcome interface for fallback message');
            showWelcomeInterface(chatbotId);
        }, 50);
        return;
    }
    
    // Process placeholders in the welcome prompt
    let processedPrompt = config.welcomePrompt;
    if (config.pageTitle) {
        processedPrompt = processedPrompt.replace(/{page_title}/g, config.pageTitle);
    }
    if (config.pageUrl) {
        processedPrompt = processedPrompt.replace(/{page_url}/g, config.pageUrl);
    }
    
    // Get current time of day
    const now = new Date();
    const hour = now.getHours();
    let timeOfDay = 'morning';
    if (hour >= 12 && hour < 17) {
        timeOfDay = 'afternoon';
    } else if (hour >= 17) {
        timeOfDay = 'evening';
    }
    processedPrompt = processedPrompt.replace(/{time_of_day}/g, timeOfDay);
    
    // Now send the processed prompt to AI
    fetch('/index.php/dashboard/katalysis_ai_chat_bot/chat_bot_settings/ask_ai/', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            message: processedPrompt,
            mode: 'basic'
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('AI response data received:', data);
        if (data.error) {
            console.log('AI error, using fallback greeting');
            // Store fallback welcome message separately for header restoration
            const fallbackMessage = 'Hello! How can I help you today?';
            localStorage.setItem(`chatbot_welcome_${chatbotId}`, fallbackMessage);
            console.log('Stored fallback welcome message:', fallbackMessage);
            // Only update header with fallback greeting, don't add to chat
            updateAIHeaderGreeting(chatbotId, fallbackMessage);
            // Show welcome interface even with fallback message
            setTimeout(() => {
                console.log('Showing welcome interface for fallback message');
                showWelcomeInterface(chatbotId);
            }, 50);
        } else {
            console.log('AI response received, updating header and storing welcome message');
            console.log('AI response content:', data.content);
            // Store welcome message separately for header restoration
            localStorage.setItem(`chatbot_welcome_${chatbotId}`, data.content);
            console.log('Stored AI-generated welcome message:', data.content);
            
            // Only update header with clean text, don't add to chat area
            const cleanHeaderText = cleanTextForHeader(data.content);
            console.log('Clean header text from AI:', cleanHeaderText);
            updateAIHeaderGreeting(chatbotId, cleanHeaderText);
            
            // Show welcome interface after welcome message is generated
            setTimeout(() => {
                console.log('Showing welcome interface for AI-generated message');
                showWelcomeInterface(chatbotId);
            }, 50);
        }
    })
    .catch(error => {
        console.error('AI request failed:', error);
        // Store fallback welcome message separately for header restoration
        localStorage.setItem(`chatbot_welcome_${chatbotId}`, 'Hello! How can I help you today?');
        // Only update header with fallback greeting, don't add to chat
        updateAIHeaderGreeting(chatbotId, 'Hello! How can I help you today?');
        // Show welcome interface
        setTimeout(() => {
            showWelcomeInterface(chatbotId);
        }, 50);
    });
}

function executeAction(chatbotId, actionId) {
    const config = window.chatbotConfigs[chatbotId];
    
    fetch('/index.php/dashboard/katalysis_ai_chat_bot/chat_bot_settings/execute_action/', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action_id: actionId,
            conversation_context: 'Action button clicked from chatbot block'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            addChatMessage(chatbotId, 'Sorry, I encountered an error executing that action.', 'ai');
        } else {
            addChatMessage(chatbotId, data.response, 'ai');
        }
    })
    .catch(error => {
        addChatMessage(chatbotId, 'Sorry, I encountered an error executing that action.', 'ai');
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Function to scroll chat to bottom
function scrollToBottom(chatbotId) {
    const messagesContainer = document.getElementById(`${chatbotId}-messages`);
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}

// Function to clean text for header display (remove HTML tags and truncate)
function cleanTextForHeader(text) {
    if (!text || typeof text !== 'string') {
        return 'AI Assistant';
    }
    
    // Remove HTML tags
    let cleanText = text.replace(/<[^>]*>/g, '');
    
    // Get first sentence or first 100 characters
    if (cleanText.includes('.')) {
        cleanText = cleanText.split('.')[0] + '.';
    } else if (cleanText.length > 100) {
        cleanText = cleanText.substring(0, 100) + '...';
    }
    
    return cleanText.trim();
}

// Function to process AI response content and convert "contact us" to links
function processAIResponseContent(content) {
    if (content && typeof content === 'string') {
        // Case-insensitive replacement for "contact us" variations
        return content.replace(/\b(contact us|Contact Us|CONTACT US)\b/g, '<a href="/contact" target="_blank" class="chatbot-text-link">$1</a>');
    }
    return content;
}

function updateAIHeaderGreeting(chatbotId, greeting) {
    console.log('updateAIHeaderGreeting called for chatbot:', chatbotId);
    console.log('Greeting text:', greeting);
    
    const headerElement = document.querySelector(`#${chatbotId} .ai-header-greeting`);
    if (headerElement) {
        console.log('Header element found:', headerElement);
        console.log('Current header text:', headerElement.textContent);
        headerElement.textContent = greeting;
        console.log('Header text updated to:', headerElement.textContent);
    } else {
        console.error('Header element not found for chatbot:', chatbotId);
        console.log('Looking for selector: #${chatbotId} .ai-header-greeting');
        console.log('Available elements with ai-header-greeting class:', document.querySelectorAll('.ai-header-greeting'));
    }
}

/**
 * Show the chat button interface (initial state)
 */
function showChatButton(chatbotId) {
    const interface = document.querySelector(`#${chatbotId} .chatbot-interface`);
    const toggle = document.querySelector(`#${chatbotId} .chatbot-toggle`);
    
    if (interface && toggle) {
        interface.style.display = 'none';
        toggle.style.display = 'block';
    }
}

/**
 * Show the welcome message and input field interface
 */
function showWelcomeInterface(chatbotId) {
    const interface = document.querySelector(`#${chatbotId} .chatbot-interface`);
    const toggle = document.querySelector(`#${chatbotId} .chatbot-toggle`);
    
    if (interface && toggle) {
        interface.style.display = 'block';
        toggle.style.display = 'none';
        
        // Hide messages container since we don't want to show it for welcome interface
        const messagesContainer = document.getElementById(`${chatbotId}-messages`);
        if (messagesContainer) {
            messagesContainer.style.display = 'none';
            messagesContainer.classList.remove('has-messages');
        }
        
        console.log('Welcome interface shown for chatbot:', chatbotId);
        console.log('Interface display:', interface.style.display);
        console.log('Toggle display:', toggle.style.display);
        
        // Check if input field is visible
        const inputField = document.getElementById(`${chatbotId}-input`);
        if (inputField) {
            console.log('Input field found:', inputField);
            console.log('Input field display:', inputField.style.display);
            console.log('Input field computed display:', window.getComputedStyle(inputField).display);
        } else {
            console.log('Input field not found');
        }
    } else {
        console.error('Interface or toggle not found for chatbot:', chatbotId);
    }
}

/**
 * Show the open chat interface with existing conversation
 */
function showOpenChatInterface(chatbotId) {
    const interface = document.querySelector(`#${chatbotId} .chatbot-interface`);
    const toggle = document.querySelector(`#${chatbotId} .chatbot-toggle`);
    
    if (interface && toggle) {
        interface.style.display = 'block';
        toggle.style.display = 'none';
        
        // Ensure messages container is visible and shows messages
        const messagesContainer = document.getElementById(`${chatbotId}-messages`);
        if (messagesContainer) {
            messagesContainer.classList.add('has-messages');
            
            // Scroll to bottom to show the latest messages
            setTimeout(() => {
                scrollToBottom(chatbotId);
            }, 100);
        }
    }
}

/**
 * Handle chat button click - show appropriate interface based on current state
 */
function handleChatButtonClick(chatbotId) {
    const config = window.chatbotConfigs[chatbotId];
    const messagesContainer = document.getElementById(`${chatbotId}-messages`);
    const hasExistingMessages = messagesContainer && messagesContainer.children.length > 0;
    
    if (hasExistingMessages && config && config.existingChatId) {
        // Check if there are actual user messages (not just system messages)
        const hasUserMessages = messagesContainer.querySelectorAll('.chatbot-message-user').length > 0;
        
        if (hasUserMessages) {
            // Existing conversation with user messages - show open chat interface
            showOpenChatInterface(chatbotId);
        } else {
            // Existing chat ID but no user messages - show welcome interface
            showWelcomeInterface(chatbotId);
            
            // Check if we already have a welcome message
            const savedWelcomeMessage = localStorage.getItem(`chatbot_welcome_${chatbotId}`);
            if (savedWelcomeMessage) {
                // Welcome message already exists in header, just show the interface
                showWelcomeInterface(chatbotId);
            } else {
                // Generate welcome message if not already generated
                setTimeout(() => {
                    generateWelcomeMessage(chatbotId, config);
                }, 100);
            }
        }
    } else {
        // New conversation - show welcome interface
        showWelcomeInterface(chatbotId);
        
        // Check if we already have a welcome message
        const savedWelcomeMessage = localStorage.getItem(`chatbot_welcome_${chatbotId}`);
        if (savedWelcomeMessage) {
            // Welcome message already exists in header, just show the interface
            showWelcomeInterface(chatbotId);
        } else {
            // Generate welcome message if not already generated
            setTimeout(() => {
                generateWelcomeMessage(chatbotId, config);
            }, 100);
        }
    }
}
</script>

<style>
:root {
    --chatbot-primary: #7749F8;
    --chatbot-primary-dark: #4D2DA5;
    --chatbot-secondary: #6c757d;
    --chatbot-success: #28a745;
    --chatbot-light: white;
    --chatbot-dark: #333;
    --chatbot-border: #e9ecef;
    --chatbot-shadow: rgba(0,0,0,0.1);
    --chatbot-hover-bg: rgba(255,255,255,0.2);
}


.katalysis-ai-chatbot-block {
    position: fixed;
    z-index: 1000;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    bottom: 20px;
    right: 20px;
}


.chatbot-toggle {
    background: var(--chatbot-primary);
    color: white;
    padding: 15px 20px;
    border-radius: 50px;
    cursor: pointer;
    box-shadow: 0 4px 12px var(--chatbot-shadow);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}

.chatbot-toggle:hover {
    background: var(--chatbot-primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px var(--chatbot-shadow);
}

.chatbot-interface {
    background: var(--chatbot-primary-dark);
    border-radius: 30px 30px 0 30px;
    box-shadow: 0 8px 32px var(--chatbot-shadow);
    width: 350px;
    max-height: 600px;
    display: flex;
    flex-direction: column;
    padding-bottom:10px;
}

.chatbot-header {
    background: transparent;
    color: white;
    padding: 15px;
    border-radius: 30px 30px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chatbot-header-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
}

.chatbot-header-actions {
    display: flex;
    flex-direction: column-reverse;
    gap: 10px;
}

/* Add right margin to all icons for better spacing */
.chatbot-message i,
.chatbot-input i,
.chatbot-toggle i {
    margin-right: 6px;
}

/* More Information header styling */
.more-info-header {
    display: block;
    font-size: 0.7.5rem;
    color: var(--chatbot-secondary);
    font-weight: 600;
}

.chatbot-clear {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    font-size: 18px;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background 0.2s ease;
}

.chatbot-clear i {
    color: var(--chatbot-primary);
}

.chatbot-clear:hover {
    background: var(--chatbot-hover-bg);
}

.chatbot-close {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    font-size: 18px;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background 0.2s ease;
}

.chatbot-close:hover {
    background: var(--chatbot-hover-bg);
}

.chatbot-messages {
    background: linear-gradient(180deg,
        color-mix(in srgb, var(--chatbot-primary) 10%, white),
        color-mix(in srgb, var(--chatbot-primary) 40%, white)
    );
    border-radius: 15px 15px 0 0;
    padding: 10px 10px 0 10px;
    margin: 0 10px;
    max-height: 400px;
    overflow-y: auto;
    box-shadow: 0 4px 12px var(--chatbot-shadow);
    display: none; /* Hide initially until first message */
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
}

.chatbot-messages.has-messages {
    display: block; /* Show when messages are present */
    opacity: 1;
}

.typing-indicator {
    opacity: 0.7;
    font-style: italic;
}

.typing-indicator .message-content span {
    animation: typing 1.5s infinite;
}

@keyframes typing {
    0%, 20% { opacity: 1; }
    50% { opacity: 0.3; }
    80%, 100% { opacity: 1; }
}

.chatbot-message {
    margin-bottom: 15px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 14px;
}

.chatbot-message-user {
    flex-direction: row-reverse;
}

.chatbot-message-user .message-content {
    background: var(--chatbot-primary);
    color: white;
    border-radius: 18px 18px 4px 18px;
}

.chatbot-message-ai .message-content {
    background: #f8f9fa;
    color: #333;
    border-radius: 18px 18px 18px 4px;
}

.message-content {
    padding: 12px 16px;
    max-width: 80%;
    word-wrap: break-word;
}

.chatbot-input {
    margin: 0 10px;
    padding: 10px;
    background-color: white;
    border-radius: 0 0 0 20px;
    display: flex;
    gap: 10px;
    align-items: center;
}

.chatbot-input-field {
    flex: 1;
    border: none;
    background-color: white;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 14px;
    outline: none;
}

.chatbot-input-field:focus, 
.chatbot-input-field:active, 
.chatbot-input-field:hover {
    border: none;
    background-color: white;
    box-shadow: none;
}

.chatbot-input-field::placeholder {
    color: #6c757d;
}

.chatbot-send-btn {
    height: 42px;
    width: 42px;
    border-radius: 50% !important;
    background-color: var(--chatbot-primary);
    border-color: var(--chatbot-primary);
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.chatbot-send-btn:hover {
    background-color: var(--chatbot-primary-dark);
    transform: scale(1.05);
}

.chatbot-actions {
    margin-top: 15px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.more-info-links {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}

.action-button, .link-button {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid;
    text-decoration: none;
    display: inline-block;
    font-weight: 500;
    line-height: 1.5;
}

.action-button {
    background-color: var(--chatbot-primary);
    border-color: var(--chatbot-primary);
    color: white;
}

.action-button:hover {
    background-color: var(--chatbot-primary-dark);
    border-color: var(--chatbot-primary-dark);
    transform: translateY(-1px);
}

a.link-button {
    background-color: white;
    color: var(--chatbot-primary) !important;
    border-color: var(--chatbot-primary);
}

a.link-button:hover {
    background-color: var(--chatbot-primary) !important;
    color: white !important;
    transform: translateY(-1px);
    text-decoration: none;
}

.chatbot-text-link {
    color: var(--chatbot-primary) !important;
    font-weight: bold;
    text-decoration: none;
    transition: color 0.2s ease;
}

.chatbot-text-link:hover {
    color: var(--chatbot-primary-dark) !important;
    text-decoration: underline;
}

.chatbot-title {
    text-align: center;
    margin-bottom: 15px;
    color: #333;
}

.chatbot-title h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

/* Dark theme */
.katalysis-ai-chatbot-block[data-theme="dark"] .chatbot-interface {
    background: #2d3748;
    color: white;
}

.katalysis-ai-chatbot-block[data-theme="dark"] .chatbot-message-ai .message-content {
    background: #4a5568;
    color: white;
}

.katalysis-ai-chatbot-block[data-theme="dark"] .chatbot-input {
    border-top-color: #4a5568;
}

/* Responsive */
@media (max-width: 768px) {
    .chatbot-interface {
        width: 300px;
        max-height: 400px;
    }
    
    .katalysis-ai-chatbot-block[data-position="center"] {
        position: fixed;
        top: 20px;
        left: 20px;
        right: 20px;
        transform: none;
    }
    
    .chatbot-interface {
        width: 100%;
    }
}
</style> 