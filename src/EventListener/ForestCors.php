<?php

namespace Nicolas\SymfonyForestAdmin\EventListener;

use Asm89\Stack\CorsService;
use Illuminate\Support\Str;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ForestCors implements EventSubscriberInterface
{
    /**
     * @var CorsService $cors
     */
    protected CorsService $cors;

    public function __construct()
    {
        $this->cors = new CorsService($this->getCorsOptions());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST  => ['onKernelRequest', 300],
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType() || !$this->shouldRun($request)) {
            return;
        }

        if ($this->cors->isPreflightRequest($request)) {
            $response = $this->cors->handlePreflightRequest($request);
            $this->cors->varyHeader($response, 'Access-Control-Request-Method');

            if ($request->headers->has('Access-Control-Request-Private-Network')) {
                $response->headers->set('Access-Control-Allow-Private-Network', 'true');
            }

            $event->setResponse($response);
        }
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType() || !$this->shouldRun($request)) {
            return;
        }

        if ($request->getMethod() === 'OPTIONS') {
            $this->cors->varyHeader($response, 'Access-Control-Request-Method');
        }

        return $this->addHeaders($request, $response);
    }

    /**
     * Add the headers to the Response, if they don't exist yet.
     *
     * @param Request   $request
     * @param Response  $response
     * @return Response
     */
    protected function addHeaders(Request $request, Response $response): Response
    {
        if (!$response->headers->has('Access-Control-Allow-Origin')) {
            $response = $this->cors->addActualRequestHeaders($response, $request);
        }

        return $response;
    }

    /**
     * Determine if the request match with the config
     *
     * @param Request $request
     * @return bool
     */
    protected function shouldRun(Request $request): bool
    {
        return str_starts_with($request->getRequestUri(), '/forest');
    }

    /**
     * Get CORS
     *
     * @return array
     */
    protected function getCorsOptions(): array
    {
        return [
            'allowedHeaders'         => ['*'],
            'allowedMethods'         => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowedOriginsPatterns' => ['#^.*\.forestadmin\.com\z#u'],
            'exposedHeaders'         => false,
            'maxAge'                 => 86400,
            'supportsCredentials'    => true,
        ];
    }
}
