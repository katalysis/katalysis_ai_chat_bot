<?php
namespace Concrete\Package\KatalysisAiChatBot\Controller\SinglePage\Dashboard\KatalysisAiChatBot;

use Core;
use Config;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\User\User;
use KatalysisAiChatBot\AiAgent;
use \NeuronAI\Chat\Messages\UserMessage;

use Concrete\Core\Http\Response;
use Concrete\Core\Http\ResponseFactory;
use Concrete\Core\Http\Request;
use KatalysisAiChatBot\RagAgent;
use Symfony\Component\HttpFoundation\JsonResponse;

class ChatBotSettings extends DashboardPageController
{

    public function view()
    {

        $this->requireAsset('css', 'katalysis-ai');
        $this->requireAsset('javascript', 'katalysis-ai');

        $this->set('token', $this->app->make('token'));
        $this->set('form', $this->app->make('helper/form'));

        $config = $this->app->make('config');
        $this->set('instructions', $config->get('katalysis.aichatbot.instructions'));
        $this->set('linkSelectionRules', $config->get('katalysis.aichatbot.link_selection_rules', $this->getDefaultLinkSelectionRules()));
        $this->set('defaultLinkSelectionRules', $this->getDefaultLinkSelectionRules());
        $this->set('defaultInstructions', $this->getDefaultInstructions());
        $this->set('welcomeMessagePrompt', $config->get('katalysis.aichatbot.welcome_message_prompt', $this->getDefaultWelcomeMessagePrompt()));
        $this->set('defaultWelcomeMessagePrompt', $this->getDefaultWelcomeMessagePrompt());
        $this->set('essentialWelcomeMessageInstructions', $this->getEssentialWelcomeMessageInstructions());
        $this->set('contactPageID', $config->get('katalysis.aichatbot.contact_page_id', null));
        
        // Get the contact page URL if a page is selected
        $contactPageUrl = '/contact-us'; // Default fallback
        if ($config->get('katalysis.aichatbot.contact_page_id')) {
            try {
                $contactPage = \Page::getByID($config->get('katalysis.aichatbot.contact_page_id'));
                if ($contactPage && !$contactPage->isError()) {
                    $contactPageUrl = $contactPage->getCollectionLink();
                }
            } catch (\Exception $e) {
                // Keep default URL if there's an error
            }
        }
        $this->set('contactPageUrl', $contactPageUrl);
        
        $this->set('debugMode', (bool) $config->get('katalysis.aichatbot.debug_mode', false));
        $this->set('debugPageTitle', $config->get('katalysis.aichatbot.debug_page_title', ''));
        $this->set('debugPageType', $config->get('katalysis.aichatbot.debug_page_type', ''));
        $this->set('debugPageUrl', $config->get('katalysis.aichatbot.debug_page_url', ''));

        // Debug: Test actions if debug mode is enabled
        $debugActions = [];
        if ($config->get('katalysis.aichatbot.debug_mode', false)) {
            try {
                $actionService = new \KatalysisAiChatBot\ActionService($this->app->make('Doctrine\ORM\EntityManager'));
                $actions = $actionService->getAllActions();
                
                foreach ($actions as $action) {
                    $debugActions[] = [
                        'id' => $action->getId(),
                        'name' => $action->getName(),
                        'icon' => $action->getIcon(),
                        'triggerInstruction' => $action->getTriggerInstruction(),
                        'responseInstruction' => $action->getResponseInstruction()
                    ];
                }
                
                // Also get the formatted prompt for debugging
                $this->set('debugActionsPrompt', $actionService->getActionsForPrompt());
                
            } catch (\Exception $e) {
                $this->set('debugActionsError', $e->getMessage());
            }
        }
        $this->set('debugActions', $debugActions);

        // Get available page types
        $pageTypes = \PageType::getList(false);
        $pageTypesList = [];
        foreach ($pageTypes as $pageType) {
            $pageTypesList[] = [
                'id' => $pageType->getPageTypeID(),
                'handle' => $pageType->getPageTypeHandle(),
                'name' => $pageType->getPageTypeDisplayName(),
                'isInternal' => $pageType->isPageTypeInternal(),
                'isFrequentlyAdded' => $pageType->isPageTypeFrequentlyAdded()
            ];
        }
        $this->set('pageTypes', $pageTypesList);

        $this->set('results', []);
    }

