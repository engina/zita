<?php
namespace Zita\Filters;

use Zita\Request;
use Zita\Response;
use Zita\Dispatcher;
use Zita\Service;
use Zita\IFilter;

/*
 * This assocToXML implementation is borrowed from  James Earlywine - July 20th 2011
 *
 * And modified. I've added $root support and turned PHP_EOLs to "\n" because an interface must be consistent no matter
 * which platform it is deployed on.
 */
function assocToXML ($theArray, $tabCount=2, $root = true)
{
    $tabCount++;
    $tabSpace = "";
    $extraTabSpace = "";
    for ($i = 0; $i<$tabCount; $i++) {
        $tabSpace .= " ";
    }

    if($root)
        $theXML = '<root>';
    // parse the array for data and output xml
    foreach($theArray as $tag => $val) {
        if (!is_array($val)) {
            $theXML .= "\n".$tabSpace.'<'.$tag.'>'.htmlentities($val).'</'.$tag.'>';
        } else {
            $tabCount++;
            $theXML .= "\n".$tabSpace.'<'.$tag.'>'.assocToXML($val, $tabCount, false);
            $theXML .= "\n".$tabSpace.'</'.$tag.'>';
        }
    }
    if($root)
        $theXML .= "\n</root>";

    return $theXML;
}

class AutoFormatFilter implements IFilter
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
                $resp->headers['Content-Type'] = 'application/json';
                $resp->body = json_encode($resp->body);
                if($req->params->callback != null)
                    $resp->body = $req->params->callback.'('.$resp->body.');';
                break;
            case 'xml';
                $resp->headers['Content-Type'] = 'application/xml';
                $resp->body = assocToXML($resp->body);
                break;
            case 'raw':
                break;
            default:
                throw new \Zita\DispatcherException("@OutputFilter AutoFormat: Unknown format $type, please fix the 'type' parameter in the Request.");
        }
    }

    public function preProcess(Request $req, Response $resp, Dispatcher $dispatcher, Service $service, $method)
    {
        if($req->headers['Content-Type'] == 'application/json')
        {
            $req->params->fromArray(array_merge($req->params->toArray(), json_decode($req->body, true)));
        }
    }
}
