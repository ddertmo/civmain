<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Make every test request authenticate from scratch, like a real HTTP
     * request does. Without this the auth guards cache the first user they
     * resolve for the whole test, so switching tokens mid-test (citizen vs
     * staff, CEO vs super admin) or deactivating a user between requests
     * silently keeps using the stale cached user.
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->app['auth']->forgetGuards();

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }
}