    public function save()
    {
        if (!$this->token->validate('ai.settings')) {
            $this->error->add($this->token->getErrorMessage());
        }
        if (!$this->error->has()) {
            $config = $this->app->make('config');
            $config->save('katalysis.aichatbot.instructions', (string) $this->post('instructions'));
            $config->save('katalysis.aichatbot.link_selection_rules', (string) $this->post('link_selection_rules'));
            $config->save('katalysis.aichatbot.welcome_message_prompt', (string) $this->post('welcome_message_prompt'));
            $config->save('katalysis.aichatbot.contact_page_id', (int) $this->post('contact_page_id'));
            $config->save('katalysis.aichatbot.debug_mode', (bool) $this->post('debug_mode'));
            $config->save('katalysis.aichatbot.debug_page_title', (string) $this->post('debug_page_title'));
            $config->save('katalysis.aichatbot.debug_page_type', (string) $this->post('debug_page_type'));
            $config->save('katalysis.aichatbot.debug_page_url', (string) $this->post('debug_page_url'));
            $this->flash('success', t('Chat bot settings have been updated.'));
        }
        return $this->buildRedirect($this->action());
    }

    /**
     * Get default instructions
     */
    private function getDefaultInstructions(): string
    {
        return "You are an expert AI sales assistant for Katalysis, a UK-based web design and development company.
You have access to indexed content from the Katalysis website and should use this information to provide accurate, contextual responses.

RESPONSE GUIDELINES:
• Keep responses concise and to the point (preferably one sentence)
• Use UK spelling: specialise, organisation, customise, optimise
• Include a call to action encouraging contact
• Include a link to the contact page when suggesting users contact us
• Be helpful and professional

EXAMPLES OF GOOD RESPONSES:
- 'Yes, we specialise in Concrete CMS hosting and would be happy to discuss your requirements.'
- 'We offer customised web development services - get in touch to learn more.'
- 'Our team can design websites for law firms - contact us for a consultation.'

AVOID:
- Long explanations or detailed feature lists
- US spelling (specialize, organization, customize, optimize)
- Responses without a call to action";
    }

    /**
     * Get default link selection rules
     */
    private function getDefaultLinkSelectionRules(): string
    {
        return "You are selecting the most relevant links for a user's question. Be very selective and only choose links that directly address the user's specific needs.

Selection Criteria (in order of priority):
1. **Direct Relevance**: Choose documents whose titles/content directly answer the user's question
2. **Specific Information**: Prefer documents that provide specific, actionable information over general pages
3. **Service Matching**: If the user asks about a specific service, prioritize pages about that service
4. **Location Context**: Only include location pages if the user specifically mentions a location
5. **Quality Over Quantity**: It's better to select 1-2 highly relevant links than 4 mediocre ones

Selection Rules:
- Select 1-3 links (prefer fewer, more relevant links)
- Avoid generic pages unless they're the only relevant option
- Don't select location pages unless location is mentioned in the question
- Prioritize pages with higher relevance scores when relevance is similar
- If no documents are truly relevant, return 'none'";
    }

    /**
     * Get essential link selection instructions that are always appended
     */
    private function getEssentialLinkSelectionInstructions(): string
    {
        return "

RESPONSE FORMAT REQUIREMENTS:
• You must respond with ONLY numbers separated by commas (e.g., '1,3,4') or 'none' if no links are relevant
• Do not include any other text, explanations, or formatting
• Do not use bullet points, dashes, or any other characters
• Maximum 4 numbers total
• Numbers must correspond to the document numbers listed above

EXAMPLES OF CORRECT RESPONSES:
- '1,3' (selects documents 1 and 3)
- '2' (selects only document 2)
- 'none' (no documents are relevant)
- '1,2,4' (selects documents 1, 2, and 4)

EXAMPLES OF INCORRECT RESPONSES:
- 'I think documents 1 and 3 would be helpful'
- '1, 3' (with spaces)
- 'Documents 1 and 3'
- '1. and 3.' (with periods)";
    }

    /**
     * Get default welcome message prompt
     */
    private function getDefaultWelcomeMessagePrompt(): string
    {
        return "Generate a short, friendly welcome message for Katalysis, a UK-based web design and development company. 

Context:
- Time of day: {time_of_day}
- Current page: {page_title}
- Page URL: {page_url}

Requirements:
- Include time-based greeting (Good morning/afternoon/evening)
- Keep it very brief (1 sentence maximum)
- Be welcoming, appreciative and professional
- End with \"How can we help?\"
- Maximum 15-20 words total";
    }

