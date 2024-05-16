<?php

namespace AndrasOtto\Csp\ViewHelpers;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use AndrasOtto\Csp\Domain\Model\Iframe;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Renders an Iframe tag
 *
 * Class IframeViewHelper
 */
class IframeViewHelper extends AbstractTagBasedViewHelper
{
    protected $tagName = 'iframe';

    /**
     * Initialize arguments.
     */
    #[\Override]
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('src', 'string', 'Specifies the source for the iframe', true);
        $this->registerTagAttribute('name', 'string', 'Specifies the name for the iframe', false);
        $this->registerTagAttribute('width', 'int', 'Specifies the width of the iframe', false);
        $this->registerTagAttribute('height', 'int', 'Specifies the height of the iframe', false);
        $this->registerTagAttribute('sandbox', 'string', 'Specifies the sandbox values (comma separated) for the iframe', false);
        $this->registerTagAttribute('allowFullScreen', 'bool', 'Specifies if the iframe can be used in full screen mode', false);
        $this->registerTagAttribute('allowPaymentRequest', 'bool', 'Specifies if payment is allowed in the iframe', false);
    }

    /**
     * Renders the iframe tag
     *
     * @return string
     */
    #[\Override]
    public function render()
    {
        $iframe = new Iframe(
            $this->arguments['src'],
            $this->arguments['class'] ?? '',
            $this->arguments['name'] ?? '',
            $this->arguments['width'] ?? 0,
            $this->arguments['height'] ?? 0,
            $this->arguments['sandbox'] ?? '',
            $this->arguments['allowPaymentRequest'] ?? false,
            $this->arguments['allowFullScreen'] ?? false
        );

        $iframe->registerSrcHost();

        $this->tag->addAttribute('src', $iframe->getSrc());

        if ($iframe->getClass()) {
            $this->tag->addAttribute('name', $iframe->getName());
        }

        if ($iframe->getClass()) {
            $this->tag->addAttribute('class', $iframe->getClass());
        }
        if ($iframe->getWidth()) {
            $this->tag->addAttribute('width', $iframe->getWidth());
        }
        if ($iframe->getHeight()) {
            $this->tag->addAttribute('height', $iframe->getHeight());
        }
        if (count($iframe->getSandbox()) > 0) {
            $this->tag->addAttribute('sandbox', implode(' ', $iframe->getSandbox()));
        }
        if ($iframe->isAllowFullScreen()) {
            $this->tag->addAttribute('allowfullscreen', 'allowfullscreen');
        }
        if ($iframe->isAllowPaymentRequest()) {
            $this->tag->addAttribute('allowpaymentrequest', 'allowpaymentrequest');
        }

        $this->tag->forceClosingTag(true);

        return $this->tag->render();
    }
}
