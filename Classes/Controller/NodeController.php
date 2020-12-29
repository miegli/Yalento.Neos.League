<?php

namespace Yalento\Neos\League\Controller;

/*
 * This file is part of the Yalento.Neos.League package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Routing\Dto\RouteParameters;
use Neos\Neos\Service\UserService;
use Yalento\Neos\League\Service\NodeData\RenderNodeService;

class NodeController extends ActionController
{


    /**
     * @Flow\Inject
     * @var RenderNodeService
     */
    protected $renderNodeService;

    /**
     * @Flow\Inject
     * @var UserService
     */
    protected $userService;


    /**
     * @param string $node
     * @return string
     */
    public function jsonAction(string $nodeIdentifier): string
    {
        $routeParameters = $this->request->getHttpRequest()->getAttribute('routingParameters') ?? RouteParameters::createEmpty();
        $json = $this->renderNodeService->toJson($nodeIdentifier, $this->userService->getPersonalWorkspace(), $routeParameters);

        if (!$json) {
            $this->response->setStatusCode(404);
            return '';
        }

        return $json;

    }
}
