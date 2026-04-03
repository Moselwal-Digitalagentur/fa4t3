<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Mutation;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationCollection;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationMode;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\UriValue;
use TYPO3\CMS\Core\Type\Map;

return Map::fromEntries(
    // Backend: Allow Fathom share dashboard iframe
    [Scope::backend(), new MutationCollection(
        new Mutation(
            MutationMode::Extend,
            Directive::FrameSrc,
            new UriValue('https://app.usefathom.com'),
        ),
    )],
    // Frontend: Allow Fathom tracking script and API connections
    [Scope::frontend(), new MutationCollection(
        new Mutation(
            MutationMode::Extend,
            Directive::ScriptSrc,
            new UriValue('https://cdn.usefathom.com'),
        ),
        new Mutation(
            MutationMode::Extend,
            Directive::ConnectSrc,
            new UriValue('https://cdn.usefathom.com'),
        ),
    )],
);
