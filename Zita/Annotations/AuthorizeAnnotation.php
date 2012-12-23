<?php
namespace Zita\Annotations;

use Zita\IAnnotation;
use Zita\Request;
use Zita\Response;
use Zita\Service;
use Zita\Dispatcher;
use Zita\Security\CouldNotAuthorizeException;

/**
 * @Authorize annotation is used for access controlling.
 *
 * Accepts comma separated list of groups allowed to access to the service.
 *
 * Pre-defined gorup names are "all" and "authenticated".
 *
 * all: allows anonymous access.
 * authenticated: allows only authenticated users to access to resource but their role does not matter.
 *
 * A request is said to be anonymous when Request->user is null.
 *
 * Examples:
 * @Authorize all
 * @Authorize authenticated
 * @Authorize moderator, admin
 */
class AuthorizeAnnotation implements IAnnotation
{
    private $allow = array();

	public function __construct($allow)
	{
        $groups = explode(',', $allow);
        foreach($groups as $group)
            $this->allow[] = trim($group);
	}

    /**
     * If user matches any of the allowed groups (including special all and authenticated groups) pre-processing
     * simply exist. If user is not in any of the allowed groups CouldNotAuthorizeException will be thrown.
     * @throws \Zita\Security\CouldNotAuthorizeException
     */
    public function preProcess (Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
	{
        if($req->params->auth != null)
        {
            $provider = $dispatcher->getSessionProvider();
            $session  = $provider->load($req->params->auth);
            $req->user = $session->user;
        }

        foreach($this->allow as $group)
        {
            switch($group)
            {
                case 'all':
                    return;
                case 'authenticated':
                    if($req->user != null)
                        return;
                    break;
                default:
                    if($req->user != null && $req->user->hasRole($group))
                        return;
            }
        }
        throw new CouldNotAuthorizeException();
	}

	public function postProcess (Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
	{
		
	}
}