    /**
     * Get essential welcome message formatting instructions that are always appended
     */
    private function getEssentialWelcomeMessageInstructions(): string
    {
        return "

RESPONSE FORMAT REQUIREMENTS:
• Respond with ONLY the welcome message text - no additional formatting, quotes, or explanations
• Do not include phrases like \"Here's a welcome message:\" or similar introductions
• Do not use markdown formatting, bullet points, or special characters
• Keep the response as plain text only
• Do not include any meta-commentary about the message

EXAMPLES OF CORRECT RESPONSES:
- \"Good morning! Welcome to our website. How can we help?\"
- \"Good afternoon and welcome to our site. How can we assist you today?\"
- \"Good evening! Thank you for visiting us. How can we help?\"

EXAMPLES OF INCORRECT RESPONSES:
- \"Here's a welcome message: Good morning! How can we help?\"
- \"*Good morning! Welcome to our site. How can we help?*\"
- \"I'll generate a welcome message: Good morning! How can we help?\"
- \"Good morning! Welcome to our site. How can we help?\" (with quotes)";
    }

    /**
     * Get default link selection rules via AJAX
     */
    public function get_default_link_rules()
    {
        if (!$this->token->validate('ai.settings')) {
            return new JsonResponse(['error' => $this->token->getErrorMessage()], 400);
        }
        
        return new JsonResponse([
            'rules' => $this->getDefaultLinkSelectionRules()
        ]);
    }

    /**
     * AJAX method to get default welcome message prompt
     */
    public function get_default_welcome_prompt()
    {
        if (!$this->token->validate('ai.settings')) {
            return new JsonResponse(['error' => $this->token->getErrorMessage()], 400);
        }
        
        return new JsonResponse([
            'prompt' => $this->getDefaultWelcomeMessagePrompt()
        ]);
    }

