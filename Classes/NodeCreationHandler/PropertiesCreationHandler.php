<?php

namespace Yalento\Neos\League\NodeCreationHandler;

/*
 * This file is part of the Yalento.Neos.League package.
 */

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Ui\NodeCreationHandler\NodeCreationHandlerInterface;
use Yalento\Neos\League\Service\NodeData\AutoCreateNodeService;

class PropertiesCreationHandler implements NodeCreationHandlerInterface
{

    /**
     * @Flow\Inject
     * @var AutoCreateNodeService
     */
    protected $autoCreateNodeService;

    public function handle(NodeInterface $node, array $data)
    {
        if ($data) {
            foreach ($data as $key => $value) {
                $node->setProperty($key, $value);
            }
        }

        $this->autoCreateNodeService->createFromAutogenerateProperty($node);

    }
}
