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
        $this->set('debugMode', (bool)$config->get('katalysis.aichatbot.debug_mode', false));
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
        $config->save('katalysis.aichatbot.debug_mode', (bool) $this->post('debug_mode'));
        $config->save('katalysis.aichatbot.debug_page_title', (string) $this->post('debug_page_title'));
        $config->save('katalysis.aichatbot.debug_page_type', (string) $this->post('debug_page_type'));
        $config->save('katalysis.aichatbot.debug_page_url', (string) $this->post('debug_page_url'));
            $this->flash('success', t('Chat bot settings have been updated.'));
        }
        return $this->buildRedirect($this->action());
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
        if (strpos($contentType, 'application/json') !== false || 
            (trim($rawContent) && (strpos(trim($rawContent), '{') === 0 || strpos(trim($rawContent), '[') === 0))) {
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
                    
                    $linkSelectionPrompt .= "
Instructions:
You are selecting the most relevant links for a user's question. Be very selective and only choose links that directly address the user's specific needs.

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
- If no documents are truly relevant, return empty (no numbers)

Return only the numbers of the selected documents separated by commas (e.g., '1,3' or '2'). If no documents are relevant, return 'none':";

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
                                    $index = (int)$number - 1; // Convert to 0-based index
                                    if ($index >= 0 && $index < count($candidateDocs)) {
                                        $selectedIndices[] = $index;
                                    }
                                }
                            }
                        }
                        
                        // If AI selection failed or returned invalid numbers, fall back to top-scoring documents
                        if (empty($selectedIndices)) {
                            // Sort by score and take top documents
                            usort($candidateDocs, function($a, $b) {
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
                        usort($candidateDocs, function($a, $b) {
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
                        'page_types_from_documents' => array_unique(array_filter(array_map(function($doc) {
                            return $doc->metadata['pagetype'] ?? null;
                        }, $relevantDocs)))
                    ],
                    'debug_info' => [
                        'link_selection' => [
                            'total_documents_processed' => count($relevantDocs),
                            'documents_with_urls' => count(array_filter($relevantDocs, function($doc) {
                                return isset($doc->metadata['url']) && !empty($doc->metadata['url']);
                            })),
                            'candidate_documents' => count($candidateDocs ?? []),
                            'ai_selected_links' => count($metadata)
                        ],
                        'scoring_details' => array_map(function($link) {
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