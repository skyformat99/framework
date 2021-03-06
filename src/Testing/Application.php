<?php
/**
 * User: 黄朝晖
 * Date: 2017-11-13
 * Time: 3:09
 */

namespace Swoft\Testing;


use Swoft\App;
use Swoft\Base\RequestContext;
use Swoft\Event\Event;
use Swoole\Http\Request;
use Swoole\Http\Response;

class Application extends \Swoft\Web\Application
{

    /**
     * handle request
     *
     * @param \Swoole\Http\Request  $request  Swoole request object
     * @param \Swoole\Http\Response $response Swoole response object
     * @return bool|\Swoft\Testing\Web\Response
     */
    public function doRequest(Request $request, Response $response)
    {
        // Fix Chrome ico request bug
        // TODO: Add Middleware mechanisms and move "fix the Chrome ico request bug" to middleware
        if (isset($request->server['request_uri']) && $request->server['request_uri'] === '/favicon.ico') {
            $response->end('favicon.ico');
            return false;
        }

        // Initialize Request and Response and set to RequestContent
        RequestContext::setRequest($request);
        RequestContext::setResponse($response);

        // Trigger 'Before Request' event
        App::trigger(Event::BEFORE_REQUEST);

        $swfRequest = RequestContext::getRequest();
        // Get URI and Method from request
        $uri = $swfRequest->getUri()->getPath();
        $method = $swfRequest->getMethod();

        // Run action of Controller by URI and Method
        $actionResponse = $this->runController($uri, $method);

        // Invalid Response was provided
        if (! $actionResponse instanceof \Swoft\Base\Response) {
            return false;
        }

        // Handle Response
        $actionResponse->send();

        // Trigger 'After Request' event
        App::trigger(Event::AFTER_REQUEST);
        return $actionResponse;
    }
}