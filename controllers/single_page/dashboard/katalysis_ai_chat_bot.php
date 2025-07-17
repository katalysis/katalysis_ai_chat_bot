<?php

namespace Concrete\Package\KatalysisAiChatBot\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;

class KatalysisAiChatBot extends DashboardPageController
{
    public function view()
    {
        \Log::info("view");
        return $this->buildRedirectToFirstAccessibleChildPage();
    }
}
