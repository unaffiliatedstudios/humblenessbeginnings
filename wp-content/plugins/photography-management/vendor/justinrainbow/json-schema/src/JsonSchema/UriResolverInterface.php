<?php
namespace JsonSchema;

/**
 * @package JsonSchema
 */
interface UriResolverInterface
{
    /**
     * Resolves a URI
     *
     * @param string      $uri     Absolute or relative
     * @param null|string $baseUri Optional base URI
     *
     * @return string Absolute URI
     */
    public function resolve($uri, $baseUri = null);
}
