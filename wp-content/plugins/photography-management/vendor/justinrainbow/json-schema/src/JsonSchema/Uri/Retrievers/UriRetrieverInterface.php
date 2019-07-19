<?php
namespace JsonSchema\Uri\Retrievers;

/**
 * Interface for URI retrievers
 *
 * @author Sander Coolen <sander@jibber.nl>
 */
interface UriRetrieverInterface
{
    /**
     * Retrieve a schema from the specified URI
     *
     * @param string $uri URI that resolves to a JSON schema
     *
     * @throws \JsonSchema\Exception\ResourceNotFoundException
     *
     * @return mixed string|null
     */
    public function retrieve($uri);

    /**
     * Get media content type
     *
     * @return string
     */
    public function getContentType();
}
