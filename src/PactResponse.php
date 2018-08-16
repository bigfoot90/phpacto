<?php

declare(strict_types=1);

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2017  Damian Długosz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Bigfoot\PHPacto;

use Bigfoot\PHPacto\Matcher\BodyMatcher;
use Bigfoot\PHPacto\Matcher\HeadersMatcher;
use Bigfoot\PHPacto\Matcher\Mismatches\Mismatch;
use Bigfoot\PHPacto\Matcher\Mismatches\MismatchCollection;
use Bigfoot\PHPacto\Matcher\Rules\EachItemRule;
use Bigfoot\PHPacto\Matcher\Rules\OrRule;
use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

class PactResponse extends PactMessage implements PactResponseInterface
{
    /**
     * @var Rule
     */
    private $statusCode;

    /**
     * @param Rule      $statusCode
     * @param Rule[]    $headers
     * @param Rule|null $body
     */
    public function __construct(Rule $statusCode, array $headers = [], $body = null)
    {
        parent::__construct($headers, $body);

        $this->statusCode = $statusCode;
    }

    /**
     * @return Rule
     */
    public function getStatusCode(): Rule
    {
        return $this->statusCode;
    }

    public function getSample(): ResponseInterface
    {
        $statusCode = $this->statusCode->getSample();

        $headers = $this->getSampleHeaders();
        $body = $this->getSampleBody();

        $contentType = $this->getContentType();

        $stream = new Stream('php://memory', 'w');
        $stream->write(BodyEncoder::encode($body, $contentType));

        $response = new Response($stream, $statusCode, $headers);

        return $response;
    }

    public function assertMatch(ResponseInterface $request)
    {
        $mismatches = [];

        try {
            $this->statusCode->assertMatch($request->getStatusCode());
        } catch (Mismatch $mismatch) {
            $mismatches['STATUS CODE'] = $mismatch;
        }

        try {
            $this->assertMatchHeaders($request);
        } catch (Mismatch $mismatch) {
            $mismatches['HEADERS'] = $mismatch;
        }

        try {
            $this->assertMatchBody($request);
        } catch (Mismatch $mismatch) {
            $mismatches['BODY'] = $mismatch;
        }

        if ($mismatches) {
            throw new MismatchCollection($mismatches, 'Response does not match');
        }
    }
}
