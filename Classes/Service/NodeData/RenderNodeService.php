<?php

namespace Yalento\Neos\League\Service\NodeData;


use Neos\ContentRepository\Domain\Model\Node;
use Neos\ContentRepository\Domain\Model\NodeData;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\ContentRepository\Domain\Model\Workspace;
use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\Helper\DateHelper;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Routing\Dto\RouteParameters;
use Neos\Flow\Security\Authentication\AuthenticationProviderManager;
use Neos\Flow\Security\Context;
use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Domain\Service\ContentContext;
use Neos\Neos\Domain\Service\ContentContextFactory;
use Neos\Neos\Domain\Service\UserService;
use Neos\Neos\Routing\FrontendNodeRoutePartHandler;
use Neos\Neos\Service\NodeOperations;

/**
 * Service to determine if a given node matches a series of filters given by configuration.
 *
 * @Flow\Scope("singleton")
 */
class RenderNodeService extends FrontendNodeRoutePartHandler
{

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    public function toJson(string $nodeIdentifier, Workspace $workspace = null, RouteParameters $routeParameters): ?string
    {
        $node = $this->resolveNodeFromNodeIdentifier($nodeIdentifier, $workspace ? $workspace->getName() : 'live', $routeParameters);
        if (!$node) {
            return null;
        }
        return json_encode($node->getProperties());
    }

    private function resolveNodeFromNodeIdentifier(string $nodeIdentifier, string $workspaceName, RouteParameters $routeParameters): ?NodeData
    {
        $this->parameters = $routeParameters;
        $contentContext = $this->buildContextFromWorkspaceName($workspaceName);
        return $this->nodeDataRepository->findOneByIdentifier($nodeIdentifier, $contentContext->getWorkspace(), $contentContext->getDimensions());
    }


}
