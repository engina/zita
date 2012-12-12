<?php
namespace Zita;

require_once('Request.php');
require_once('Response.php');
require_once('Controller.php');

interface IAnnotation
{
	public function __construct($paramString);
	public function preProcess (Request $req, Response $resp, Controller $controller = null, $method = null);
	public function postProcess(Request $req, Response $resp, Controller $controller = null, $method = null);
}