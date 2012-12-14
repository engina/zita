<?php
namespace Zita;

/**
 * Annotation interface.
 * 
 * Each annotation must implement this inteface. Annotations can accept parameters
 * and can be run before, after (or both) controller->method invokation.
 * 
 * Method annotations overrides class wide annotations.
 *
 */
interface IAnnotation
{
	/**
	 * Annotations can accept parameters
	 * \@Foo param
	 * These will be fed to Annotation implementation
	 * @param unknown_type $paramString
	 */
	public function __construct($paramString);
	
	/**
	 * Run just before controller->method is going to be invoked
	 * @param Request $req
	 * @param Response $resp
	 * @param Controller $controller
	 * @param unknown_type $method
	 */
	public function preProcess (Request $req, Response $resp, Service $service = null, $method = null);
	
	/**
	 * Ran after the controller->method has been invoked
	 * @param Request $req
	 * @param Response $resp
	 * @param Controller $controller
	 * @param unknown_type $method
	 */
	public function postProcess(Request $req, Response $resp, Service $service = null, $method = null);
}