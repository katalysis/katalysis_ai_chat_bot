<?php
defined('C5_EXECUTE') or die("Access Denied.");

/** @var \KatalysisAiChatBot\Entity\Chat $chat */
/** @var string $pageTitle */
?>

<div class="ccm-dashboard-header-buttons">
    <a href="<?php echo $this->action(''); ?>" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> <?php echo t('Back to Chats'); ?>
    </a>
</div>

<div class="ccm-dashboard-content-full">
    <div class="container-fluid">
        <div class="row justify-content-between">
            <div class="col-md-5">
                <h4><?php echo t('Basic Information'); ?></h4>
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th style="width:40%;"><?php echo t('ID'); ?></th>
                            <td><?php echo $chat->getId(); ?></td>
                        </tr>
                        <tr>
                            <th style="width:40%;"><?php echo t('Session ID'); ?></th>
                            <td><?php echo $chat->getSessionId() ?: t('Not set'); ?></td>
                        </tr>
                        <tr>
                            <th style="width:40%;"><?php echo t('Started'); ?></th>
                            <td><?php echo $chat->getStarted() ? $chat->getStarted()->format('Y-m-d H:i:s') : t('Not set'); ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width:40%;"><?php echo t('Created Date'); ?></th>
                            <td><?php echo $chat->getCreatedDate() ? $chat->getCreatedDate()->format('Y-m-d H:i:s') : t('Not set'); ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width:40%;"><?php echo t('Created By'); ?></th>
                            <td><?php echo $chat->getCreatedBy(); ?></td>
                        </tr>
                        <tr>
                            <th style="width:40%;"><?php echo t('Location'); ?></th>
                            <td><?php echo $chat->getLocation() ?: t('Not set'); ?></td>
                        </tr>
                        <tr>
                            <th style="width:40%;"><?php echo t('LLM'); ?></th>
                            <td><?php echo $chat->getLlm() ?: t('Not set'); ?></td>
                        </tr>
                        <tr>
                            <th style="width:40%;"><?php echo t('User Message Count'); ?></th>
                            <td>
                                <span class="badge bg-primary"><?php echo $chat->getUserMessageCount() ?: '0'; ?></span>
                                <small class="text-muted ms-2"><?php echo t('engagement metric'); ?></small>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <h4><?php echo t('Page Information'); ?></h4>
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th style="width:40%;"><?php echo t('Page Title'); ?></th>
                            <td><?php echo $chat->getLaunchPageTitle() ?: t('Not set'); ?></td>
                        </tr>
                        <tr>
                            <th style="width:40%;"><?php echo t('Page URL'); ?></th>
                            <td style="word-wrap: break-word;word-break: break-all;">
                                <?php if ($chat->getLaunchPageUrl()): ?>
                                    <a href="<?php echo htmlspecialchars($chat->getLaunchPageUrl()); ?>" target="_blank">
                                        <?php echo htmlspecialchars($chat->getLaunchPageUrl()); ?>
                                    </a>
                                <?php else: ?>
                                    <?php echo t('Not set'); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width:40%;"><?php echo t('Page Type'); ?></th>
                            <td><?php echo $chat->getLaunchPageType() ?: t('Not set'); ?></td>
                        </tr>
                    </tbody>
                </table>
                <h4><?php echo t('User Information'); ?></h4>
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th style="width:40%;"><?php echo t('Name'); ?></th>
                            <td><?php echo $chat->getName() ?: t('Not set'); ?></td>
                        </tr>
                        <tr>
                            <th style="width:40%;"><?php echo t('Email'); ?></th>
                            <td>
                                <?php if ($chat->getEmail()): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($chat->getEmail()); ?>">
                                        <?php echo htmlspecialchars($chat->getEmail()); ?>
                                    </a>
                                <?php else: ?>
                                    <?php echo t('Not set'); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="width:40%;"><?php echo t('Phone'); ?></th>
                            <td><?php echo $chat->getPhone() ?: t('Not set'); ?></td>
                        </tr>
                    </tbody>
                </table>
                <h4><?php echo t('UTM Tracking'); ?></h4>
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <th style="width:40%;"><?php echo t('UTM ID'); ?></th>
                            <td><?php echo $chat->getUtmId() ?: t('Not set'); ?></td>
                        </tr>
                        <tr>
                            <th style="width:40%;"><?php echo t('UTM Source'); ?></th>
                            <td><?php echo $chat->getUtmSource() ?: t('Not set'); ?></td>
                        </tr>
                        <tr>
                            <th style="width:40%;"><?php echo t('UTM Medium'); ?></th>
                            <td><?php echo $chat->getUtmMedium() ?: t('Not set'); ?></td>
                        </tr>
                        <tr>
                            <th style="width:40%;"><?php echo t('UTM Campaign'); ?></th>
                            <td><?php echo $chat->getUtmCampaign() ?: t('Not set'); ?></td>
                        </tr>
                        <tr>
                            <th style="width:40%;"><?php echo t('UTM Term'); ?></th>
                            <td><?php echo $chat->getUtmTerm() ?: t('Not set'); ?></td>
                        </tr>
                        <tr>
                            <th style="width:40%;"><?php echo t('UTM Content'); ?></th>
                            <td><?php echo $chat->getUtmContent() ?: t('Not set'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6">
                <div class="row mt-4">
                    <!-- Complete Chat History -->
                    <div class="col-12">
                        <?php if ($chat->getCompleteChatHistory()): ?>
                            <?php
                            $chatHistory = json_decode($chat->getCompleteChatHistory(), true);
                            if (is_array($chatHistory) && !empty($chatHistory)): ?>
                                <?php foreach ($chatHistory as $message): ?>
                                    <div class="<?php echo $message['sender'] === 'user' ? 'user-message' : 'ai-response'; ?>">
                                        <div class="message-content">
                                            <i class="fa <?php echo $message['sender'] === 'user' ? 'fa-user' : 'fa-robot'; ?> me-1"></i>
                                            <strong><?php echo $message['sender'] === 'user' ? t('You') : t('AI Assistant'); ?></strong>

                                            <small class="ms-auto me-2">
                                                <?php echo isset($message['timestamp']) ? date('H:i:s', $message['timestamp'] / 1000) : ''; ?>
                                            </small>

                                            <?php if (isset($message['content']) && is_string($message['content'])): ?>
                                                <?php if ($message['sender'] === 'ai'): ?>
                                                    <?php echo nl2br($message['content']); ?>
                                                <?php else: ?>
                                                    <?php echo nl2br(htmlspecialchars($message['content'])); ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <em class="text-muted"><?php echo t('Message content not available'); ?></em>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                            <?php else: ?>
                                <em class="text-muted"><?php echo t('No chat history available'); ?></em>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <em class="text-muted"><?php echo t('Chat history not yet implemented'); ?></em>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
