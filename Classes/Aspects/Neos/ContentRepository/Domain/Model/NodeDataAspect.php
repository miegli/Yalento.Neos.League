<?php

namespace Yalento\Neos\League\Aspects\Neos\ContentRepository\Domain\Model;

use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\AOP\JoinPointInterface;


/**
 * @Flow\Aspect
 */
class NodeDataAspect
{

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;


    /**
     * NodesController indexAction
     *
     * @Flow\Around("method(Neos\ContentRepository\Domain\Model\NodeData->createSingleNodeData())")
     * @param JoinPointInterface $joinPoint
     */
    public function createSingleNode(JoinPointInterface $joinPoint)
    {

        /** @var NodeData $nodeData */
        $nodeData = $joinPoint->getAdviceChain()->proceed($joinPoint);

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

        return $nodeData;
    }

}
