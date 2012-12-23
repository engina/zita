<?php
namespace Zita\Annotations;

use Zita\IAnnotation;
use Zita\Request;
use Zita\Response;
use Zita\Service;
use Zita\Dispatcher;

/**
 * @Secure annotation is used for access controlling.
 *
 * Allowed parameters.
 *
 * allow: allowed roles
 * deny : denied roles
 *
 * A special role is anonymous regardless of the underlying IUser and IAuthenticator implementation.
 *
 * Anonymous is the state where $request->user is null.
 *
 * Only one of the lists can be used, i.e. an allow list, or an deny list.
 *
 * Examples:
 * @Secure deny=anonymous; allow=user|moderator; order=deny,allow
 */
class SecureAnnotation implements IAnnotation
{
	public function __construct($allow, $deny, $order)
	{
	}

	public function preProcess (Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
	{
        if($req->params->auth != null)
        {
            $provider = $dispatcher->getSessionProvider();
            $session  = $provider->load($req->params->auth);
            $req->user = $session->user;
        }
	}

	public function postProcess (Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
	{
		
	}
}