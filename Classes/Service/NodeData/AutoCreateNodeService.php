<?php

namespace Yalento\Neos\League\Service\NodeData;


use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\Flow\Annotations as Flow;

/**
 * Service to determine if a given node matches a series of filters given by configuration.
 *
 * @Flow\Scope("singleton")
 */
class AutoCreateNodeService
{

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @param NodeData $nodeData nodeType that contains 'defaultValue' in childNode configuration
     * @throws \Neos\ContentRepository\Exception\NodeTypeNotFoundException
     */
    public function createFromChildNodeDefaultValues(NodeData $nodeData)
    {
        if ($nodeData->getParent()) {

            /** @var NodeType $nodeType */
            $nodeType = $nodeData->getParent()->getNodeType();
            $parentNode = $nodeData->getParent()->getParent();
            if ($parentNode) {
                $parentNode = $parentNode->getParent();
            }

            if ($nodeType->getConfiguration('childNodes')) {
                foreach ($nodeType->getConfiguration('childNodes') as $childNodeKey => $childNode) {
                    if (!empty($childNode['defaultValue']) && $childNodeKey === $nodeData->getName()) {
                        $childNodeDefaultProperties = $childNode['defaultValue'];
                        $nodeData->setProperty('title', 'test');
                        $childNodes = $parentNode && $parentNode->getParent() ? $parentNode->getParent()->getNodeType()->getConfiguration('childNodes') : array();

                        foreach ($childNodeDefaultProperties as $key => $value) {
                            if (isset($childNodes[$value])) {
                                $referenceNode = $this->nodeDataRepository->findOneByPath($parentNode->getParent()->getPath() . '/' . $value, $nodeData->getWorkspace());
                                if ($referenceNode) {
                                    $nodeData->setProperty($key, $referenceNode->getIdentifier());
                                }
                            } else {
                                if ($key === 'date') {
                                    if (preg_match('/([0-9]{2}):([0-9]{2})/', $value)) {
                                        list($hours, $minutes) = explode(":", $value);
                                        $value = new \DateTime('now', new \DateTimeZone("UTC"));
                                        $value->setTime(intval($hours), intval($minutes), 0);
                                    } else {
                                        $value = new \DateTime($value, new \DateTimeZone("UTC"));
                                    }
                                }
                                $nodeData->setProperty($key, $value);
                            }
                        }
                    }
                }
            }
        }
    }

}
