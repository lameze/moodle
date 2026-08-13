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

use Slim\Interfaces\RouteInterface;

/**
 * A base Route Loader
 *
 * @package    core
 * @copyright  2024 Andrew Lyons <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class abstract_route_loader {
    /**
     * Get all routes in the namespace.
     *
     * @param string $namespace The namespace to get the routes for
     * @param callable $componentpathcallback A callback to get the component path for a class
     * @param null|callable $filtercallback A callback to use to filter routes before they are added
     * @return array[]
     */
    protected function get_all_routes_in_namespace(
        string $namespace,
        callable $componentpathcallback,
        ?callable $filtercallback = null,
    ): array {
        $routes = [];

        // Get all classes in the namespace.
        $classes = \core_component::get_component_classes_in_namespace(namespace: $namespace);
        foreach (array_keys($classes) as $classname) {
            $classinfo = new \ReflectionClass($classname);
            if ($filtercallback && !$filtercallback($classname)) {
                continue;
            }
            $component = \core_component::get_component_from_classname($classname);
            $componentpath = $componentpathcallback($component);

            // Add all public methods with a #[route] attribute in this class.
            array_push($routes, ...$this->get_all_routes_in_class(
                componentpath: $componentpath,
                classinfo: $classinfo,
            ));
        }

        return $routes;
    }

    /**
     * Get all routes in a class.
     *
     * @param string $componentpath The path to the component that the class belongs to
     * @param \ReflectionClass $classinfo The class to get the routes for
     * @return array[]
     */
    protected function get_all_routes_in_class(
        string $componentpath,
        \ReflectionClass $classinfo,
    ): array {
        // Filter out any methods which are public but do not have any route attached.
        return array_filter(
            array_map(
                fn ($methodinfo) => $this->get_route_data_for_method(
                    componentpath: $componentpath,
                    classinfo: $classinfo,
                    methodinfo: $methodinfo,
                ),
                $classinfo->getMethods(\ReflectionMethod::IS_PUBLIC),
            )
        );
    }

    /**
     * Get route data for a single method in a class.
     *
     * @param string $componentpath The path to the component that the class belongs to
     * @param \ReflectionClass $classinfo The class to get the route data for
     * @param \ReflectionMethod $methodinfo The method to get the route data for
     * @return null|array[]
     */
    protected function get_route_data_for_method(
        string $componentpath,
        \ReflectionClass $classinfo,
        \ReflectionMethod $methodinfo,
    ): ?array {
        $routeattribute = $this->get_route_attribute_for_method(
            $classinfo,
            $methodinfo,
        );

        if ($routeattribute === null) {
            // No route on this method.
            return null;
        }

        // Build the pattern for this route.
        $path = $routeattribute->get_path();
        $pattern = "/{$componentpath}{$path}";

        // Remove duplicate slashes.
        $pattern = preg_replace('@/+@', '/', $pattern);

        // Get the HTTP methods for this route.
        $httpmethods = $routeattribute->get_methods(['GET']);

        return [
            'methods' => $httpmethods,
            'pattern' => $pattern,
            'callable' => [$classinfo->getName(), $methodinfo->getName()],
        ];
    }

    /**
     * Get the route attribute for the specified method.
     *
     * Note: If a parent has a route, but the method does not, no route will be returned.
     *
     * @param \ReflectionClass $classinfo The class to get the route attribute for
     * @param \ReflectionMethod $methodinfo The method to get the route attribute for
     * @return null|route
     */
    protected function get_route_attribute_for_method(
        \ReflectionClass $classinfo,
        \ReflectionMethod $methodinfo,
    ): ?route {
        // Fetch the route attribute from the method.
        // Each method can only have a single route attribute.
        $routeattributes = $methodinfo->getAttributes(route::class, \ReflectionAttribute::IS_INSTANCEOF);
        if (empty($routeattributes)) {
            return null;
        }

        // Get the instance.
        $methodroute = $routeattributes[0]->newInstance();

        // Set the parent route if the class has one.
        $classattributes = $classinfo->getAttributes(route::class);
        if ($classattributes) {
            // The class has a #route attribute.
            $methodroute->set_parent($classattributes[0]->newInstance());
        }

        return $methodroute;
    }

    /**
     * Get the route pattern for the specified callable, including any route group prefix.
     *
     * Loaders which know their routes without building the application should override this. It
     * allows a path to be resolved without standing up the Slim application, its middleware, and
     * the full route collection.
     *
     * @param array|string $callable The callable to get the pattern for
     * @return null|string The pattern, or null if it could not be determined
     */
    public function get_pattern_for_callable(
        array|string $callable,
    ): ?string {
        return null;
    }

    /**
     * Normalise a callable into a [class, method] pair for comparison.
     *
     * @param mixed $callable
     * @return null|array The normalised callable, or null if it is not a class/method pair
     */
    protected static function normalise_callable(mixed $callable): ?array {
        if (is_string($callable) && str_contains($callable, '::')) {
            $callable = explode('::', $callable, 2);
        }

        if (!is_array($callable) || count($callable) !== 2) {
            return null;
        }

        if (!is_string($callable[0]) || !is_string($callable[1])) {
            return null;
        }

        return [ltrim($callable[0], '\\'), $callable[1]];
    }

    /**
     * Find the pattern matching a callable in a set of route groups.
     *
     * @param array $callable The normalised callable to look for
     * @param array $groups An array of [group prefix, route data] pairs
     * @return null|string
     */
    protected static function find_pattern_in_groups(
        array $callable,
        array $groups,
    ): ?string {
        foreach ($groups as [$prefix, $routes]) {
            foreach ($routes as $route) {
                if (self::normalise_callable($route['callable']) === $callable) {
                    // Remove any duplicate slashes introduced by the group prefix.
                    return preg_replace('@/+@', '/', $prefix . $route['pattern']);
                }
            }
        }

        return null;
    }

    /**
     * Normalise the component for use as part of the path.
     *
     * If the component is a subsystem, the `core_` prefix will be removed.
     * If the component is 'core', it will be kept.
     * All other components will use their frankenstyle name.
     *
     * @param string $component
     * @return string
     */
    protected function normalise_component_path(
        string $component,
    ): string {
        return util::normalise_component_path($component);
    }

    /**
     * Set a route name for the specified callable.
     *
     * @param RouteInterface $slimroute
     * @param string|array|callable $callable
     * @return string|null The name of the route if it was set, otherwise null
     */
    protected function set_route_name_for_callable(
        RouteInterface $slimroute,
        string|array|callable $callable,
    ): ?string {
        if (is_string($callable)) {
            $slimroute->setName($callable);
            return $callable;
        }

        if (is_array($callable)) {
            $name = implode('::', $callable);
            $slimroute->setName($name);
            return $name;
        }

        // Unable to set a name. Return null.
        return null;
    }
}