    /**
     * Clear chat history files from the server
     */
    public function clear_chat_history()
    {
        try {
            $chatDirectory = DIR_APPLICATION . '/files/neuron';

            // Clear RAG chat history (key '1')
            $ragChatFile = $chatDirectory . '/1.json';
            if (file_exists($ragChatFile)) {
                unlink($ragChatFile);
            }

            // Clear basic AI chat history (key '2')
            $basicChatFile = $chatDirectory . '/2.json';
            if (file_exists($basicChatFile)) {
                unlink($basicChatFile);
            }

            // Also clear any other chat files that might exist
            $chatFiles = glob($chatDirectory . '/*.json');
            foreach ($chatFiles as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            return new JsonResponse(['success' => true, 'message' => 'Chat history cleared successfully']);

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }


    public function ask_ai()
    {
        // Initialize variables
        $message = null;
        $mode = 'rag'; // Default to RAG mode
        $isNewChat = false; // Track if this is a new chat session

        // Get the request object
        $request = $this->app->make('request');

        // Check if this is a JSON request
        $contentType = $request->headers->get('Content-Type');
        $rawContent = $request->getContent();

        // Check if content looks like JSON (starts with { or [)
        if (
            strpos($contentType, 'application/json') !== false ||
            (trim($rawContent) && (strpos(trim($rawContent), '{') === 0 || strpos(trim($rawContent), '[') === 0))
        ) {
            // Handle JSON request
            $jsonData = json_decode($rawContent, true);
            $message = $jsonData['message'] ?? null;
            $mode = $jsonData['mode'] ?? 'rag';
            $isNewChat = $jsonData['new_chat'] ?? false;
            $pageType = $jsonData['page_type'] ?? null;
            $pageTitle = $jsonData['page_title'] ?? null;
            $pageUrl = $jsonData['page_url'] ?? null;
        } else {
            // Handle form data request
            $data = $request->request->all();
            $message = $data['message'] ?? null;
            $mode = $data['mode'] ?? 'rag';
            $isNewChat = $data['new_chat'] ?? false;
            $pageType = $data['page_type'] ?? null;
            $pageTitle = $data['page_title'] ?? null;
            $pageUrl = $data['page_url'] ?? null;
        }

        // Create new chat record if this is a new chat session
        $chatId = null;
        if ($isNewChat) {
            $chatId = $this->createNewChatRecord($mode, $pageType, $pageTitle, $pageUrl);
        }

        // Update chat record with message information
        if ($chatId) {
            $this->updateChatWithMessage($chatId, $message);
        }

        // Get AI configuration
        $config = $this->app->make('config');
        $openaiKey = $config->get('katalysis.ai.open_ai_key');
        $openaiModel = $config->get('katalysis.ai.open_ai_model');
        $maxLinksPerResponse = (int) $config->get('katalysis.ai.max_links_per_response', 3);
        $linkSelectionRules = $config->get('katalysis.aichatbot.link_selection_rules', $this->getDefaultLinkSelectionRules());

        if (!isset($message) || empty($message)) {
            $message = 'Please apologise for not understanding the question';
        }

        try {
            // Test if configuration is valid
            if (empty($openaiKey) || empty($openaiModel)) {
                return new JsonResponse(
                    ['error' => 'AI configuration is incomplete. Please check your OpenAI API key and model settings.'],
                    400
                );
            }

            if ($mode === 'rag') {
                // RAG Mode: Use RagAgent with its instructions
                $ragAgent = new RagAgent();
                $ragAgent->setApp($this->app);

                try {
                    // Get the response using page context if available
                    if ($pageType || $pageTitle || $pageUrl) {
                        $response = $ragAgent->answerWithPageContext(new UserMessage($message), $pageType, $pageTitle, $pageUrl);
                    } else {
                        $response = $ragAgent->answer(new UserMessage($message));
                    }
                    $responseContent = $response->getContent();
                } catch (\Exception $e) {
                    \Log::addError('RAG agent failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                    throw new \Exception('RAG processing failed: ' . $e->getMessage());
                }


                // Handle case where AI returns JSON instead of plain text
                if (strpos($responseContent, '{') === 0 && strpos($responseContent, '}') !== false) {
                    $jsonData = json_decode($responseContent, true);
                    if ($jsonData && isset($jsonData['response'])) {
                        $responseContent = $jsonData['response'];
                    } elseif ($jsonData && isset($jsonData['content'])) {
                        $responseContent = $jsonData['content'];
                    } elseif ($jsonData && isset($jsonData['message'])) {
                        $responseContent = $jsonData['message'];
                    }
                }

                // Extract action IDs from response
                $actionIds = [];
                if (preg_match('/\[ACTIONS:([^\]]+)\]/', $responseContent, $matches)) {
                    $actionIds = array_map('intval', explode(',', $matches[1]));
                    // Remove the action tag from the response content
                    $responseContent = preg_replace('/\[ACTIONS:[^\]]+\]/', '', $responseContent);
                    $responseContent = trim($responseContent);
                }

                // Get relevant documents for metadata links using the parent class method
                try {
                    $relevantDocs = $ragAgent->retrieveDocuments(new UserMessage($message));
                } catch (\Exception $e) {
                    \Log::addError('Document retrieval failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                    // Continue without documents rather than failing completely
                    $relevantDocs = [];
                }

                // Track page types used in the response
                $pageTypesUsed = [];
                $pageTypesUsed[] = $pageType; // Add the current page type if available

                // Extract page types from relevant documents
                foreach ($relevantDocs as $doc) {
                    if (isset($doc->metadata['pagetype']) && !empty($doc->metadata['pagetype'])) {
                        $docPageType = $doc->metadata['pagetype'];
                        if (!in_array($docPageType, $pageTypesUsed)) {
                            $pageTypesUsed[] = $docPageType;
                        }
                    }
                }

                // AI-based link selection - let the AI choose the most relevant links
                $metadata = [];
                $seenUrls = []; // Track seen URLs to avoid duplicates

                // Prepare candidate documents for AI selection
                $candidateDocs = [];
                foreach ($relevantDocs as $doc) {
                    if (isset($doc->metadata['url']) && !empty($doc->metadata['url'])) {
                        $url = $doc->metadata['url'];

                        // Skip if we've already seen this URL
                        if (in_array($url, $seenUrls)) {
                            continue;
                        }

                        $title = $doc->sourceName ?? '';
                        $content = $doc->content ?? '';
                        $score = $doc->score ?? 0;
                        $pageType = $doc->metadata['pagetype'] ?? '';

                        // Only include documents with reasonable relevance scores
                        if ($score >= 0.3) {
                            $candidateDocs[] = [
                                'title' => $title,
                                'url' => $url,
                                'content' => $content,
                                'score' => $score,
                                'page_type' => $pageType
                            ];
                            $seenUrls[] = $url;
                        }
                    }
                }

                // If we have candidate documents, let AI select the best ones
                if (!empty($candidateDocs)) {
                    // Limit candidates to prevent token overflow
                    $maxCandidates = min(count($candidateDocs), 15);
                    $candidateDocs = array_slice($candidateDocs, 0, $maxCandidates);

                    // Create a prompt for AI to select the most relevant links
                    $linkSelectionPrompt = "You are helping to select the most relevant links for a user's question. 

User Question: \"{$message}\"

Available documents (with titles and URLs):
";

                    foreach ($candidateDocs as $index => $doc) {
                        $linkSelectionPrompt .= ($index + 1) . ". Title: \"{$doc['title']}\" | URL: {$doc['url']} | Page Type: {$doc['page_type']} | Relevance Score: " . number_format($doc['score'], 3) . "\n";
                    }
                    
                    $linkSelectionPrompt .= "\n" . $linkSelectionRules;
                    $linkSelectionPrompt .= "\n" . $this->getEssentialLinkSelectionInstructions();

                    try {
                        // Use the same AI provider to select links
                        $aiProvider = new \NeuronAI\Providers\OpenAI\OpenAI(
                            key: $openaiKey,
                            model: $openaiModel
                        );

                        $linkSelectionResponse = $aiProvider->chat([
                            new \NeuronAI\Chat\Messages\UserMessage($linkSelectionPrompt)
                        ]);

                        $selectedNumbers = $linkSelectionResponse->getContent();

                        // Parse the AI's selection
                        $selectedIndices = [];

                        // Check if AI returned 'none' (no relevant documents)
                        if (strtolower(trim($selectedNumbers)) === 'none') {
                            $selectedIndices = []; // No documents selected
                        } else {
                            // Parse numbers from AI response
                            if (preg_match_all('/\d+/', $selectedNumbers, $matches)) {
                                foreach ($matches[0] as $number) {
                                    $index = (int) $number - 1; // Convert to 0-based index
                                    if ($index >= 0 && $index < count($candidateDocs)) {
                                        $selectedIndices[] = $index;
                                    }
                                }
                            }
                        }

                        // If AI selection failed or returned invalid numbers, fall back to top-scoring documents
                        if (empty($selectedIndices)) {
                            // Sort by score and take top documents
                            usort($candidateDocs, function ($a, $b) {
                                return $b['score'] <=> $a['score'];
                            });
                            $selectedIndices = array_keys(array_slice($candidateDocs, 0, $maxLinksPerResponse));
                        }

                        // Build metadata from AI-selected documents
                        foreach ($selectedIndices as $index) {
                            if (isset($candidateDocs[$index])) {
                                $doc = $candidateDocs[$index];
                                $metadata[] = [
                                    'title' => $doc['title'],
                                    'url' => $doc['url'],
                                    'score' => $doc['score'],
                                    'original_score' => $doc['score'],
                                    'ai_selected' => true,
                                    'selection_reason' => 'AI chose this as most relevant to the user\'s question'
                                ];
                            }
                        }

                    } catch (\Exception $e) {
                        // Fallback to top-scoring documents if AI selection fails
                        usort($candidateDocs, function ($a, $b) {
                            return $b['score'] <=> $a['score'];
                        });

                        $topDocs = array_slice($candidateDocs, 0, $maxLinksPerResponse);
                        foreach ($topDocs as $doc) {
                            $metadata[] = [
                                'title' => $doc['title'],
                                'url' => $doc['url'],
                                'score' => $doc['score'],
                                'original_score' => $doc['score'],
                                'ai_selected' => false,
                                'selection_reason' => 'Fallback to top-scoring documents (AI selection failed)'
                            ];
                        }
                    }
                }

                // Return response with metadata and page types used
                $responseData = [
                    'content' => $responseContent,
                    'metadata' => $metadata,
                    'page_types_used' => $pageTypesUsed,
                    'current_page_type' => $pageType,
                    'context_info' => [
                        'current_page_title' => $pageTitle,
                        'current_page_url' => $pageUrl,
                        'total_documents_retrieved' => count($relevantDocs),
                        'page_types_from_documents' => array_unique(array_filter(array_map(function ($doc) {
                            return $doc->metadata['pagetype'] ?? null;
                        }, $relevantDocs)))
                    ],
                    'debug_info' => [
                        'link_selection' => [
                            'total_documents_processed' => count($relevantDocs),
                            'documents_with_urls' => count(array_filter($relevantDocs, function ($doc) {
                                return isset($doc->metadata['url']) && !empty($doc->metadata['url']);
                            })),
                            'candidate_documents' => count($candidateDocs ?? []),
                            'ai_selected_links' => count($metadata)
                        ],
                        'scoring_details' => array_map(function ($link) {
                            return [
                                'title' => $link['title'],
                                'url' => $link['url'],
                                'final_score' => $link['score'],
                                'original_score' => $link['original_score'] ?? $link['score'],
                                'selection_reason' => $link['selection_reason'] ?? null
                            ];
                        }, $metadata)
                    ]
                ];

                // Add actions if any were suggested by the AI
                if (!empty($actionIds)) {
                    $actionService = new \KatalysisAiChatBot\ActionService($this->app->make('Doctrine\ORM\EntityManager'));
                    $suggestedActions = [];
                    
                    foreach ($actionIds as $actionId) {
                        $action = $actionService->getActionById($actionId);
                        if ($action) {
                            $suggestedActions[] = [
                                'id' => $action->getId(),
                                'name' => $action->getName(),
                                'icon' => $action->getIcon(),
                                'triggerInstruction' => $action->getTriggerInstruction(),
                                'responseInstruction' => $action->getResponseInstruction()
                            ];
                        }
                    }
                    
                    $responseData['actions'] = $suggestedActions;
                }

                // Add chat ID if a new chat was created
                if ($chatId) {
                    $responseData['chat_id'] = $chatId;
                }

                return new JsonResponse($responseData);

            } else {
                // Basic Mode: Use regular AiAgent or direct AI call for welcome messages
                if (strpos($message, 'Generate a short, friendly welcome message') !== false) {
                    // This is a welcome message request - use direct AI call with the welcome prompt
                    $aiProvider = new \NeuronAI\Providers\OpenAI\OpenAI(
                        key: $openaiKey,
                        model: $openaiModel
                    );

                    $response = $aiProvider->chat([
                        new \NeuronAI\Chat\Messages\UserMessage($message)
                    ]);

                    $responseContent = $response->getContent();
                } else {
                    // Regular basic mode - use AiAgent
                    $agent = new AiAgent();
                    $response = $agent->chat(
                        new UserMessage($message)
                    );

                    $responseContent = $response->getContent();
                }

                $responseData = [
                    'content' => $responseContent,
                    'metadata' => []
                ];

                // Add chat ID if a new chat was created
                if ($chatId) {
                    $responseData['chat_id'] = $chatId;
                }

                return new JsonResponse($responseData);
            }

        } catch (\Exception $e) {
            // Log the specific error for debugging
            \Log::addError('AI request failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            
            // Return more specific error information
            return new JsonResponse([
                'error' => 'AI processing failed',
                'details' => $e->getMessage(),
                'type' => get_class($e)
            ], 500);
        }
    }

    /**
     * Handle action button clicks
     */
    public function execute_action()
    {
        // No token validation needed (like ask_ai method)

        // Get request data
        $request = $this->app->make('request');
        
        // Parse JSON request body
        $rawContent = $request->getContent();
        $jsonData = json_decode($rawContent, true);
        
        $actionId = $jsonData['action_id'] ?? null;
        $conversationContext = $jsonData['conversation_context'] ?? '';

        if (!$actionId) {
            return new JsonResponse(['error' => 'Action ID is required'], 400);
        }

        try {
            // Get the action
            $actionService = new \KatalysisAiChatBot\ActionService($this->app->make('Doctrine\ORM\EntityManager'));
            $action = $actionService->getActionById($actionId);

            if (!$action) {
                return new JsonResponse(['error' => 'Action not found'], 404);
            }

            // Get AI configuration
            $config = $this->app->make('config');
            $openaiKey = $config->get('katalysis.ai.open_ai_key');
            $openaiModel = $config->get('katalysis.ai.open_ai_model');

            if (empty($openaiKey) || empty($openaiModel)) {
                return new JsonResponse(['error' => 'AI configuration is incomplete'], 400);
            }

            // Create a prompt for the AI to execute the action
            $prompt = "The user has clicked the '{$action->getName()}' action button. ";
            $prompt .= "Here is the instruction for what to do: {$action->getResponseInstruction()}";
            
            if (!empty($conversationContext)) {
                $prompt .= "\n\nConversation context: {$conversationContext}";
            }

            // Execute the action using AI
            $aiProvider = new \NeuronAI\Providers\OpenAI\OpenAI(
                key: $openaiKey,
                model: $openaiModel
            );

            $response = $aiProvider->chat([
                new \NeuronAI\Chat\Messages\UserMessage($prompt)
            ]);

            $responseContent = $response->getContent();

            return new JsonResponse([
                'content' => $responseContent,
                'action_name' => $action->getName(),
                'action_icon' => $action->getIcon()
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to execute action: ' . $e->getMessage()], 500);
        }
    }







    /**
     * Create a new chat record in the database
     */
    private function createNewChatRecord(string $mode, ?string $pageType, ?string $pageTitle, ?string $pageUrl): ?int
    {
        try {
            $entityManager = $this->app->make('Doctrine\ORM\EntityManager');
            
            $chat = new \KatalysisAiChatBot\Entity\Chat();
            
            // Set chat properties
            $chat->setStarted(new \DateTime());
            $chat->setCreatedDate(new \DateTime());
            
            // Get user's geographical location from IP address
            $location = $this->getUserGeographicalLocation();
            $chat->setLocation($location);
            
            // Get the actual chat model being used
            $config = $this->app->make('config');
            $chatModel = $config->get('katalysis.ai.open_ai_model', 'gpt-4');
            $chat->setLlm($chatModel);
            
            // Get current page information from Concrete CMS
            $currentPage = \Page::getCurrentPage();
            if ($currentPage && !$currentPage->isError()) {
                $chat->setLaunchPageUrl($currentPage->getCollectionLink());
                $chat->setLaunchPageType($currentPage->getPageTypeHandle());
                $chat->setLaunchPageTitle($currentPage->getCollectionName());
            } else {
                // Fallback to provided values if current page is not available
                $chat->setLaunchPageUrl($pageUrl ?: '');
                $chat->setLaunchPageType($pageType ?: '');
                $chat->setLaunchPageTitle($pageTitle ?: '');
            }
            
            // Set UTM parameters from request
            $request = $this->app->make('request');
            $chat->setUtmId($request->get('utm_id', ''));
            $chat->setUtmSource($request->get('utm_source', ''));
            $chat->setUtmMedium($request->get('utm_medium', ''));
            $chat->setUtmCampaign($request->get('utm_campaign', ''));
            $chat->setUtmTerm($request->get('utm_term', ''));
            $chat->setUtmContent($request->get('utm_content', ''));
            
            // Set created by (current user or null if not logged in)
            $user = new \Concrete\Core\User\User();
            if ($user && $user->isRegistered()) {
                $chat->setCreatedBy($user->getUserID());
            }
            
            // Persist the chat record
            $entityManager->persist($chat);
            $entityManager->flush();
            
            return $chat->getId();
            
        } catch (\Exception $e) {
            // Log the error but don't fail the chat request
            \Log::addError('Failed to create chat record: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user's geographical location from IP address
     */
    private function getUserGeographicalLocation(): string
    {
        try {
            $request = $this->app->make('request');
            $ip = $request->getClientIp();
            
            // Skip local/private IPs
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return 'Local/Private IP';
            }
            
            // Use a free IP geolocation service
            $url = "http://ip-api.com/json/{$ip}?fields=status,message,country,regionName,city";
            $response = file_get_contents($url);
            
            if ($response !== false) {
                $data = json_decode($response, true);
                
                if ($data && isset($data['status']) && $data['status'] === 'success') {
                    $location = [];
                    
                    if (!empty($data['city'])) {
                        $location[] = $data['city'];
                    }
                    if (!empty($data['regionName'])) {
                        $location[] = $data['regionName'];
                    }
                    if (!empty($data['country'])) {
                        $location[] = $data['country'];
                    }
                    
                    return !empty($location) ? implode(', ', $location) : 'Unknown Location';
                }
            }
            
            return 'Location Unknown';
            
        } catch (\Exception $e) {
            \Log::addError('Failed to get user location: ' . $e->getMessage());
            return 'Location Error';
        }
    }

    /**
     * Update chat record with message information
     */
    private function updateChatWithMessage(int $chatId, string $message): void
    {
        try {
            $entityManager = $this->app->make('Doctrine\ORM\EntityManager');
            $chat = $entityManager->find(\KatalysisAiChatBot\Entity\Chat::class, $chatId);
            
            if ($chat) {
                // Set first message if not already set
                if (empty($chat->getFirstMessage())) {
                    $chat->setFirstMessage($message);
                }
                
                // Always update last message
                $chat->setLastMessage($message);
                
                $entityManager->persist($chat);
                $entityManager->flush();
            }
            
        } catch (\Exception $e) {
            // Log the error but don't fail the chat request
            \Log::addError('Failed to update chat with message: ' . $e->getMessage());
        }
    }

    /**
     * Update chat record with complete chat history
     */
    private function updateChatWithCompleteHistory(int $chatId, array $messages): void
    {
        try {
            $entityManager = $this->app->make('Doctrine\ORM\EntityManager');
            $chat = $entityManager->find(\KatalysisAiChatBot\Entity\Chat::class, $chatId);
            
            if ($chat) {
                // Convert messages to JSON for storage
                $chatHistoryJson = json_encode($messages, JSON_PRETTY_PRINT);
                
                // Update the complete chat history
                $chat->setCompleteChatHistory($chatHistoryJson);
                
                $entityManager->persist($chat);
                $entityManager->flush();
            }
            
        } catch (\Exception $e) {
            // Log the error but don't fail the chat request
            \Log::addError('Failed to update chat with complete history: ' . $e->getMessage());
        }
    }

    /**
     * Log chat from chatbot block to database
     */
    public function log_chat()
    {
        try {
            $request = $this->app->make('request');
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new \Symfony\Component\HttpFoundation\JsonResponse([
                    'success' => false,
                    'error' => 'Invalid request data'
                ]);
            }
            
            $chatbotId = $data['chatbot_id'] ?? '';
            $pageTitle = $data['page_title'] ?? '';
            $pageUrl = $data['page_url'] ?? '';
            $pageType = $data['page_type'] ?? '';
            $messages = $data['messages'] ?? [];
            $timestamp = $data['timestamp'] ?? time();
            $sessionId = $data['session_id'] ?? '';
            
            if (empty($messages)) {
                return new \Symfony\Component\HttpFoundation\JsonResponse([
                    'success' => false,
                    'error' => 'No messages to log'
                ]);
            }
            
            // Create or update chat record
            $chatId = $this->createOrUpdateChatRecord($chatbotId, $pageTitle, $pageUrl, $pageType, $sessionId);
            
            if ($chatId) {
                // Log individual messages
                $this->logChatMessages($chatId, $messages);
                
                return new \Symfony\Component\HttpFoundation\JsonResponse([
                    'success' => true,
                    'message' => 'Chat logged successfully. Chat ID: ' . $chatId,
                    'chat_id' => $chatId
                ]);
            } else {
                return new \Symfony\Component\HttpFoundation\JsonResponse([
                    'success' => false,
                    'error' => 'Failed to create chat record'
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::addError('Failed to log chat: ' . $e->getMessage());
            return new \Symfony\Component\HttpFoundation\JsonResponse([
                'success' => false,
                'error' => 'Failed to log chat: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update existing chat record in database
     */
    public function update_chat()
    {
        try {
            $request = $this->app->make('request');
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new \Symfony\Component\HttpFoundation\JsonResponse([
                    'success' => false,
                    'error' => 'Invalid request data'
                ]);
            }
            
            $chatId = $data['chat_id'] ?? 0;
            $messages = $data['messages'] ?? [];
            
            if (empty($chatId)) {
                return new \Symfony\Component\HttpFoundation\JsonResponse([
                    'success' => false,
                    'error' => 'No chat ID provided'
                ]);
            }
            
            // Update the existing chat record with new messages
            $this->logChatMessages($chatId, $messages);
            
            return new \Symfony\Component\HttpFoundation\JsonResponse([
                'success' => true,
                'message' => 'Chat updated successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::addError('Failed to update chat: ' . $e->getMessage());
            return new \Symfony\Component\HttpFoundation\JsonResponse([
                'success' => false,
                'error' => 'Failed to update chat: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create or update chat record for chatbot block
     */
    private function createOrUpdateChatRecord(string $chatbotId, string $pageTitle, string $pageUrl, string $pageType, string $sessionId = ''): ?int
    {
        try {
            $entityManager = $this->app->make('Doctrine\ORM\EntityManager');
            
            // Always create a new chat record for each session
            // This ensures that clearing chat history creates a new database record
            $chat = new \KatalysisAiChatBot\Entity\Chat();
            
            // Set chat record properties
            $chat->setStarted(new \DateTime());
            $chat->setLocation($this->getUserGeographicalLocation());
            $chat->setLlm('gpt-4');
            $chat->setLaunchPageTitle($pageTitle);
            $chat->setLaunchPageUrl($pageUrl);
            $chat->setLaunchPageType($pageType);
            $chat->setCreatedDate(new \DateTime());
            $chat->setUtmSource('chatbot_block');
            $chat->setUtmMedium('chatbot');
            $chat->setUtmCampaign('website_chat');
            $chat->setUtmTerm($chatbotId);
            $chat->setUtmContent('block_chat');
            $chat->setSessionId($sessionId);
            
            // Set created by (current user or null if not logged in)
            $user = new \Concrete\Core\User\User();
            if ($user && $user->isRegistered()) {
                $chat->setCreatedBy($user->getUserID());
            }
            
            // Persist the chat record
            $entityManager->persist($chat);
            $entityManager->flush();
            
            return $chat->getId();
            
        } catch (\Exception $e) {
            \Log::addError('Failed to create/update chat record: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log individual chat messages
     */
    private function logChatMessages(int $chatId, array $messages): void
    {
        try {
            $entityManager = $this->app->make('Doctrine\ORM\EntityManager');
            $chat = $entityManager->find(\KatalysisAiChatBot\Entity\Chat::class, $chatId);
            
            if ($chat) {
                // Update chat with last message
                $lastMessage = end($messages);
                if ($lastMessage && isset($lastMessage['content'])) {
                    $this->updateChatWithMessage($chatId, $lastMessage['content']);
                }
                
                // Store complete chat history
                $this->updateChatWithCompleteHistory($chatId, $messages);
            }
            
        } catch (\Exception $e) {
            \Log::addError('Failed to log chat messages: ' . $e->getMessage());
        }
    }
}