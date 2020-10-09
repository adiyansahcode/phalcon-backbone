<?php

namespace Pbackbone\Middlewares;

use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

/**
 * CORSMiddleware
 *
 * CORS checking
 */
class CORSMiddleware implements MiddlewareInterface
{
    /**
     * Before anything happens
     *
     * @param Event $event
     * @param Micro $app
     *
     * @returns bool
     */
    public function beforeHandleRoute(Event $event, Micro $app)
    {
        if ($app->request->getHeader('ORIGIN')) {
            $origin = $app->request->getHeader('ORIGIN');
        } else {
            $origin = '*';
        }

        $app
            ->response
            ->setHeader('Access-Control-Allow-Origin', $origin)
            ->setHeader('Access-Control-Allow-Methods', 'DELETE,GET,HEAD,OPTIONS,PATCH,POST,PUT')
            ->setHeader(
                'Access-Control-Allow-Headers',
                'Authorization, Accept, Content-Range, Content-Disposition, Content-Type, Origin, X-Requested-With'
            )
            ->setHeader('Access-Control-Allow-Credentials', 'true')
            ->setHeader('Content-Type', 'application/json; charset=UTF-8');
    }

    /**
     * Calls the middleware
     *
     * @param Micro $app
     *
     * @returns bool
     */
    public function call(Micro $app)
    {
        return true;
    }
}
