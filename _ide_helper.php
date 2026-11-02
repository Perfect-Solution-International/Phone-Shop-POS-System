<?php

// @formatter:off
// phpcs:ignoreFile

/**
 * IDE Helper for VS Code and Intelephense
 * Provides static analysis symbols for Laravel Facades and Pagination
 */

namespace {
    class App extends \Illuminate\Support\Facades\App {}
    class Artisan extends \Illuminate\Support\Facades\Artisan {}
    class Auth extends \Illuminate\Support\Facades\Auth {}
    class Blade extends \Illuminate\Support\Facades\Blade {}
    class Broadcast extends \Illuminate\Support\Facades\Broadcast {}
    class Bus extends \Illuminate\Support\Facades\Bus {}
    class Cache extends \Illuminate\Support\Facades\Cache {}
    class Config extends \Illuminate\Support\Facades\Config {}
    class Cookie extends \Illuminate\Support\Facades\Cookie {}
    class Crypt extends \Illuminate\Support\Facades\Crypt {}
    class Date extends \Illuminate\Support\Facades\Date {}
    class DB extends \Illuminate\Support\Facades\DB {}
    class Event extends \Illuminate\Support\Facades\Event {}
    class File extends \Illuminate\Support\Facades\File {}
    class Gate extends \Illuminate\Support\Facades\Gate {}
    class Hash extends \Illuminate\Support\Facades\Hash {}
    class Http extends \Illuminate\Support\Facades\Http {}
    class Lang extends \Illuminate\Support\Facades\Lang {}
    class Log extends \Illuminate\Support\Facades\Log {}
    class Mail extends \Illuminate\Support\Facades\Mail {}
    class Notification extends \Illuminate\Support\Facades\Notification {}
    class Password extends \Illuminate\Support\Facades\Password {}
    class Queue extends \Illuminate\Support\Facades\Queue {}
    class RateLimiter extends \Illuminate\Support\Facades\RateLimiter {}
    class Redirect extends \Illuminate\Support\Facades\Redirect {}
    class Request extends \Illuminate\Support\Facades\Request {}
    class Response extends \Illuminate\Support\Facades\Response {}
    class Route extends \Illuminate\Support\Facades\Route {}
    class Schema extends \Illuminate\Support\Facades\Schema {}
    class Session extends \Illuminate\Support\Facades\Session {}
    class Storage extends \Illuminate\Support\Facades\Storage {}
    class URL extends \Illuminate\Support\Facades\URL {}
    class Validator extends \Illuminate\Support\Facades\Validator {}
    class View extends \Illuminate\Support\Facades\View {}
    class Str extends \Illuminate\Support\Str {}
    class Arr extends \Illuminate\Support\Arr {}
    class Carbon extends \Illuminate\Support\Carbon {}
}

namespace Illuminate\Contracts\Pagination {
    /**
     * @method $this withQueryString()
     * @method $this appends(array|string $key, string|null $value = null)
     * @method $this fragment(string|null $fragment = null)
     * @method bool hasMorePages()
     * @method int total()
     * @method int lastPage()
     * @method int currentPage()
     * @method int perPage()
     * @method int firstItem()
     * @method int lastItem()
     */
    interface LengthAwarePaginator
    {
    }

    /**
     * @method $this withQueryString()
     * @method $this appends(array|string $key, string|null $value = null)
     * @method $this fragment(string|null $fragment = null)
     * @method bool hasMorePages()
     */
    interface Paginator
    {
    }
}

namespace Illuminate\Database\Eloquent {
    /**
     * @method \Illuminate\Pagination\LengthAwarePaginator paginate(int|null $perPage = null, array|string $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method \Illuminate\Pagination\CursorPaginator cursorPaginate(int|null $perPage = null, array|string $columns = ['*'], string $cursorName = 'cursor', \Illuminate\Pagination\Cursor|string|null $cursor = null)
     * @method \Illuminate\Pagination\Paginator simplePaginate(int|null $perPage = null, array|string $columns = ['*'], string $pageName = 'page', int|null $page = null)
     */
    class Builder
    {
    }
}

namespace Illuminate\Database\Query {
    /**
     * @method \Illuminate\Pagination\LengthAwarePaginator paginate(int|null $perPage = null, array|string $columns = ['*'], string $pageName = 'page', int|null $page = null)
     * @method \Illuminate\Pagination\CursorPaginator cursorPaginate(int|null $perPage = null, array|string $columns = ['*'], string $cursorName = 'cursor', \Illuminate\Pagination\Cursor|string|null $cursor = null)
     * @method \Illuminate\Pagination\Paginator simplePaginate(int|null $perPage = null, array|string $columns = ['*'], string $pageName = 'page', int|null $page = null)
     */
    class Builder
    {
    }
}
