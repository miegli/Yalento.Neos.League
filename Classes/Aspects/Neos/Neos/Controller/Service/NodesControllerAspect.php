<?php

namespace Yalento\Neos\League\Aspects\Neos\Neos\Controller\Service;

use Neos\ContentRepository\Domain\Model\Node;
use Neos\ContentRepository\Domain\Repository\NodeDataRepository;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\AOP\JoinPointInterface;
use Neos\Flow\Mvc\ActionRequest;

/**
 * @Flow\Aspect
 */
class NodesControllerAspect
{

    /**
     * @Flow\Inject
     * @var NodeDataRepository
     */
    protected $nodeDataRepository;

    /**
     * NodesController indexAction
     *
     * @Flow\Around("method(Neos\Neos\Controller\Service\NodesController->indexAction())")
     * @param JoinPointInterface $joinPoint
     */
    public function indexAction(JoinPointInterface $joinPoint)
    {
        /** @var ActionRequest $request */
        $request = $joinPoint->getProxy()->getControllerContext()->getRequest();

        if ($joinPoint->getMethodArgument('nodeTypes') &&
            $joinPoint->getMethodArgument('nodeTypes')[0] === 'Yalento.Neos.League:Document.TableTeam' &&
            $joinPoint->getMethodArgument('contextNode')
        ) {
            if ($request->getHttpRequest()->getHeader('referer')) {
                parse_str(parse_url($request->getHttpRequest()->getHeader('referer')[0], PHP_URL_QUERY), $query);
                if ($query['node']) {
                    /** @var Node $contextNode */
                    $contextNode = $joinPoint->getMethodArgument('contextNode');
                    $refererQuery = new FlowQuery([$contextNode]);
                    $refererNode = $refererQuery->find(explode("@", $query['node'])[0])->get(0);
                    if ($refererNode) {
                        $joinPoint->setMethodArgument('contextNode', $refererNode);
                    }
                }
            }
        }

        if ($joinPoint->getMethodArgument('nodeTypes') &&
            $joinPoint->getMethodArgument('nodeTypes')[0] === 'Yalento.Neos.League:Document.Team' &&
            $joinPoint->getMethodArgument('contextNode')
        ) {
            if ($request->getHttpRequest()->getHeader('referer')) {
                parse_str(parse_url($request->getHttpRequest()->getHeader('referer')[0], PHP_URL_QUERY), $query);
                if ($query['node']) {
                    /** @var Node $contextNode */
                    $contextNode = $joinPoint->getMethodArgument('contextNode');
                    $refererQuery = new FlowQuery([$contextNode]);
                    $refererNode = $refererQuery->find(explode("@", $query['node'])[0])->get(0);
                    if ($refererNode) {
                        $seasonNodeQuery = new FlowQuery([$refererNode]);
                        $seasonNode = $seasonNodeQuery->closest('[instanceof Yalento.Neos.League:Document.Season]')->get(0);
                        if ($seasonNode) {
                            $joinPoint->setMethodArgument('contextNode', $seasonNode);
                        }
                    }
                }
            }
        }

        return $joinPoint->getAdviceChain()->proceed($joinPoint);
    }

}
