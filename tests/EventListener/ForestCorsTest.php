<?php

use ForestAdmin\SymfonyForestAdmin\EventListener\ForestCors;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

test('getSubscribedEvents() should return onKernelRequest & onKernelResponse', function () {
    expect(ForestCors::getSubscribedEvents())->toBeArray()->toEqual([
        KernelEvents::REQUEST  => ['onKernelRequest', 300],
        KernelEvents::RESPONSE => ['onKernelResponse', 0],
    ]);
});

test('onKernelRequest() on private network of a preflight request should has the correct headers and a 204 response', function () {
    $cors = new ForestCors();
    $kernel = mock(HttpKernelInterface::class)->makePartial();
    $request = Request::create(
        '/forest/ping',
        'OPTIONS',
        [],
        [],
        [],
        [
            'HTTP_ACCESS_CONTROL_REQUEST_PRIVATE_NETWORK' => 'true',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD'          => 'true',
            'HTTP_ORIGIN'                                 => 'http://api.forestadmin.com',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD'          => 'POST',
        ]
    );
    $event = new RequestEvent($kernel, $request, 1);
    $cors->onKernelRequest($event);

    expect($request->headers->get('access-control-request-private-network'))
        ->toEqual('true')
        ->and($event->getResponse()->headers->get('access-control-allow-origin'))
        ->toEqual('http://api.forestadmin.com')
        ->and($event->getResponse()->headers->get('access-control-allow-credentials'))
        ->toEqual('true')
        ->and(Str::containsAll($event->getResponse()->headers->get('access-control-allow-methods'), ['GET', 'POST', 'PUT', 'DELETE']))
        ->toBeTrue()
        ->and($event->getResponse()->headers->get('access-control-max-age'))
        ->toEqual(86400)
        ->and($event->getResponse()->headers->get('access-control-allow-headers'))
        ->toBeNull()
        ->and($event->getResponse()->getStatusCode())
        ->toEqual(204);
});

test('onKernelRequest() request  of a preflight request should has the correct headers and a 204 response', function () {
    $cors = new ForestCors();
    $kernel = mock(HttpKernelInterface::class)->makePartial();
    $request = Request::create(
        '/forest/ping',
        'OPTIONS',
        [],
        [],
        [],
        [
            'HTTP_ORIGIN'                        => 'http://api.forestadmin.com',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]
    );
    $event = new RequestEvent($kernel, $request, 1);
    $cors->onKernelRequest($event);

    expect($event->getResponse()->headers->get('access-control-allow-origin'))
        ->toEqual('http://api.forestadmin.com')
        ->and($event->getResponse()->headers->get('access-control-allow-credentials'))
        ->toEqual('true')
        ->and(Str::containsAll($event->getResponse()->headers->get('access-control-allow-methods'), ['GET', 'POST', 'PUT', 'DELETE']))
        ->toBeTrue()
        ->and($event->getResponse()->headers->get('access-control-max-age'))
        ->toEqual(86400)
        ->and($event->getResponse()->headers->get('access-control-allow-headers'))
        ->toBeNull()
        ->and($event->getResponse()->getStatusCode())
        ->toEqual(204);
});

test('onKernelRequest() when eventType of event is different of HttpKernelInterface::MAIN_REQUEST should return null response', function () {
    $cors = new ForestCors();
    $kernel = mock(HttpKernelInterface::class)->makePartial();
    $request = new Request();
    $event = new RequestEvent($kernel, $request, 2);
    $cors->onKernelRequest($event);


    expect($event->getResponse())->toBeNull();
});

test('onKernelRequest() when request is not a preflight request should return null response', function () {
    $cors = new ForestCors();
    $kernel = mock(HttpKernelInterface::class)->makePartial();
    $request = Request::create(
        '/forest/ping',
        'OPTIONS',
        [],
        [],
        [],
        [
            'HTTP_ORIGIN' => 'http://api.forestadmin.com',
        ]
    );
    $event = new RequestEvent($kernel, $request, 1);
    $cors->onKernelRequest($event);

    expect($event->getResponse())->toBeNull();
});

test('onKernelRequest() when request is an OPTIONS and uri don\'t start by /forest should return null response', function () {
    $cors = new ForestCors();
    $kernel = mock(HttpKernelInterface::class)->makePartial();
    $request = Request::create(
        '/no-forest/ping',
        'OPTIONS',
        [],
        [],
        [],
        [
            'HTTP_ORIGIN'                        => 'http://api.forestadmin.com',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]
    );
    $event = new RequestEvent($kernel, $request, 1);
    $cors->onKernelRequest($event);

    expect($event->getResponse())->toBeNull();
});

test('onKernelResponse() when request is an OPTIONS and should vary and add correct headers on the response', function () {
    $cors = new ForestCors();
    $kernel = mock(HttpKernelInterface::class)->makePartial();
    $request = Request::create(
        '/forest/ping',
        'OPTIONS',
        [],
        [],
        [],
        [
            'HTTP_ORIGIN' => 'http://api.forestadmin.com',
        ]
    );
    $response = new Response();
    $event = new ResponseEvent($kernel, $request, 1, $response);
    $cors->onKernelResponse($event);

    expect($response->headers->get('vary'))->toEqual('Access-Control-Request-Method, Origin')
        ->and($response->headers->get('access-control-allow-credentials'))->toEqual('true')
        ->and($response->headers->get('access-control-allow-origin'))->toEqual('http://api.forestadmin.com')
        ->and($response->getStatusCode())->toEqual(200);
});

test('onKernelResponse() should add correct headers on the response', function () {
    $cors = new ForestCors();
    $kernel = mock(HttpKernelInterface::class)->makePartial();
    $request = Request::create(
        '/forest/ping',
        'POST',
        [],
        [],
        [],
        [
            'HTTP_ORIGIN' => 'http://api.forestadmin.com',
        ]
    );
    $response = new Response();
    $event = new ResponseEvent($kernel, $request, 1, $response);
    $cors->onKernelResponse($event);

    expect($response->headers->get('access-control-allow-credentials'))->toEqual('true')
        ->and($response->headers->get('access-control-allow-origin'))->toEqual('http://api.forestadmin.com')
        ->and($response->getStatusCode())->toEqual(200);
});

test('onKernelResponse() when eventType of event is different of HttpKernelInterface::MAIN_REQUEST should return null response', function () {
    $cors = new ForestCors();
    $kernel = mock(HttpKernelInterface::class)->makePartial();
    $request = new Request();
    $response = new Response();
    $event = new ResponseEvent($kernel, $request, 1, $response);
    $cors->onKernelResponse($event);

    expect($response->headers->get('access-control-allow-credentials'))->toBeNull()
        ->and($response->headers->get('access-control-allow-origin'))->toBeNull()
        ->and($response->getStatusCode())->toEqual(200);
});

test('onKernelResponse() when uri don\'t start by /forest should return null response', function () {
    $cors = new ForestCors();
    $kernel = mock(HttpKernelInterface::class)->makePartial();
    $request = Request::create(
        '/no-forest/ping',
        'POST',
        [],
        [],
        [],
        [
            'HTTP_ORIGIN' => 'http://api.forestadmin.com',
        ]
    );
    $response = new Response();
    $event = new ResponseEvent($kernel, $request, 1, $response);
    $cors->onKernelResponse($event);

    expect($response->headers->get('access-control-allow-credentials'))->toBeNull()
        ->and($response->headers->get('access-control-allow-origin'))->toBeNull()
        ->and($response->getStatusCode())->toEqual(200);
});
