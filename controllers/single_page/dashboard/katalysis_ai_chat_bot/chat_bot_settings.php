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
        $this->set('debugMode', (bool) $config->get('katalysis.aichatbot.debug_mode', false));
        $this->set('debugPageTitle', $config->get('katalysis.aichatbot.debug_page_title', ''));
        $this->set('debugPageType', $config->get('katalysis.aichatbot.debug_page_type', ''));
        $this->set('debugPageUrl', $config->get('katalysis.aichatbot.debug_page_url', ''));

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
        return "Generate a short, friendly welcome message for a legal services website. 

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
- \"Good morning! Welcome to our legal services website. How can we help?\"
- \"Good afternoon and welcome to our site. How can we assist you today?\"
- \"Good evening! Thank you for visiting our legal services. How can we help?\"

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
            $pageType = $jsonData['page_type'] ?? null;
            $pageTitle = $jsonData['page_title'] ?? null;
            $pageUrl = $jsonData['page_url'] ?? null;
        } else {
            // Handle form data request
            $data = $request->request->all();
            $message = $data['message'] ?? null;
            $mode = $data['mode'] ?? 'rag';
            $pageType = $data['page_type'] ?? null;
            $pageTitle = $data['page_title'] ?? null;
            $pageUrl = $data['page_url'] ?? null;
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

                // Get the response using page context if available
                if ($pageType || $pageTitle || $pageUrl) {
                    $response = $ragAgent->answerWithPageContext(new UserMessage($message), $pageType, $pageTitle, $pageUrl);
                } else {
                    $response = $ragAgent->answer(new UserMessage($message));
                }
                $responseContent = $response->getContent();

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

                // Get relevant documents for metadata links using the parent class method
                $relevantDocs = $ragAgent->retrieveDocuments(new UserMessage($message));

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
                return new JsonResponse([
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
                ]);

            } else {
                // Basic Mode: Use regular AiAgent
                $agent = new AiAgent();
                $response = $agent->chat(
                    new UserMessage($message)
                );

                $responseContent = $response->getContent();

                return new JsonResponse([
                    'content' => $responseContent,
                    'metadata' => []
                ]);
            }

        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to process request: ' . $e->getMessage()],
                500
            );
        }
    }





}