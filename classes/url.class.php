<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Utility class to handle URLs.
 *
 * @author Pedro Ferreira <pedro.ferreira1@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package classes
 */
class Url
{
    /**
     * URL part names.
     * @link http://php.net/manual/en/function.parse-url.php
     */
    public const SCHEME = 'scheme';
    public const HOST = 'host';
    public const PORT = 'port';
    public const USER = 'user';
    public const PASS = 'pass';
    public const PATH = 'path';
    public const QUERY = 'query';
    public const FRAGMENT = 'fragment';

    /** The path relative to the base of ExamSys. */
    public const ROGOPATH = 'rogo';

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
    public function __construct($url)
    {
        $this->url = $url;
        $this->parse();

        if ($this->parts === false) {
            // The url was too corrupted to be parsed.
            throw new \InvalidArgumentException('Invalid URL: ' . $url);
        }

        // Now validate the non-query parts of the url.
        $plainurl = $this->getPart(static::SCHEME, 'http')
            . '://' . $this->getPart(static::HOST) . '/'
            . $this->getPart(static::PATH, '');

        $cleanurl = param::clean($plainurl, param::URL);

        if (is_null($cleanurl) or $plainurl !== $cleanurl) {
            // Something was invalid in the url.
            throw new \InvalidArgumentException('Invalid URL: ' . $url);
        }
    }

    /**
     * Create new instance from globals.
     *
     * @param array $server
     * @return \Url
     * @throws \InvalidArgumentException
     */
    public static function fromGlobals(array $server = null)
    {
        if (null === $server) {
            $server = $_SERVER;
        }

        $requiredElements = array('HTTP_HOST', 'REQUEST_URI');

        foreach ($requiredElements as $element) {
            if (!isset($server[$element])) {
                throw new \InvalidArgumentException(sprintf('Missing required server array element: "%s".', $element));
            }
        }

        $url = (isset($server['HTTPS']) && $server['HTTPS'] && !in_array(mb_strtolower($server['HTTPS']), array('off', 'no'))) ? 'https' : 'http';
        $url .= '://' . $server['HTTP_HOST'];
        $url .= $server['REQUEST_URI'];

        return new static($url);
    }

    /**
     * Internally parse given URL.
     *
     * @return void
     */
    protected function parse()
    {
        $this->parts = parse_url($this->url);

        // Get the base url with no trailing space.
        $base = rtrim(Config::get_instance()->get('cfg_root_path'), '/');

        // Calculate the URL relative to the base of ExamSys.
        if (!empty($base) and mb_strpos($this->parts[static::PATH], $base) === 0) {
            // ExamSys is in a sub directory.
            $this->parts[self::ROGOPATH] = mb_substr($this->parts[static::PATH], mb_strlen($base));
        } else {
            $this->parts[self::ROGOPATH] = $this->parts[static::PATH];
        }
    }

    /**
     * Get part of given URL if defined; otherwise return default argument.
     *
     * @param string $name
     * @param string|null $default
     * @return string|null
     */
    public function getPart($name, $default = null)
    {
        return isset($this->parts[$name]) ? $this->parts[$name] : $default;
    }

    /**
     * Get URL scheme.
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->getPart(static::SCHEME, 'http');
    }

    /**
     * Get URL host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->getPart(static::HOST, 'localhost');
    }

    /**
     * Get URL port.
     *
     * @return int
     */
    public function getPort()
    {
        return (int) $this->getPart(static::PORT, 80);
    }

    /**
     * Get URL user.
     *
     * @return string|null
     */
    public function getUser()
    {
        return $this->getPart(static::USER);
    }

    /**
     * Get URL pass.
     *
     * @return string|null
     */
    public function getPass()
    {
        return $this->getPart(static::PASS);
    }

    /**
     * Get URL path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getPart(static::PATH, '/');
    }

    /**
     * Get URL path relative to the ExamSys base path.
     *
     * @return string
     */
    public function getRogoPath()
    {
        return $this->getPart(static::ROGOPATH, '/');
    }

    /**
     * Get URL query string.
     *
     * @return string|null
     */
    public function getQuery()
    {
        return $this->getPart(static::QUERY);
    }

    /**
     * Get URL fragment.
     *
     * @return string|null
     */
    public function getFragment()
    {
        return $this->getPart(static::FRAGMENT);
    }

    /**
     * Get relative URL.
     *
     * @return string
     */
    public function getRelative()
    {
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
    public function getCanonical()
    {
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
    public function getQueryAsArray()
    {
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
    public function setQueryValues(array $values)
    {
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
    public function setQueryValue($name, $value)
    {
        $values = $this->getQueryAsArray();
        $values[$name] = $value;

        return $this->setQueryValues($values);
    }

    /**
     * Output current URL in canonical format.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getCanonical();
    }
}
