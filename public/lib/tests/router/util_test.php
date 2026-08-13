<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace core\router;

use core\router\middleware\moodle_route_attribute_middleware;
use core\tests\router\route_testcase;
use core\url;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use Slim\App;

/**
 * Tests for the route utility class.
 *
 * @package    core
 * @copyright  Andrew Lyons <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(util::class)]
final class util_test extends route_testcase {
    /**
     * Ensure that redirecting works as expected.
     *
     * @dataProvider redirect_provider
     * @param string|url $url The URL to redirect to, as a string or a \core\url instance.
     * @param string|null $expectedurl The expected URL in the Location header of the response.
     */
    public function test_redirect(
        string|url $url,
        ?string $expectedurl = null,
    ): void {
        $expectedurl = $expectedurl ?? $url;
        $response = new Response(200, ['Content-Type' => 'application/json']);
        $redirecturl = util::redirect($response, $url);
        $this->assertInstanceOf(Response::class, $redirecturl);
        $this->assertStringEndsWith($expectedurl, $redirecturl->getHeaderLine('Location'));
    }

    /**
     * Data provider for test_redirect.
     *
     * @return \Generator
     */
    public static function redirect_provider(): \Generator {
        yield 'String URL without parameters' => [
            'url' => '/path',
        ];
        yield 'String URL with one parameter' => [
            'url' => '/path?param1=value1',
        ];
        yield 'String URL with more than one parameter' => [
            'url' => '/path?param1=value1&param2=value2',
        ];
        yield 'Object URL without parameters' => [
            'url' => new url('/path'),
            'expectedurl' => '/path',
        ];
        yield 'Object URL with one parameter' => [
            'url' => new url('/path', ['param1' => 'value1']),
            'expectedurl' => '/path?param1=value1',
        ];
        yield 'Object URL with more than one parameters' => [
            'url' => new url('/path', ['param1' => 'value1', 'param2' => 'value2']),
            'expectedurl' => '/path?param1=value1&param2=value2',
        ];
    }

    /**
     * Ensure that no error is thrown when getting a route instance for a callable.
     */
    public function test_get_route_instance_for_method_not_array_callable(): void {
        $this->assertNull(util::get_route_instance_for_method(fn () => null));
    }

    /**
     * Ensure that the act of getting the route name does not instantiate the class.
     */
    public function test_get_route_name_for_method_does_not_instantiate(): void {
        self::load_fixture('core', 'router/uninstantiable_class.php');

        $classname = \core\fixtures\uninstantiable_class::class;

        \core\router\util::get_route_name_for_callable([$classname, 'method_with_route']);
        \core\router\util::get_route_name_for_callable("{$classname}::method_with_route");

        $this->assertTrue(true, 'No error was thrown when getting the route name for a non-instantiable class.');

        // And ensure that no-one has broken the fixture by making it instantiable.
        $this->expectException(\Error::class);
        new \core\fixtures\uninstantiable_class();
    }

    /**
     * Test getting the path for a callable.
     */
    public function test_get_path_for_callable(): void {
        global $CFG;

        $this->resetAfterTest();

        $CFG->routerconfigured = true;
        self::load_fixture('core', 'router/route_on_class.php');

        $this->add_route_to_route_loader(
            \core\fixtures\route_on_class::class,
            'method_with_route',
            grouppath: '/example',
        );

        $url = util::get_path_for_callable(
            [\core\fixtures\route_on_class::class, 'method_with_route'],
            [],
            [],
        );

        $parsedurl = parse_url($url);
        $this->assertEquals(
            (new \moodle_url('/example/class/path/method/path'))->get_path(),
            $parsedurl['path'],
        );
    }

    /**
     * Register the parameterised route fixture with the test route loader.
     */
    protected function add_parameter_fixture_routes(): void {
        global $CFG;

        $this->resetAfterTest();

        $CFG->routerconfigured = true;
        self::load_fixture('core', 'router/route_with_parameters.php');

        $this->add_class_routes_to_route_loader(
            \core\fixtures\route_with_parameters::class,
            grouppath: '/example',
        );
    }

    /**
     * Get the URL which the Slim route parser generates for a callable.
     *
     * This is what get_path_for_callable() returned before it learned to resolve paths from the
     * route metadata, and is the behaviour that must not change.
     *
     * @param array $callable The callable to get the URL for
     * @param array $params Any parameters to include in the path
     * @param array $queryparams Any parameters to include in the query string
     * @return url
     */
    protected function get_slim_path_for_callable(
        array $callable,
        array $params = [],
        array $queryparams = [],
    ): url {
        global $CFG;

        $parser = $this->get_app()->getRouteCollector()->getRouteParser();

        return new url(
            url: $parser->fullUrlFor(
                new Uri($CFG->wwwroot),
                util::get_route_name_for_callable($callable),
                $params,
                $queryparams,
            ),
        );
    }

    /**
     * Check whether the Slim application has been built by the router.
     *
     * @param \core\router $router
     * @return bool
     */
    protected function app_was_built(\core\router $router): bool {
        return (new \ReflectionProperty(\core\router::class, 'app'))->isInitialized($router);
    }

    /**
     * The path generated for a callable must be identical whether or not the application is built.
     *
     * @param string $methodname The fixture method to generate a path for
     * @param array $params Any parameters to include in the path
     * @param array $queryparams Any parameters to include in the query string
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('path_for_callable_provider')]
    public function test_get_path_for_callable_matches_slim(
        string $methodname,
        array $params = [],
        array $queryparams = [],
    ): void {
        $this->add_parameter_fixture_routes();

        $callable = [\core\fixtures\route_with_parameters::class, $methodname];

        $actual = util::get_path_for_callable($callable, $params, $queryparams);
        $expected = $this->get_slim_path_for_callable($callable, $params, $queryparams);

        $this->assertEquals((string) $expected, (string) $actual);
    }

    /**
     * Data provider for test_get_path_for_callable_matches_slim.
     *
     * @return \Generator
     */
    public static function path_for_callable_provider(): \Generator {
        yield 'No parameters' => [
            'methodname' => 'plain',
        ];
        yield 'Query parameters only' => [
            'methodname' => 'plain',
            'params' => [],
            'queryparams' => ['foo' => 'bar', 'baz' => '1'],
        ];
        yield 'Query parameters requiring encoding' => [
            'methodname' => 'plain',
            'params' => [],
            'queryparams' => ['with space' => 'and&ampersand'],
        ];
        yield 'A single parameter' => [
            'methodname' => 'simple_parameter',
            'params' => ['id' => '42'],
        ];
        yield 'A single parameter given as an integer' => [
            'methodname' => 'simple_parameter',
            'params' => ['id' => 42],
        ];
        yield 'An unused parameter is ignored' => [
            'methodname' => 'simple_parameter',
            'params' => ['id' => '42', 'unused' => 'value'],
        ];
        yield 'A constrained parameter' => [
            'methodname' => 'constrained_parameter',
            'params' => ['id' => '42'],
        ];
        yield 'A path parameter which is empty' => [
            'methodname' => 'path_parameter',
            'params' => ['revision' => '12345', 'scriptpath' => ''],
        ];
        yield 'A path parameter containing slashes' => [
            'methodname' => 'path_parameter',
            'params' => ['revision' => '-1', 'scriptpath' => 'mod/forum/amd/src/example.js'],
        ];
        yield 'A path parameter with query parameters' => [
            'methodname' => 'path_parameter',
            'params' => ['revision' => '1', 'scriptpath' => 'core/first.js'],
            'queryparams' => ['cache' => '0'],
        ];
        yield 'An optional segment which was not supplied' => [
            'methodname' => 'optional_segment',
        ];
        yield 'An optional segment which was supplied' => [
            'methodname' => 'optional_segment',
            'params' => ['page' => '2'],
        ];
    }

    /**
     * The whole point of resolving paths from the route metadata is that the application, its
     * middleware, and the full route collection do not have to be built.
     *
     * @param string $methodname The fixture method to generate a path for
     * @param array $params Any parameters to include in the path
     * @param string $expectedpath The path expected, relative to the wwwroot
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('path_without_app_provider')]
    public function test_get_path_for_callable_does_not_build_the_app(
        string $methodname,
        array $params,
        string $expectedpath,
    ): void {
        $this->add_parameter_fixture_routes();

        $router = $this->get_router();
        $this->assertFalse(
            $this->app_was_built($router),
            'The application was already built before the path was generated.',
        );

        $url = util::get_path_for_callable(
            [\core\fixtures\route_with_parameters::class, $methodname],
            $params,
        );

        $this->assertFalse(
            $this->app_was_built($router),
            'The Slim application was built in order to generate a path.',
        );

        $this->assertEquals(
            (new \moodle_url($expectedpath))->get_path(),
            parse_url((string) $url, PHP_URL_PATH),
        );
    }

    /**
     * Data provider for test_get_path_for_callable_does_not_build_the_app.
     *
     * @return \Generator
     */
    public static function path_without_app_provider(): \Generator {
        yield 'No parameters' => [
            'methodname' => 'plain',
            'params' => [],
            'expectedpath' => '/example/plain/path',
        ];
        yield 'A single parameter' => [
            'methodname' => 'simple_parameter',
            'params' => ['id' => '42'],
            'expectedpath' => '/example/item/42',
        ];
        yield 'A constraint containing square brackets is not an optional segment' => [
            'methodname' => 'constrained_parameter',
            'params' => ['id' => '42'],
            'expectedpath' => '/example/constrained/42',
        ];
        yield 'A path parameter keeps its slashes' => [
            'methodname' => 'path_parameter',
            'params' => ['revision' => '1', 'scriptpath' => 'core/example.js'],
            'expectedpath' => '/example/serve/1/core/example.js',
        ];
    }

    /**
     * Optional segments have to be expanded by the Slim route parser, so the application is built.
     */
    public function test_get_path_for_callable_falls_back_for_optional_segments(): void {
        $this->add_parameter_fixture_routes();

        $router = $this->get_router();

        util::get_path_for_callable(
            [\core\fixtures\route_with_parameters::class, 'optional_segment'],
        );

        $this->assertTrue(
            $this->app_was_built($router),
            'A pattern with an optional segment must be resolved by the Slim route parser.',
        );
    }

    /**
     * A missing parameter must produce the same error as it did before, which means falling back to
     * the Slim route parser rather than generating a path containing an unfilled placeholder.
     */
    public function test_get_path_for_callable_falls_back_for_a_missing_parameter(): void {
        $this->add_parameter_fixture_routes();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing data for URL segment: id');

        util::get_path_for_callable(
            [\core\fixtures\route_with_parameters::class, 'simple_parameter'],
        );
    }

    /**
     * A route loader which does not extend the abstract loader cannot be asked for its patterns, so
     * paths must still be resolved through the application.
     */
    public function test_get_path_for_callable_falls_back_for_an_unknown_loader(): void {
        $this->add_parameter_fixture_routes();

        // Wrap the test loader in one which does not extend the abstract loader.
        $wrapped = \core\di::get(route_loader_interface::class);
        // phpcs:ignore
        \core\di::set(route_loader_interface::class, new class ($wrapped) implements route_loader_interface {
            // phpcs:ignore
            public function __construct(
                /** @var route_loader_interface The loader to delegate to */
                protected route_loader_interface $loader,
            ) {
            }

            // phpcs:ignore
            public function configure_routes(App $app): array {
                return $this->loader->configure_routes($app);
            }
        });

        $router = $this->get_router();
        $callable = [\core\fixtures\route_with_parameters::class, 'plain'];

        $url = util::get_path_for_callable($callable);

        $this->assertTrue(
            $this->app_was_built($router),
            'A loader which cannot report its patterns must be resolved by the Slim route parser.',
        );
        $this->assertEquals(
            (string) $this->get_slim_path_for_callable($callable),
            (string) $url,
        );
    }

    public function test_get_route_instance_for_method(): void {
        self::load_fixture('core', 'router/route_on_method_only.php');
        self::load_fixture('core', 'router/route_on_class.php');

        // The class has no route attribute.

        // Test a method that has no route attribute.
        $this->assertNull(util::get_route_instance_for_method('core\fixtures\route_on_method_only::method_without_route'));
        $this->assertNull(util::get_route_instance_for_method(['core\fixtures\route_on_method_only', 'method_without_route']));

        // Test a method that has a route attribute.
        $this->assert_route_callable_data(
            'core\fixtures\route_on_method_only::method_with_route',
            '/method/path',
            'core\fixtures\route_on_method_only::method_with_route',
        );
        $this->assert_route_callable_data(
            ['core\fixtures\route_on_method_only', 'method_with_route'],
            '/method/path',
            'core\fixtures\route_on_method_only::method_with_route',
        );

        // The class has a route attribute.

        // Test a method that has no route attribute.
        $this->assertNull(util::get_route_instance_for_method('core\fixtures\route_on_class::method_without_route'));
        $this->assertNull(util::get_route_instance_for_method(['core\fixtures\route_on_class', 'method_without_route']));

        // Test a method that has a route attribute - it is merged with parent.
        $this->assert_route_callable_data(
            'core\fixtures\route_on_class::method_with_route',
            '/class/path/method/path',
            'core\fixtures\route_on_class::method_with_route',
        );
        $this->assert_route_callable_data(
            ['core\fixtures\route_on_class', 'method_with_route'],
            '/class/path/method/path',
            'core\fixtures\route_on_class::method_with_route',
        );
    }

    /**
     * Assertion helper to asser that a callable is a route and has the expected path and name.
     *
     * @param callable $callable The callable to check.
     * @param string $path The expected path.
     * @param string $routename The expected route name.
     */
    protected function assert_route_callable_data(
        $callable,
        string $path,
        string $routename,
    ): void {
        $route = util::get_route_instance_for_method($callable);
        $this->assertInstanceOf(route::class, $route);
        $this->assertEquals($path, $route->get_path());
        $this->assertIsString(util::get_route_name_for_callable($callable));
        $this->assertEquals($routename, util::get_route_name_for_callable($callable));
    }

    /**
     * Test getting the route name for an anonymous callable.
     */
    public function test_get_route_for_callable_not_array_callable(): void {
        $this->expectException(\coding_exception::class);
        $this->assertNull(util::get_route_name_for_callable(fn () => null));
    }

    public function test_get_route_instance_for_request(): void {
        self::load_fixture('core', 'router/route_on_method_only.php');

        $app = $this->get_simple_app();
        $app->add(moodle_route_attribute_middleware::class);
        $app->addRoutingMiddleware();
        $app->get('/method/path', [\core\fixtures\route_on_method_only::class, 'method_with_route']);
        $app->handle(new ServerRequest('GET', '/method/path'));

        $request = $this->route_request($app, new ServerRequest('GET', '/method/path'));

        $route = util::get_route_instance_for_request($request);

        $this->assertInstanceOf(route::class, $route);
        $this->assertEquals('/method/path', $route->get_path());

        $secondroute = util::get_route_instance_for_request($request);
        $this->assertInstanceOf(route::class, $secondroute);
        $this->assertEquals('/method/path', $secondroute->get_path());
    }
}
