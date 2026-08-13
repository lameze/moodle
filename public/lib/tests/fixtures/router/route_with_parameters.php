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

namespace core\fixtures;

use core\router\route;
use GuzzleHttp\Psr7\Response;

/**
 * Fixture containing routes whose paths exercise each style of route pattern.
 *
 * @package    core
 * @copyright  2026 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class route_with_parameters {
    /**
     * A route with no parameters at all.
     *
     * @return Response
     */
    #[route(
        path: '/plain/path',
    )]
    public function plain(): Response {
        return new Response(200, [], 'plain');
    }

    /**
     * A route with a single unconstrained parameter.
     *
     * @return Response
     */
    #[route(
        path: '/item/{id}',
    )]
    public function simple_parameter(): Response {
        return new Response(200, [], 'simple');
    }

    /**
     * A route whose parameter constraint itself contains square brackets.
     *
     * @return Response
     */
    #[route(
        path: '/constrained/{id:[0-9]+}',
    )]
    public function constrained_parameter(): Response {
        return new Response(200, [], 'constrained');
    }

    /**
     * A route which takes a path, including slashes, as a parameter.
     *
     * This mirrors the ES module loader, which is the highest-traffic caller.
     *
     * @return Response
     */
    #[route(
        path: '/serve/{revision:[0-9-]+}/{scriptpath:.*}',
    )]
    public function path_parameter(): Response {
        return new Response(200, [], 'path');
    }

    /**
     * A route with an optional segment.
     *
     * @return Response
     */
    #[route(
        path: '/list[/{page}]',
    )]
    public function optional_segment(): Response {
        return new Response(200, [], 'optional');
    }
}
