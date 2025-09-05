<?php

namespace Concrete\Package\KatalysisAiChatBot\Controller\SinglePage\Dashboard\KatalysisAiChatBot;

use Concrete\Core\Page\Controller\DashboardPageController;
use KatalysisAiChatBot\Entity\Action;
use Concrete\Core\User\User;
use Symfony\Component\HttpFoundation\JsonResponse;

class CreateSampleForms extends DashboardPageController
{
    public function view()
    {
        // Nothing special needed for the view, the template handles everything
    }
    
    public function create_sample_form()
    {
        if (!$this->token->validate('create_sample_form')) {
            return new JsonResponse([
                'success' => false,
                'message' => t('Invalid token')
            ], 400);
        }
        
        $data = json_decode($this->request->getContent(), true);
        $type = $data['type'] ?? null;
        
        if (!$type) {
            return new JsonResponse([
                'success' => false,
                'message' => t('Form type is required')
            ], 400);
        }
        
        try {
            $result = $this->createSampleFormByType($type);
            return new JsonResponse($result);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => t('Error creating form: %s', $e->getMessage())
            ], 500);
        }
    }
    
    public function create_all_sample_forms()
    {
        if (!$this->token->validate('create_all_sample_forms')) {
            return new JsonResponse([
                'success' => false,
                'message' => t('Invalid token')
            ], 400);
        }
        
        try {
            $results = [];
            $types = ['contact', 'lead_qualification', 'demo_request', 'support_request'];
            
            foreach ($types as $type) {
                $result = $this->createSampleFormByType($type);
                $results[] = $result;
                
                if (!$result['success']) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => t('Failed to create %s form: %s', $type, $result['message']),
                        'details' => 'Partial completion - some forms may have been created'
                    ]);
                }
            }
            
            return new JsonResponse([
                'success' => true,
                'message' => t('Successfully created all 4 sample forms!'),
                'details' => t('Contact Form, AI Lead Qualification, Demo Request, and Support Request forms have been created.')
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => t('Error creating forms: %s', $e->getMessage())
            ], 500);
        }
    }
    
    private function createSampleFormByType($type)
    {
        $entityManager = $this->app->make('Doctrine\ORM\EntityManager');
        $user = new User();
        $userId = $user->getUserID();
        
        // Check if this type already exists
        $existing = $entityManager->getRepository(Action::class)->findOneBy([
            'name' => $this->getFormNameByType($type)
        ]);
        
        if ($existing) {
            return [
                'success' => false,
                'message' => t('A form named "%s" already exists', $this->getFormNameByType($type))
            ];
        }
        
        $action = new Action();
        $formData = $this->getSampleFormData($type);
        
        $action->setName($formData['name']);
        $action->setIcon($formData['icon']);
        $action->setActionType($formData['actionType']);
        $action->setTriggerInstruction($formData['triggerInstruction']);
        $action->setResponseInstruction($formData['responseInstruction']);
        $action->setFormSteps(json_encode($formData['formSteps']));
        $action->setFormConfig(json_encode($formData['formConfig']));
        $action->setCreatedBy($userId);
        $action->setCreatedDate(new \DateTime());
        
        $entityManager->persist($action);
        $entityManager->flush();
        
        return [
            'success' => true,
            'message' => t('Successfully created "%s" form action with ID %d', $formData['name'], $action->getId()),
            'details' => t('Form type: %s, Steps: %d', $formData['actionType'], count($formData['formSteps']))
        ];
    }
    
    private function getFormNameByType($type)
    {
        $names = [
            'contact' => 'Contact Us',
            'lead_qualification' => 'Get Personalized Quote',
            'demo_request' => 'Schedule Demo',
            'support_request' => 'Get Help'
        ];
        
        return $names[$type] ?? 'Unknown Form';
    }
    
    private function getSampleFormData($type)
    {
        switch ($type) {
            case 'contact':
                return [
                    'name' => 'Contact Us',
                    'icon' => 'fas fa-envelope',
                    'actionType' => 'form',
                    'triggerInstruction' => 'User wants to contact us, get in touch, or needs help from our team',
                    'responseInstruction' => 'I\'d be happy to help you get in touch with our team!',
                    'formSteps' => [
                        [
                            'stepKey' => 'name',
                            'fieldType' => 'text',
                            'question' => 'What\'s your name?',
                            'aiPrompt' => 'Ask for their full name in a warm, friendly way',
                            'validation' => ['required' => true, 'min_length' => 2],
                            'sortOrder' => 1
                        ],
                        [
                            'stepKey' => 'email',
                            'fieldType' => 'email',
                            'question' => 'What\'s your email address?',
                            'aiPrompt' => 'Ask for their email so we can respond to them',
                            'validation' => ['required' => true, 'email' => true],
                            'sortOrder' => 2
                        ],
                        [
                            'stepKey' => 'message',
                            'fieldType' => 'textarea',
                            'question' => 'What can we help you with?',
                            'aiPrompt' => 'Ask what specific help they need from our team',
                            'validation' => ['required' => true, 'min_length' => 10],
                            'sortOrder' => 3
                        ]
                    ],
                    'formConfig' => [
                        'progressive' => true,
                        'completion_message' => 'Thanks {name}! We\'ve received your message and will get back to you within 24 hours.',
                        'ai_completion' => false
                    ]
                ];
                
            case 'lead_qualification':
                return [
                    'name' => 'Get Personalized Quote',
                    'icon' => 'fas fa-calculator',
                    'actionType' => 'dynamic_form',
                    'triggerInstruction' => 'User wants pricing, a quote, cost information, or to understand our rates',
                    'responseInstruction' => 'I can help you get a personalized quote based on your specific needs!',
                    'formSteps' => [
                        [
                            'stepKey' => 'name',
                            'fieldType' => 'text',
                            'question' => 'First, what\'s your name?',
                            'aiPrompt' => 'Get their name for personalization',
                            'validation' => ['required' => true],
                            'sortOrder' => 1
                        ],
                        [
                            'stepKey' => 'company',
                            'fieldType' => 'text',
                            'question' => 'What company do you work for?',
                            'aiPrompt' => 'Ask about their company to understand business context',
                            'validation' => ['required' => true],
                            'sortOrder' => 2
                        ],
                        [
                            'stepKey' => 'company_size',
                            'fieldType' => 'select',
                            'question' => 'How many employees does {company} have?',
                            'options' => ['1-10', '11-50', '51-200', '201-1000', '1000+'],
                            'sortOrder' => 3,
                            'conditionalLogic' => [
                                'ai_decides' => true,
                                'decision_prompt' => 'Based on company "{company}", should we ask about company size? Skip for well-known large companies like Microsoft, Google, Apple, etc.'
                            ]
                        ],
                        [
                            'stepKey' => 'budget_range',
                            'fieldType' => 'select',
                            'question' => 'What\'s your approximate budget range for this project?',
                            'options' => ['Under $1,000', '$1,000 - $5,000', '$5,000 - $25,000', '$25,000 - $100,000', 'Over $100,000'],
                            'sortOrder' => 4,
                            'conditionalLogic' => [
                                'ai_decides' => true,
                                'decision_prompt' => 'Should we ask about budget? Consider: company size "{company_size}", company "{company}". Only ask if they seem like a qualified prospect based on company and size.'
                            ]
                        ],
                        [
                            'stepKey' => 'ai_generated_followup',
                            'fieldType' => 'ai_generated',
                            'sortOrder' => 5,
                            'aiGenerationPrompt' => 'Based on: Company: {company}, Size: {company_size}, Budget: {budget_range} - generate 1 additional qualifying question that would help us provide the most accurate quote.'
                        ]
                    ],
                    'formConfig' => [
                        'progressive' => true,
                        'ai_completion' => true,
                        'completion_prompt' => 'Based on the collected information, determine the best next action. Consider: Company size, budget, timeline, and overall qualification level.',
                        'ai_decision_model' => 'claude-3-haiku'
                    ]
                ];
                
            case 'demo_request':
                return [
                    'name' => 'Schedule Demo',
                    'icon' => 'fas fa-calendar-alt',
                    'actionType' => 'dynamic_form',
                    'triggerInstruction' => 'User wants to see a demo, product demonstration, or wants to see how our solution works',
                    'responseInstruction' => 'I\'d love to show you how our solution works! Let me get some details to set up the perfect demo for you.',
                    'formSteps' => [
                        [
                            'stepKey' => 'name',
                            'fieldType' => 'text',
                            'question' => 'What\'s your name?',
                            'validation' => ['required' => true],
                            'sortOrder' => 1
                        ],
                        [
                            'stepKey' => 'email',
                            'fieldType' => 'email',
                            'question' => 'What\'s your email address?',
                            'validation' => ['required' => true, 'email' => true],
                            'sortOrder' => 2
                        ],
                        [
                            'stepKey' => 'role',
                            'fieldType' => 'select',
                            'question' => 'What\'s your role at your company?',
                            'options' => ['CEO/Founder', 'CTO/Technical Lead', 'Marketing Manager', 'Sales Manager', 'Operations Manager', 'Other'],
                            'sortOrder' => 3
                        ],
                        [
                            'stepKey' => 'specific_interest',
                            'fieldType' => 'ai_generated',
                            'sortOrder' => 4,
                            'aiGenerationPrompt' => 'Based on their role ({role}), generate a specific question about what aspect of our product they\'re most interested in seeing during the demo.'
                        ]
                    ],
                    'formConfig' => [
                        'progressive' => true,
                        'ai_completion' => true,
                        'completion_prompt' => 'Based on role and interests, should this lead get: enterprise_demo (for senior roles), standard_demo (for managers), or self_service_trial (for individual contributors)?'
                    ]
                ];
                
            case 'support_request':
                return [
                    'name' => 'Get Help',
                    'icon' => 'fas fa-life-ring',
                    'actionType' => 'form',
                    'triggerInstruction' => 'User has a problem, needs help, has an issue, or something isn\'t working',
                    'responseInstruction' => 'I\'m here to help! Let me get some details so I can assist you or connect you with the right person.',
                    'formSteps' => [
                        [
                            'stepKey' => 'issue_type',
                            'fieldType' => 'select',
                            'question' => 'What type of issue are you experiencing?',
                            'options' => ['Technical Problem', 'Account/Billing Question', 'Feature Request', 'General Question', 'Bug Report'],
                            'validation' => ['required' => true],
                            'sortOrder' => 1
                        ],
                        [
                            'stepKey' => 'urgency',
                            'fieldType' => 'select',
                            'question' => 'How urgent is this issue?',
                            'options' => ['Critical - System Down', 'High - Major Impact', 'Medium - Some Impact', 'Low - Minor Issue'],
                            'validation' => ['required' => true],
                            'sortOrder' => 2,
                            'conditionalLogic' => [
                                'show_if' => ['field' => 'issue_type', 'equals' => 'Technical Problem']
                            ]
                        ],
                        [
                            'stepKey' => 'description',
                            'fieldType' => 'textarea',
                            'question' => 'Please describe the {issue_type} in detail:',
                            'aiPrompt' => 'Ask them to describe their specific issue so we can help effectively',
                            'validation' => ['required' => true, 'min_length' => 20],
                            'sortOrder' => 3
                        ],
                        [
                            'stepKey' => 'contact_email',
                            'fieldType' => 'email',
                            'question' => 'What\'s the best email to reach you at?',
                            'validation' => ['required' => true, 'email' => true],
                            'sortOrder' => 4
                        ]
                    ],
                    'formConfig' => [
                        'progressive' => true,
                        'completion_message' => 'Thank you! I\'ve submitted your {issue_type} request. Our support team will get back to you shortly.',
                        'ai_completion' => false
                    ]
                ];
                
            default:
                throw new \InvalidArgumentException('Unknown form type: ' . $type);
        }
    }
}