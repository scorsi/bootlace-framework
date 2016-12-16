<?php

namespace Bootlace\Http\Response\Cookie;

/**
 * Class CookieBuilder
 *
 * @package Bootlace\Http
 */
class CookieBuilder implements CookieBuilderInterface
{
    /* @var string $defaultDomain */
    private $defaultDomain = '';

    /* @var string $defaultPath */
    private $defaultPath = '/';

    /* @var bool $defaultSecure */
    private $defaultSecure = true;

    /* @var bool $defaultHttpOnly */
    private $defaultHttpOnly = true;

    /**
     * Sets the default Cookie domain property.
     *
     * @param string $domain
     * @return CookieBuilderInterface
     */
    public function setDefaultDomain(string $domain): CookieBuilderInterface
    {
        $this->defaultDomain = $domain;
        return $this;
    }

    /**
     * Sets the default Cookie path property.
     *
     * @param string $path
     * @return CookieBuilderInterface
     */
    public function setDefaultPath(string $path): CookieBuilderInterface
    {
        $this->defaultPath = $path;
        return $this;
    }

    /**
     * Sets the default Cookie secure property.
     * @param bool $secure
     * @return CookieBuilderInterface
     */
    public function setDefaultSecure(bool $secure): CookieBuilderInterface
    {
        $this->defaultSecure = $secure;
        return $this;
    }

    /**
     * Sets the default Cookie HttpOnly property.
     * @param bool $httpOnly
     * @return CookieBuilderInterface
     */
    public function setDefaultHttpOnly(bool $httpOnly): CookieBuilderInterface
    {
        $this->defaultHttpOnly = $httpOnly;
        return $this;
    }

    /**
     * Build a new Cookie.
     *
     * @param string $name
     * @param string $value
     * @return CookieInterface
     */
    public function build(string $name, string $value): CookieInterface
    {
        $cookie = new Cookie($name, $value);
        $cookie->setPath($this->defaultPath);
        $cookie->setSecure($this->defaultSecure);
        $cookie->setHttpOnly($this->defaultHttpOnly);
        if ($this->defaultDomain !== null) {
            $cookie->setDomain($this->defaultDomain);
        }
        return $cookie;
    }
}