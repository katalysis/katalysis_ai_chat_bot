<?php
/**
 * Example: Frontend Chat Widget with Page Type Context
 * 
 * This example shows how to implement a chat widget that automatically
 * detects the current page context and passes it to the AI system.
 */

// Get current page information
$page = Page::getCurrentPage();
$pageType = $page->getPageTypeHandle();
$pageTitle = $page->getCollectionName();
$pageUrl = $page->getCollectionLink();

// Get CSRF token for AJAX requests
$token = $this->app->make('token');
?>

<!-- Chat Widget HTML -->
<div id="chat-widget" class="chat-widget">
    <div class="chat-header">
        <h4>Chat with us</h4>
        <button id="chat-toggle" class="btn btn-primary">Start Chat</button>
    </div>
    
    <div id="chat-container" class="chat-container" style="display: none;">
        <div id="chat-messages" class="chat-messages"></div>
        
        <div class="chat-input">
            <input type="text" id="chat-input" placeholder="Type your message..." />
            <button id="chat-send" class="btn btn-primary">Send</button>
        </div>
    </div>
</div>

<!-- Page Context Script -->
<script>
// Pass page context to JavaScript
window.currentPage = {
    page_type: '<?php echo addslashes($pageType); ?>',
    page_title: '<?php echo addslashes($pageTitle); ?>',
    page_url: '<?php echo addslashes($pageUrl); ?>'
};

console.log('Page context loaded:', window.currentPage);
</script>

<!-- Chat Widget JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatToggle = document.getElementById('chat-toggle');
    const chatContainer = document.getElementById('chat-container');
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');
    
    // Toggle chat visibility
    chatToggle.addEventListener('click', function() {
        if (chatContainer.style.display === 'none') {
            chatContainer.style.display = 'block';
            chatToggle.textContent = 'Close Chat';
            
            // Add welcome message with page context
            addMessage('ai', `Hi! I see you're on our ${window.currentPage.page_title} page. How can I help you today?`);
        } else {
            chatContainer.style.display = 'none';
            chatToggle.textContent = 'Start Chat';
        }
    });
    
    // Send message
    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add user message to chat
        addMessage('user', message);
        chatInput.value = '';
        
        // Show loading indicator
        addMessage('ai', 'Thinking...', 'loading');
        
        // Send to AI with page context
        fetch('/dashboard/katalysis_ai_chat_bot/chat_bot_settings/ask_ai', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo $token->generate('ai.settings'); ?>'
            },
            body: JSON.stringify({
                message: message,
                mode: 'rag',
                page_type: window.currentPage.page_type,
                page_title: window.currentPage.page_title,
                page_url: window.currentPage.page_url
            })
        })
        .then(response => response.json())
        .then(data => {
            // Remove loading message
            const loadingMessage = chatMessages.querySelector('.loading');
            if (loadingMessage) {
                loadingMessage.remove();
            }
            
            // Add AI response
            if (data.error) {
                addMessage('ai', 'Sorry, I encountered an error. Please try again.');
            } else {
                addMessage('ai', data.content);
                
                // Add "More Info" links if available
                if (data.metadata && data.metadata.length > 0) {
                    let linksHtml = '<div class="more-info"><strong>More Information:</strong><ul>';
                    data.metadata.forEach(link => {
                        linksHtml += `<li><a href="${link.url}" target="_blank">${link.title}</a></li>`;
                    });
                    linksHtml += '</ul></div>';
                    addMessage('ai', linksHtml, 'links');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Remove loading message
            const loadingMessage = chatMessages.querySelector('.loading');
            if (loadingMessage) {
                loadingMessage.remove();
            }
            
            addMessage('ai', 'Sorry, I encountered an error. Please try again.');
        });
    }
    
    // Add message to chat
    function addMessage(type, content, className = '') {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${type}-message ${className}`;
        messageDiv.innerHTML = content;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Event listeners
    chatSend.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
});
</script>

<!-- Chat Widget CSS -->
<style>
.chat-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 350px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
}

.chat-header {
    padding: 15px;
    background: #f8f9fa;
    border-bottom: 1px solid #ddd;
    border-radius: 8px 8px 0 0;
}

.chat-header h4 {
    margin: 0 0 10px 0;
    font-size: 16px;
}

.chat-container {
    height: 400px;
    display: flex;
    flex-direction: column;
}

.chat-messages {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    max-height: 300px;
}

.chat-message {
    margin-bottom: 10px;
    padding: 8px 12px;
    border-radius: 8px;
    max-width: 80%;
}

.user-message {
    background: #007bff;
    color: white;
    margin-left: auto;
}

.ai-message {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
}

.loading {
    font-style: italic;
    color: #6c757d;
}

.links ul {
    margin: 5px 0;
    padding-left: 20px;
}

.links a {
    color: #007bff;
    text-decoration: none;
}

.links a:hover {
    text-decoration: underline;
}

.chat-input {
    padding: 15px;
    border-top: 1px solid #ddd;
    display: flex;
    gap: 10px;
}

.chat-input input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.chat-input button {
    padding: 8px 16px;
}
</style>

<?php
/**
 * Example Usage Instructions:
 * 
 * 1. Include this file in your page template or view
 * 2. The widget will automatically detect the current page context
 * 3. When users chat, the AI will receive page type, title, and URL
 * 4. Configure your AI instructions in the dashboard to use the placeholders:
 *    - {page_type} - Current page type handle
 *    - {page_title} - Current page title
 *    - {page_url} - Current page URL
 * 
 * Example Instructions for Location Pages:
 * "If the page type is 'location', mention that we are based in your local area 
 *  and can provide on-site services. Always include local contact information."
 * 
 * Example Instructions for Service Pages:
 * "If the page type is 'service', focus on the specific service mentioned in 
 *  {page_title} and provide detailed information about our expertise in this area."
 */
?> 