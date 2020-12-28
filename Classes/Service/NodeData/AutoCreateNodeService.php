<?php

namespace Yalento\Neos\League\Service\NodeData;


use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\Helper\DateHelper;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Service\NodeOperations;

/**
 * Service to determine if a given node matches a series of filters given by configuration.
 *
 * @Flow\Scope("singleton")
 */
class AutoCreateNodeService
{

    private $timeZone = 'Europe/Zurich';

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * @var NodeOperations
     * @Flow\Inject
     */
    protected $nodeOperations;

    /**
     * @param NodeData $nodeData nodeType that contains 'defaultValue' in childNode configuration
     * @throws \Neos\ContentRepository\Exception\NodeTypeNotFoundException
     */
    public function createTournamentGamesFromChildNodeDefaultValues(NodeData $nodeData): NodeData
    {
        if ($nodeData->getParent()) {

            /** @var NodeType $nodeType */
            $nodeType = $nodeData->getParent()->getNodeType();
            $parentNode = $nodeData->getParent()->getParent();
            if ($parentNode) {
                $parentNode = $parentNode->getParent();
            }

            if ($nodeType->getConfiguration('childNodes')) {
                $index = 0;
                /** @var \DateInterval $deltaDateInterval * */
                $deltaDateInterval = null;
                /** @var \DateTime $tournamentStartTime */
                $tournamentStartTime = $nodeData->getParent()->getProperty('startTime');
                if (is_array($tournamentStartTime)) {
                    $tournamentStartTime = new \DateTime($tournamentStartTime['date'], new \DateTimeZone('UTC'));
                    $tournamentStartTime->setTimezone(new \DateTimeZone($this->timeZone));
                }
                /** @var \DateTime $tournamentDate */
                $tournamentDate = $nodeData->getParent()->getProperty('date');
                if (is_array($tournamentDate)) {
                    $tournamentDate = new \DateTime($tournamentDate['date'], new \DateTimeZone('UTC'));
                    $tournamentDate->setTimezone(new \DateTimeZone($this->timeZone));
                }

                foreach ($nodeType->getConfiguration('childNodes') as $childNodeKey => $childNode) {
                    $index++;

                    /** pre calculate delta startTime */
                    if ($index === 1 && !empty($childNode['defaultValue'])) {
                        $childNodeDefaultProperties = $childNode['defaultValue'];
                        foreach ($childNodeDefaultProperties as $key => $value) {
                            if ($key === 'date' && preg_match('/([0-9]{2}):([0-9]{2})/', $value)) {
                                list($hours, $minutes) = explode(":", $value);

                                if ($tournamentDate) {
                                    $value = new \DateTime($tournamentDate->format('Y-m-d H:i:00'), new \DateTimeZone($this->timeZone));
                                } else {
                                    $value = new \DateTime('now', new \DateTimeZone('UTC'));
                                }

                                $value->setTime(intval($hours), intval($minutes), 0);
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


                            }
                        }
                    }


                    if (!empty($childNode['defaultValue']) && $childNodeKey === $nodeData->getName()) {
                        $childNodeDefaultProperties = $childNode['defaultValue'];
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
                                        $helper = new DateHelper();

                                        if ($tournamentDate) {
                                            $value = new \DateTime($tournamentDate->format('Y-m-d H:i:00'), new \DateTimeZone($this->timeZone));
                                        } else {
                                            $value = new \DateTime('now', new \DateTimeZone('UTC'));
                                        }

                                        $value->setTime(intval($hours), intval($minutes), 0);

                                        if ($deltaDateInterval) {
                                            $value = $helper->add($value, $deltaDateInterval);
                                        }

                                    } else {
                                        $value = new \DateTime($value, new \DateTimeZone($this->timeZone));
                                    }

                                    if ($index === 1 && !$tournamentStartTime) {
                                        $value->setTimezone(new \DateTimeZone('UTC'));
                                        $nodeData->getParent()->setProperty('startTime', $value);
                                    }

                                }

                                if (is_string($value) && preg_match('/\$index/', $value)) {
                                    $value = str_replace('$index', $index, $value);
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

    /**
     * @param NodeInterface $node
     * @throws \Neos\ContentRepository\Exception\NodeException
     */
    public function createFromAutogenerateProperty(TraversableNodeInterface $node)
    {

        if ($node->getNodeType()->isOfType('Yalento.Neos.League:Document.Table')) {
            $autogenerateProperty = $node->getProperty('autogenerate');
            if (!$autogenerateProperty) {
                $autogenerateProperty = $node->findParentNode()->getProperty('autogenerate');
            }

            if (!$autogenerateProperty) {
                $autogenerateProperty = $node->findParentNode()->findParentNode()->getProperty('autogenerate');
            }

            if (!$autogenerateProperty) {
                $autogenerateProperty = $node->findParentNode()->findParentNode()->findParentNode()->getProperty('autogenerate');
            }

            if (!$autogenerateProperty) {
                return;
            }

            $flowQuery = new FlowQuery(array($node));
            $baseNode = $flowQuery->find('[instanceof Yalento.Neos.League:ContentCollection.Games]')->get(0);

            if (!$baseNode) {
                return;
            }

            $tableSize = explode("Yalento.Neos.League:ContentCollection.Games.", $baseNode->getNodeType()->getName())[1];
            switch ($autogenerateProperty) {
                case 'tournamentsSingleRound':
                    $this->nodeOperations->create($baseNode, ['nodeType' => 'Yalento.Neos.League:Content.Tournaments.' . $tableSize . '.SingleRound'], 'into');
                    break;
                case 'tournamentsDoubleRound':
                    $this->nodeOperations->create($baseNode, ['nodeType' => 'Yalento.Neos.League:Content.Tournaments.' . $tableSize . '.DoubleRound'], 'into');
                    break;
                case 'tournamentsSingleGameSingleRound':
                    $this->nodeOperations->create($baseNode, ['nodeType' => 'Yalento.Neos.League:Content.TournamentsSingleGame.' . $tableSize . '.SingleRound'], 'into');
                    break;
                case 'tournamentsSingleGameDoubleRound':
                    $this->nodeOperations->create($baseNode, ['nodeType' => 'Yalento.Neos.League:Content.TournamentsSingleGame.' . $tableSize . '.DoubleRound'], 'into');
                    break;
                case 'singleGamesSingleRound':
                    $this->nodeOperations->create($baseNode, ['nodeType' => 'Yalento.Neos.League:Content.SingleGames.' . $tableSize . '.SingleRound'], 'into');
                    break;
                case 'singleGamesDoubleRound':
                    $this->nodeOperations->create($baseNode, ['nodeType' => 'Yalento.Neos.League:Content.SingleGames.' . $tableSize . '.DoubleRound'], 'into');
                    break;
            }
        }

    }
}
