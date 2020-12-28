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

    private $timeZone = 'Europe/Zurich';

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

        if ($node->getNodeType()->isOfType('Yalento.Neos.League:Content.Game') &&
            ($propertyName === 'date' || $propertyName === 'home' || $propertyName === 'away' || $propertyName === 'place')
        ) {
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

        if (!$node->getProperty('date') || !$value) {
            return;
        }

        /** @var \DateTime $tournamentDate */
        $tournamentDate = new \DateTime($node->getProperty('date')->format('Y-m-d 00:00:00'), new \DateTimeZone($this->timeZone));;
        /** @var \DateTime $tournamentStartTime */
        $tournamentStartTime = new \DateTime($tournamentDate->format('Y-m-d') . ' ' . $value->format('H:i:00'), new \DateTimeZone($this->timeZone));
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

                        $gameDate = new \DateTime($tournamentDate->format("Y-m-d $hours:$minutes:00"), new \DateTimeZone($this->timeZone));

                        if ($deltaDateInterval === null && $tournamentStartTime) {
                            list($tournamentStartTimeHours, $tournamentStartTimeMinutes) = explode(":", $tournamentStartTime->format("H:i"));
                            $tournamentStartTimeHours = intval($tournamentStartTimeHours);
                            $tournamentStartTimeMinutes = intval($tournamentStartTimeMinutes);
                            $defaultGameStartTimeHours = intval($hours);
                            $defaultGameStartTimeMinutes = intval($minutes);
                            $minutes1 = ($tournamentStartTimeHours * 60.0 + $tournamentStartTimeMinutes);
                            $minutes2 = ($defaultGameStartTimeHours * 60.0 + $defaultGameStartTimeMinutes);
                            $diff = $minutes1 - $minutes2;
                            $deltaDateInterval = new \DateInterval('PT' . abs($diff) . 'M');
                            if ($diff < 0) {
                                $deltaDateInterval->invert = 1;
                            }
                        }

                        if ($deltaDateInterval) {
                            $gameDate = $helper->add($gameDate, $deltaDateInterval);
                        }

                        $childNode->setProperty('date', $gameDate);


                    }

                }
            }
        }
    }
}
