<?php

namespace Yalento\Neos\League\Service\NodeData;


use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Service\NodeOperations;

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
     * @var NodeOperations
     * @Flow\Inject
     */
    protected $nodeOperations;

    /**
     * @param NodeData $nodeData nodeType that contains 'defaultValue' in childNode configuration
     * @throws \Neos\ContentRepository\Exception\NodeTypeNotFoundException
     */
    public function createFromChildNodeDefaultValues(NodeData $nodeData): NodeData
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
                foreach ($nodeType->getConfiguration('childNodes') as $childNodeKey => $childNode) {
                    $index++;
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
                                        $value = new \DateTime('now', new \DateTimeZone("UTC"));
                                        $value->setTime(intval($hours), intval($minutes), 0);
                                    } else {
                                        $value = new \DateTime($value, new \DateTimeZone("UTC"));
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
