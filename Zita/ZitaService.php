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
        $result = "var $apiName = {\n";
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
                $args = substr($args, 0, -2);
                $cb_data .= '}';
                $result .= '    '.$method.': function('.$args.'){'.$callback.'(\''.$service.'\', \''.$method."', $cb_data)},\n";
            }
            $result .= "  },\n";
        }
        $result .= "}";
        $this->response->headers['Content-type'] = 'application/javascript';
        $this->response->body = $result;
    }
}
