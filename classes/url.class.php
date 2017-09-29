<?php

// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Utility class to handle URLs.
 * 
 * @author Pedro Ferreira <pedro.ferreira1@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package classes
 */
class Url {

    /**
     * URL part names.
     * @link http://php.net/manual/en/function.parse-url.php
     */
    const SCHEME = 'scheme';
    const HOST = 'host';
    const PORT = 'port';
    const USER = 'user';
    const PASS = 'pass';
    const PATH = 'path';
    const QUERY = 'query';
    const FRAGMENT = 'fragment';

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $parts;

    /**
     * Class constructor expects a valid URL as string argument.
     *
     * @param string $url
     * @throws \InvalidArgumentException
     */
    public function __construct($url) {
        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL: ' . $url);
        }

        $this->url = $url;
    }

    /**
     * Create new instance from globals.
     *
     * @param array $server
     * @return \Url
     * @throws \InvalidArgumentException
     */
    public static function fromGlobals(array $server = null) {
        if (null === $server) {
            $server = $_SERVER;
        }

        $requiredElements = array('HTTP_HOST', 'REQUEST_URI');

        foreach ($requiredElements as $element) {
            if (!isset($server[$element])) {
                throw new \InvalidArgumentException(sprintf('Missing required server array element: "%s".', $element));
            }
        }

        $url = (isset($server['HTTPS']) && $server['HTTPS'] && !in_array(strtolower($server['HTTPS']), array('off', 'no'))) ? 'https' : 'http';
        $url .= '://' . $server['HTTP_HOST'];
        $url .= $server['REQUEST_URI'];

        return new static($url);
    }

    /**
     * Internally parse given URL.
     *
     * @return void
     */
    protected function parse() {
        $this->parts = parse_url($this->url);
    }

    /**
     * Get part of given URL if defined; otherwise return default argument.
     *
     * @param string $name
     * @param string|null $default
     * @return string|null
     */
    public function getPart($name, $default = null) {
        if (null === $this->parts) {
            $this->parse();
        }

        return isset($this->parts[$name]) ? $this->parts[$name] : $default;
    }

    /**
     * Get URL scheme.
     *
     * @return string
     */
    public function getScheme() {
        return $this->getPart(static::SCHEME, 'http');
    }

    /**
     * Get URL host.
     *
     * @return string
     */
    public function getHost() {
        return $this->getPart(static::HOST, 'localhost');
    }

    /**
     * Get URL port.
     *
     * @return int
     */
    public function getPort() {
        return (int) $this->getPart(static::PORT, 80);
    }

    /**
     * Get URL user.
     *
     * @return string|null
     */
    public function getUser() {
        return $this->getPart(static::USER);
    }

    /**
     * Get URL pass.
     *
     * @return string|null
     */
    public function getPass() {
        return $this->getPart(static::PASS);
    }

    /**
     * Get URL path.
     *
     * @return string
     */
    public function getPath() {
        return $this->getPart(static::PATH, '/');
    }

    /**
     * Get URL query string.
     *
     * @return string|null
     */
    public function getQuery() {
        return $this->getPart(static::QUERY);
    }

    /**
     * Get URL fragment.
     *
     * @return string|null
     */
    public function getFragment() {
        return $this->getPart(static::FRAGMENT);
    }

    /**
     * Get relative URL.
     *
     * @return string
     */
    public function getRelative() {
        $relative = $this->getPath();

        if (null !== $query = $this->getQuery()) {
            $relative .= '?' . $query;
        }

        if (null !== $fragment = $this->getFragment()) {
            $relative .= '#' . $fragment;
        }

        return $relative;
    }

    /**
     * Get canonical URL.
     *
     * @return string
     */
    public function getCanonical() {
        $canonical = $this->getScheme() . '://';

        if (null !== $user = $this->getUser()) {
            $canonical .= $user;

            if (null !== $pass = $this->getPass()) {
                $canonical .= ':' . $pass;
            }

            $canonical .= '@';
        }

        if (null !== $host = $this->getHost()) {
            $canonical .= $host;

            if (!in_array($port = $this->getPort(), array(80, 443))) {
                $canonical .= ':' . $port;
            }
        }

        $canonical .= $this->getRelative();

        return $canonical;
    }

    /**
     * Get query values as an associative array.
     *
     * @return array
     */
    public function getQueryAsArray() {
        $query = $this->getQuery();

        if (null === $query or '' === trim($query)) {
            return array();
        }

        $values = array();

        foreach (explode('&', $query) as $pair) {
            $parts = explode('=', $pair);
            $values[$parts[0]] = count($parts) > 1 ? urldecode($parts[1]) : null;
        }

        return $values;
    }

    /**
     * Set query from an associative array of values.
     *
     * @param array $values
     * @return Url
     */
    public function setQueryValues(array $values) {
        $this->parse();

        if (0 === count($values)) {
            unset($this->parts[static::QUERY]);
            return $this;
        }

        $query = array();

        foreach ($values as $name => $value) {
            $query[] = sprintf('%s=%s', $name, urlencode($value));
        }

        $this->parts[static::QUERY] = implode('&', $query);
        return $this;
    }

    /**
     * Set a single value on the current URL query string.
     *
     * @param string $name
     * @param string $value
     *
     * @return Uri
     */
    public function setQueryValue($name, $value) {
        $values = $this->getQueryAsArray();
        $values[$name] = $value;

        return $this->setQueryValues($values);
    }

    /**
     * Output current URL in canonical format.
     * 
     * @return string
     */
    public function __toString() {
        return $this->getCanonical();
    }
}
