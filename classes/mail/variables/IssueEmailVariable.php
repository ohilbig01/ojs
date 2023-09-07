<?php

/**
 * @file classes/mail/variables/IssueEmailVariable.php
 *
 * Copyright (c) 2014-2023 Simon Fraser University
 * Copyright (c) 2000-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class IssueEmailVariable
 *
 * @ingroup mail_variables
 *
 * @brief Email template variables for an issue.
 */

namespace APP\mail\variables;

use APP\core\Application;
use APP\issue\Issue;
use APP\pages\issue\IssueHandler;
use APP\template\TemplateManager;
use PKP\mail\Mailable;
use PKP\mail\variables\Variable;

class IssueEmailVariable extends Variable
{
    public const ISSUE_ID = 'issueId';
    public const ISSUE_IDENTIFICATION = 'issueIdentification';
    public const ISSUE_URL = 'issueUrl';
    public const ISSUE_TOC = 'issueToc';

    protected Issue $issue;

    public function __construct(Issue $issue, Mailable $mailable)
    {
        parent::__construct($mailable);

        $this->issue = $issue;
    }

    public static function descriptions(): array
    {
        return
        [
            static::ISSUE_ID => __('emailTemplate.variable.issueId'),
            static::ISSUE_IDENTIFICATION => __('emailTemplate.variable.issue.issueIdentification'),
            static::ISSUE_URL => __('emailTemplate.variable.issue.issuePublishedUrl'),
            static::ISSUE_TOC => __('emailTemplate.variable.issue.issueTableOfContents'),
        ];
    }

    public function values(string $locale): array
    {
        return
        [
            static::ISSUE_ID => $this->issue->getId(),
            static::ISSUE_IDENTIFICATION => htmlspecialchars($this->issue->getIssueIdentification()),
            static::ISSUE_URL => $this->getIssueUrl(),
            static::ISSUE_TOC => $this->getIssueToc(),
        ];
    }

    protected function getIssueUrl(): string
    {
        return Application::get()->getDispatcher()->url(
            Application::get()->getRequest(),
            Application::ROUTE_PAGE,
            $this->getContext()->getPath(),
            'issue',
            'view',
            $this->issue->getBestIssueId()
        );
    }

    protected function getIssueToc(): string
    {
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);

        IssueHandler::_setupIssueTemplate($request, $this->issue, $this->getContext(), true, false);

        $templateMgr->assign([
            'includeIssuePublishDate' => false,
        ]);

        return $templateMgr->fetch('frontend/objects/issue_toc.tpl');
    }
}
