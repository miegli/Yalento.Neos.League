<?php

namespace Yalento\Neos\League\Security\RequestPattern;

/*
 * This file is part of the Neos.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\RequestInterface;
use Neos\Flow\Security\Exception\InvalidRequestPatternException;
use Neos\Flow\Security\RequestPatternInterface;
use Neos\Flow\Utility\Ip as IpUtility;
use Neos\Flow\Security\Authentication\AuthenticationManagerInterface;
use Neos\Flow\Security\Context;
use Neos\Flow\Http\Request;
use Neos\Flow\Utility\Environment;

/**
 * This class holds an URI pattern an decides, if a \Neos\Flow\Mvc\ActionRequest object matches against this pattern
 */
class ApplicationFirewallPattern implements RequestPatternInterface
{
    /**
     * @var array
     */
    protected $options;


    /**
     * @Flow\Inject
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @Flow\Inject
     * @var Context
     */
    protected $securityContext;


    /**
     * @Flow\Inject
     * @var Environment
     */
    protected $environment;

    /**
     * Expects options in the form array('uriPattern' => '<URI pattern>')
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Matches a \Neos\Flow\Mvc\RequestInterface against its set URL pattern rules
     *
     * @param RequestInterface $request The request that should be matched
     * @return boolean true if the pattern matched, false otherwise
     * @throws InvalidRequestPatternException
     */
    public function matchRequest(RequestInterface $request)
    {

        return true;

    }
}
