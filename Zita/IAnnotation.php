<?php
namespace Zita;

/**
 * Annotation interface.
 * 
 * Each annotation must implement this interface. Annotations can accept parameters
 * and can be run before, after (or both) controller->method invokation.
 * 
 * Method annotations overrides class wide annotations.
 *
 */
interface IAnnotation
{
	/**
	 * Annotations can accept parameters.
     *
     * Parameters are free form, you can do whatever you like with them
	 * \@Foo param
	 * These will be fed to Annotation implementation's constructor.
     *
     * You can use Core::parseParams() to parse "param1=value1; param2 = some value; foo" like parameters.
     *
     * @see   Core::parseParams()
	 * @param $string $paramString
	 */
	public function __construct($paramString);
	
	/**
	 * Run just before service->method is going to be invoked.
     *
     * Can throw an exception which will practically stop any remaining pre-annotations to be procesed and
     * service invokation to be skipped. Annotation post-processing will be done however. This is to allow output
     * formatting of the exception thrown in the Annotation pre-processing.
     *
     * A possible example scenario might be @Secure annotation throws an NotAuthorized exception and stops all
     * remaining annotation execution and does not allow access to service.
     *
     * Yet Annotation post processing should still be attempted, as it might re-format this exceptions representation,
     * such as in XML, JSON or anything.
     *
     * Also, if you set $req->handled to true, service won't be invoked as Annotation claims that it handled it.
     *
     * @param Request $req
	 * @param Response $resp
	 * @param Controller $controller
	 * @param string $method
	 */
	public function preProcess (Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method);
	
	/**
	 * Ran after the service->method has been invoked
     *
     * No exceptions should be thrown in post processing.
     *
	 * @param Request $req
	 * @param Response $resp
	 * @param Controller $controller
	 * @param string $method
	 */
	public function postProcess(Request $req, Response $resp,  Dispatcher $dispatcher, Service $service , $method);
}