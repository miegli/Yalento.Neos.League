<?php

namespace Yalento\Neos\League\Eel\Helper;

/*
 * This file is part of the Neos.Eel package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;

/**
 * Array helpers for Eel contexts
 *
 * The implementation uses the JavaScript specificiation where applicable, including EcmaScript 6 proposals.
 *
 * See https://developer.mozilla.org/docs/Web/JavaScript/Reference/Global_Objects/Array for a documentation and
 * specification of the JavaScript implementation.
 *
 * @Flow\Proxy(false)
 */
class LabelHelper implements ProtectedContextAwareInterface
{


    /**
     * Wrap the incoming string in curly brackets
     *
     * @return string
     */
    public function team(): string
    {
        return 'test';
    }

    /**
     * All methods are considered safe, i.e. can be executed from within Eel
     *
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
