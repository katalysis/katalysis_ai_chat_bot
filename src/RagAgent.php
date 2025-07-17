<?php

namespace KatalysisAiChatBot;

use Concrete\Core\Support\Facade\Config;
use NeuronAI\SystemPrompt;
use NeuronAI\Chat\History\FileChatHistory;

use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\Embeddings\OpenAIEmbeddingsProvider;
use NeuronAI\RAG\RAG;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use NeuronAI\RAG\DataLoader\StringDataLoader;
use NeuronAI\RAG\VectorStore\FileVectorStore;

class RagAgent extends RAG
{
    
    protected function provider(): AIProviderInterface
    {
        // return an AI provider (Anthropic, OpenAI, Ollama, Gemini, etc.)
        return new OpenAI(
            key: Config::get('katalysis.ai.open_ai_key'),
            model: Config::get('katalysis.ai.open_ai_model')
        );
    }

    protected function embeddings(): EmbeddingsProviderInterface
    {
        return new OpenAIEmbeddingsProvider(
            key: Config::get('katalysis.ai.open_ai_key'),
            model: 'text-embedding-3-small'
        );
    }
    
    protected function vectorStore(): VectorStoreInterface
    {
        return new FileVectorStore(
            directory: DIR_APPLICATION . '/files/neuron',
            topK: 8  // Increased from 4 to 8 for better search results
        );
    }

    protected function chatHistory(): \NeuronAI\Chat\History\AbstractChatHistory
    {
        return new FileChatHistory(
            directory: DIR_APPLICATION . '/files/neuron',
            key: '1', // The key allow to store different files to separate conversations
            contextWindow: 50000
        );
    }

    public function instructions(): string
    {
        return new SystemPrompt(
            background: [
                Config::get('katalysis.aichatbot.instructions'),
                "RESPONSE FORMAT GUIDELINES:",
                "• Respond with plain text only - no JSON, no formatting",
                "RESPONSE FORMAT AVOID:",
                "- JSON formatting or structured output",
                "- Any response that starts with { or contains JSON syntax"
            ]
        );
    }

    /**
     * Answer with page context
     */
    public function answerWithPageContext($message, $pageType = null, $pageTitle = null, $pageUrl = null)
    {
        $baseInstructions = Config::get('katalysis.aichatbot.instructions');
        
        // Replace placeholders with actual values
        $instructions = str_replace('{page_type}', $pageType ?? 'unknown', $baseInstructions);
        $instructions = str_replace('{page_title}', $pageTitle ?? 'this page', $instructions);
        $instructions = str_replace('{page_url}', $pageUrl ?? 'current page', $instructions);
        
        // Create a temporary system prompt with page context
        $systemPrompt = new SystemPrompt(
            background: [
                $instructions,
                "RESPONSE FORMAT GUIDELINES:",
                "• Respond with plain text only - no JSON, no formatting",
                "RESPONSE FORMAT AVOID:",
                "- JSON formatting or structured output",
                "- Any response that starts with { or contains JSON syntax"
            ]
        );
        
        // Create a temporary RAG instance with the modified instructions
        $tempRag = new RAG(
            provider: $this->provider(),
            embeddings: $this->embeddings(),
            vectorStore: $this->vectorStore(),
            chatHistory: $this->chatHistory(),
            systemPrompt: $systemPrompt
        );
        
        // Get the response
        $response = $tempRag->answer($message);
        
        return $response;
    }
}   
