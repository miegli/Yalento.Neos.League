<?php

namespace Yalento\Neos\League\Domain\Model;

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
use Neos\ContentRepository\Domain\Model\NodeLabelGeneratorInterface;

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
class NodeLabelGenerator implements NodeLabelGeneratorInterface
{

    public function getLabel(\Neos\ContentRepository\Domain\Projection\Content\NodeInterface $node): string
    {

        if ($node->getNodeType()->isOfType('Yalento.Neos.League:Content.Tournament')) {

            if ($node->getProperty('title')) {
                $name = $node->getProperty('title');
                if ($node->getProperty('date')) {
                    $name .= ' (' . $this->formatDate($node->getProperty('date'), 'd.m.Y') . ')';
                }
                return $name;
            }

            $name = '(' . explode("round", $node->getNodeName())[1] . ')';
            if ($node->getProperty('date')) {
                $name .= ' ' . $this->formatDate($node->getProperty('date'), 'd.m.Y');
            }
            return $name;
        }

        if ($node->getNodeType()->isOfType('Yalento.Neos.League:Document.TableTeam')) {
            $number = explode("team", $node->getNodeName())[1];

            if ($node->getProperty('team')) {
                $name = '(' . $number . ') ' . $node->getProperty('team')->getProperty('title');
            } else {
                $name = '(' . $number . ')' . '';
            }

            if ($node->getProperty('retreat')) {
                $name .= ' ❌';
            }

            return $name;
        }

        if ($node->getNodeType()->isOfType('Yalento.Neos.League:Content.Game')) {

            $name = '';

            if ($node->getProperty('date')) {
                $name .= ' ⏰ ' . $this->formatDate($node->getProperty('date'), 'H:i');
            }

            if ($node->getProperty('title')) {
                $name .= ' ' . $node->getProperty('title');
            }

            if ($node->getProperty('home')) {
                $name .= ' 🏡 ' . $node->getProperty('home')->getLabel();
            }

            if ($node->getProperty('away')) {
                $name .= ' 🛣️ ' . $node->getProperty('away')->getLabel();
            }

            return $name;
        }

        return '';
    }

    private function formatDate(\DateTime $dateTime, string $format): string
    {

        if (!$dateTime) {
            return '';
        }

        return $dateTime->setTimezone(new \DateTimeZone('Europe/Zurich'))->format($format);

    }

}
