<?php
namespace Zita;
/**
 * User: Engin
 * Date: 17.12.2012
 * Time: 17:45
 */
class ZitaService extends  Service
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
/* Example implementation

function zita_api_success(response, options)
{

}

function zita_api_failure(response, options)
{

}

function $callback(service, method, params, success_cb, failure_cb)
{
    Ext.Ajax.request({url: 'api/0.1/api.php',
                      params: {'service': service, 'method': method},
                      jsonData: params,
                      success: success_cb === undefined ? zita_api_success : success_cb,
                      failure: failure_cb === undefined ? zita_api_failure : failure_cb});
}
*/
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
                $result .= '    '.$method.': function('.$args.'){'.$callback.'(\''.$service.'\', \''.$method."', $cb_data, success, failure)},\n";
            }
            $result .= "  },\n";
        }
        $result .= "}";
        $this->response->headers['Content-type'] = 'application/javascript';
        $this->response->body = $result;
    }
}
