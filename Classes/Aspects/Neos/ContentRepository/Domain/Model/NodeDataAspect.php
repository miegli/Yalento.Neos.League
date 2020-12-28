<?php

namespace Yalento\Neos\League\Aspects\Neos\ContentRepository\Domain\Model;

use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\Eel\Helper\DateHelper;
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
        $this->autoCreateNodeService->createTournamentGamesFromChildNodeDefaultValues($nodeData);

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
            $this->postProcessUpdateTournamentDate($node, $value);
        }

        if ($node->getNodeType()->isOfType('Yalento.Neos.League:Content.Tournament') && $propertyName === 'startTime') {
            $this->postProcessUpdateTournamentStartTime($node, $value);
        }

        if ($node->getNodeType()->isOfType('Yalento.Neos.League:Content.Game')) {
            $this->postProcessUpdateGame($node, $value);
        }


    }

    /**
     * @param Node $node
     * @param $value
     * @throws \Neos\ContentRepository\Exception\NodeException
     * @throws \Neos\ContentRepository\Exception\NodeTypeNotFoundException
     * @throws \Neos\Flow\Property\Exception
     * @throws \Neos\Flow\Security\Exception
     */
    private function postProcessUpdateGame(Node $node, $value)
    {
        if (!$value) {
            /**
             * reset auto-generated data
             */
            $this->autoCreateNodeService->createTournamentGamesFromChildNodeDefaultValues($node->getNodeData());
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

    /**
     * @param Node $node
     * @param $value
     * @throws \Neos\ContentRepository\Exception\NodeException
     * @throws \Neos\ContentRepository\Exception\NodeTypeNotFoundException
     * @throws \Neos\Flow\Property\Exception
     * @throws \Neos\Flow\Security\Exception
     */
    private function postProcessUpdateTournamentDate(Node $node, $value)
    {
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

    /**
     * @param Node $node
     * @param $value
     * @throws \Neos\ContentRepository\Exception\NodeException
     * @throws \Neos\ContentRepository\Exception\NodeTypeNotFoundException
     * @throws \Neos\Flow\Property\Exception
     * @throws \Neos\Flow\Security\Exception
     */
    private function postProcessUpdateTournamentStartTime(Node $node, $value)
    {
        /** @var \DateTime $tournamentStartTime */
        $tournamentStartTime = $value;
        /** @var \DateTime $tournamentDate */
        $tournamentDate = $node->getProperty('date');
        $helper = new DateHelper();

        if (!$node->getProperty('date')) {
            return;
        }

        $childNodeConfiguration = $node->getNodeType()->getConfiguration('childNodes');

        /** @var \DateInterval $deltaDateInterval * */
        $deltaDateInterval = null;

        /** @var Node $childNode * */
        foreach ($node->findChildNodes() as $childNode) {
            if ($childNode->getNodeType()->isOfType('Yalento.Neos.League:Content.Game')) {

                if (isset($childNodeConfiguration[$childNode->getNodeName()->jsonSerialize()]) && isset($childNodeConfiguration[$childNode->getNodeName()->jsonSerialize()]['defaultValue'])) {
                    $defaultValueProperties = $childNodeConfiguration[$childNode->getNodeName()->jsonSerialize()]['defaultValue'];
                    if (isset($defaultValueProperties['date']) && preg_match('/([0-9]{2}):([0-9]{2})/', $defaultValueProperties['date'])) {
                        list($hours, $minutes) = explode(":", $defaultValueProperties['date']);

                        /** @var \DateTime $gameDate */
                        $gameDate = $childNode->getProperty('date');
                        $gameDate->setDate(intval($tournamentDate->format('Y')), intval($tournamentDate->format('m')), intval($tournamentDate->format('d')));
                        $gameDate->setTime(intval($hours), intval($minutes), 0);

                        if ($deltaDateInterval === null && $tournamentStartTime) {
                            $tournamentStartTime->setDate(intval($tournamentDate->format('Y')), intval($tournamentDate->format('m')), intval($tournamentDate->format('d')));
                            $deltaDateInterval = $helper->diff($gameDate, $tournamentStartTime);
                        }

                        if ($deltaDateInterval) {
                            $childNode->setProperty('date', $helper->add($gameDate, $deltaDateInterval));
                        } else {
                            $childNode->setProperty('date', $gameDate);
                        }

                    }

                }
            }
        }
    }
}
