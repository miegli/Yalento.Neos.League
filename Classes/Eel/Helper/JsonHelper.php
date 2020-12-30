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

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use phpDocumentor\Reflection\Types\Boolean;

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
class JsonHelper implements ProtectedContextAwareInterface
{


    /**
     * Get nodeType to render in json path
     *
     * @return string
     */
    public function nodeType(NodeInterface $node): string
    {
        return $this->calculateRendererNodeType($node, false);
    }

    /**
     * Get nodeType to render in json path
     *
     * @return string
     */
    public function modelType(NodeInterface $node): string
    {
        return $this->calculateRendererNodeType($node, true);
    }

    /**
     * @param NodeInterface $node
     * @param string $property
     * @return String|null
     * @throws \Neos\ContentRepository\Exception\NodeException
     */
    public function getProperty(NodeInterface $node, string $property)
    {

        $properties = explode(".", $property);
        $child = $node->getProperty($properties[0]);
        for ($i = 1; $i <= count($properties); ++$i) {
            if ($child instanceof NodeInterface && isset($properties[$i])) {
                $child = $child->getProperty($properties[$i]);
            }
        }

        return $child;
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

    private function calculateRendererNodeType(NodeInterface $node, bool $calculateModelName): string
    {

        $renderNodeType = str_replace('Yalento.Neos.League:Document', 'Yalento.Neos.League:Json', $node->getNodeType()->getName());
        $renderNodeType = str_replace('Yalento.Neos.League:Content', 'Yalento.Neos.League:Json', $renderNodeType);
        $renderNodeType = preg_split('/[0-9].*$/', $renderNodeType)[0];
        $renderNodeType = preg_replace('/[^A-z]$/', '', $renderNodeType);

        if ($calculateModelName) {
            $list = explode(".", $renderNodeType);
            return $list[count($list) - 1];
        }

        return $renderNodeType;

    }

}
