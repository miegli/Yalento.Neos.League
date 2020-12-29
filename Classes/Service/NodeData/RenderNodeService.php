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
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Mvc\Routing\Dto\RouteParameters;
use Neos\Flow\Security\Authentication\AuthenticationProviderManager;
use Neos\Flow\Security\Context;
use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Domain\Service\ContentContext;
use Neos\Neos\Domain\Service\ContentContextFactory;
use Neos\Neos\Domain\Service\UserService;
use Neos\Neos\Routing\FrontendNodeRoutePartHandler;
use Neos\Neos\Service\NodeOperations;
use Neos\Neos\View\FusionView;

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

    /**
     * @var FusionView
     */
    protected $view;


    public function toJson(string $nodeIdentifier, Workspace $workspace = null, RouteParameters $routeParameters, ControllerContext $controllerContext): ?string
    {
        $node = $this->resolveNodeFromNodeIdentifier($nodeIdentifier, $workspace ? $workspace->getName() : 'live', $routeParameters);
        if (!$node) {
            return null;
        }

        $this->view = new FusionView();
        $this->view->setControllerContext($controllerContext);
        $this->view->setFusionPath('json');
        $this->view->assign('value', $node);
        return json_encode($this->view->render());
    }


    private function resolveNodeFromNodeIdentifier(string $nodeIdentifier, string $workspaceName, RouteParameters $routeParameters): ?Node
    {
        $this->parameters = $routeParameters;
        $contentContext = $this->buildContextFromWorkspaceName($workspaceName);
        $nodeData = $this->nodeDataRepository->findOneByIdentifier($nodeIdentifier, $contentContext->getWorkspace(), $contentContext->getDimensions());

        if (!$nodeData) {
            return null;
        }
        $baseNode = new Node($nodeData, $contentContext);
        $flowQuery = new FlowQuery([$baseNode]);
        return $flowQuery->find("#" . $nodeIdentifier)->get(0);
    }


}
