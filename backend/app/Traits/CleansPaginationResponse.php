<?php

namespace App\Traits;

use App\Helpers\CamelCaseHelper;

trait CleansPaginationResponse
{
    /**
     * Customize the pagination information for the resource.
     *
     * This method is automatically called by Laravel when using Resource::collection()
     * with a paginator. It removes unnecessary fields like 'links', 'meta.path', and 'meta.to'
     * to keep the API response clean and consistent with frontend expectations.
     * Additionally, it converts all pagination metadata to camelCase format.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $paginated
     * @param  array  $default
     * @return array
     */
    public function paginationInformation($request, $paginated, $default)
    {
        // Remove 'links' array from root level
        unset($default['links']);

        // Clean 'meta' if it exists and convert to camelCase
        if (isset($default['meta'])) {
            unset(
                $default['meta']['links'], // Remove 'links' inside 'meta' if present
                $default['meta']['path'],  // Remove full URL path
                $default['meta']['to']      // Remove 'to' field (duplicate of last item index)
            );
            
            // Convert remaining meta fields to camelCase
            $default['meta'] = CamelCaseHelper::convertPaginationMetadata($default['meta']);
        }

        // Convert data array keys to camelCase if it exists
        if (isset($default['data']) && is_array($default['data'])) {
            $default['data'] = CamelCaseHelper::convertToCamelCase($default['data']);
        }

        return $default;
    }
}
