<?php
namespace Zita\Filters;

use Zita\Request;
use Zita\Response;
use Zita\Dispatcher;
use Zita\Service;

class AutoFormatFilter extends \Zita\OutputFilter
{
    function postProcess(Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
    {
        $type = $req->params->type;
        if($type == null)
            $type = 'json';
        $type = strtolower($type);

        switch($type)
        {
            case 'json':
                $resp->body = json_encode($resp->body);
                if($req->params->callback != null)
                    $resp->body = $req->params->callback.'('.$resp->body.');';
                break;
            case 'raw':
                break;
            default:
                trigger_error('Unknown format.');
        }
    }

    function __construct($param)
    {
    }
}
