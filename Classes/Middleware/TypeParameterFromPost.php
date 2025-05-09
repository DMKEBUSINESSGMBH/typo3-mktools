<?php

declare(strict_types=1);

/*
 * Copyright notice
 *
 * (c) DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mktools" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

namespace DMK\Mktools\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class TypeParameterFromPost.
 *
 * @author  Hannes Bochmann
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class TypeParameterFromPost implements MiddlewareInterface
{
    /**
     * The Ajax Content Renderer sets the page type parameter in many cases through POST request parameters. But TYPO3
     * doesn't use the page type parameter from the POST data anymore. Therefore we put the type from POST to GET
     * ourselves.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $typeFromPostParameters = $request->getParsedBody()['type'] ?? null;
        $typeFromQueryParameters = $request->getQueryParams()['type'] ?? null;
        if ($typeFromPostParameters && !$typeFromQueryParameters) {
            $request = $request->withQueryParams(
                $request->getQueryParams() +
                ['type' => $request->getParsedBody()['type']]
            );
        }

        return $handler->handle($request);
    }
}
