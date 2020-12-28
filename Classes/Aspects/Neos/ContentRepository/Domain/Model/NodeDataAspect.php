<?php

namespace Yalento\Neos\League\Aspects\Neos\ContentRepository\Domain\Model;

use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\AOP\JoinPointInterface;
use Neos\ContentRepository\Domain\Model\Node;
use Yalento\Neos\League\Service\NodeData\AutoCreateNodeService;


/**
 * @Flow\Aspect
 */
class NodeDataAspect
{


    /**
     * @Flow\Inject
     * @var AutoCreateNodeService
     */
    protected $autoCreateNodeService;


    /**
     * createSingleNode around
     *
     * @Flow\Around("method(Neos\ContentRepository\Domain\Model\NodeData->createSingleNodeData())")
     * @param JoinPointInterface $joinPoint
     */
    public function createSingleNodeAround(JoinPointInterface $joinPoint)
    {

        /** @var NodeData $nodeData */
        $nodeData = $joinPoint->getAdviceChain()->proceed($joinPoint);
        $this->autoCreateNodeService->createFromChildNodeDefaultValues($nodeData);

        return $nodeData;
    }


    /**
     * NodesController indexAction
     *
     * @Flow\After("method(Neos\ContentRepository\Domain\Model\Node->setProperty())")
     * @param JoinPointInterface $joinPoint
     */
    public function updateNode(JoinPointInterface $joinPoint)
    {
        /** @var Node $node */
        $node = $joinPoint->getProxy();
        $propertyName = $joinPoint->getMethodArgument('propertyName');
        $value = $joinPoint->getMethodArgument('value');

        if ($node->getNodeType()->isOfType('Yalento.Neos.League:Content.Tournament') && $propertyName === 'date') {
            /** @var \DateTime $tournamentDate */
            $tournamentDate = $value;

            if (!$tournamentDate) {
                return;
            }
            /** @var Node $childNode * */
            foreach ($node->findChildNodes() as $childNode) {
                if ($childNode->getNodeType()->isOfType('Yalento.Neos.League:Content.Game') && $childNode->getProperty('date')) {
                    /** @var \DateTime $gameDate */
                    $gameDate = $childNode->getProperty('date');
                    $gameDate->setDate(intval($tournamentDate->format('Y')), intval($tournamentDate->format('m')), intval($tournamentDate->format('d')));
                    $childNode->setProperty('date', $gameDate);
                }
            }
        }

        if ($node->getNodeType()->isOfType('Yalento.Neos.League:Content.Game')) {

            if (!$value) {
                /**
                 * reset auto-generated data
                 */
                $this->autoCreateNodeService->createFromChildNodeDefaultValues($node->getNodeData());
            }

            /**
             * reset date from tournament
             */
            /** @var \DateTime $tournamentDate */
            $tournamentDate = $node->findParentNode()->getProperty('date');
            if ($tournamentDate) {
                /** @var \DateTime $gameDate */
                $gameDate = $node->getProperty('date');
                $gameDate->setDate(intval($tournamentDate->format('Y')), intval($tournamentDate->format('m')), intval($tournamentDate->format('d')));
                $node->setProperty('date', $gameDate);
            }

        }


    }


}
