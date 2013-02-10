<?php
namespace Zita;
/**
 * User: Engin
 * Date: 17.12.2012
 * Time: 17:45
 */
class ZitaService extends Service
{
    /**
     * Discovers the available services to build API definition.
     */
    public function discover()
    {
        $this->response->body = Reflector::discover();
    }

    /**
     * We do not want any encoding for the output.
     * @OutputFilter
     */
    public function apigen($apiName = 'ZitaAPI', $callback = 'zita_api')
    {
        $services = Reflector::discover();
        $result = <<<SCRIPT
// You MUST implement $callback yourself -- Example jQuery implementation
//
// function $callback(service, method, params, success_cb, failure_cb)
// {
//    var zitaParams = {service: service, method: method};
//    if(ZitaAPI.auth != undefined) {
//      zitaParams.auth = ZitaAPI.auth;
//    }
//    $.post('api.php', $.extend(params, zitaParams), success_cb);
// }

var $apiName = {

SCRIPT;

        foreach($services as $service => $methods)
        {
            $service = substr($service, 0, -7);
            $result .= "  $service: {\n";
            foreach($methods as $method => $methodInfo)
            {
                $cb_data = '{';
                $args = '';
                foreach($methodInfo['parameters'] as $param => $detail)
                {
                    $args .= $param.', ';
                    $cb_data .= "'$param':$param,";
                }
                $args .= 'success, failure';
                $cb_data .= '}';
                $result .= '    '.$method.': function('.$args."){\n        ".$callback.'(\''.$service.'\', \''.$method."', $cb_data, success, failure)\n    },\n";
            }
            $result .= "  },\n";
        }
        $result .= "}";
        $this->response->headers['Content-type'] = 'application/javascript';
        $this->response->body = $result;
    }
}
