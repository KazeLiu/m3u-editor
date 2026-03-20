<?php

namespace App\Services;

use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\URL;

/**
 * Service to handle proxy URL generation for channels and episodes.
 */
class ProxyService
{
    /**
     * Base URL for the proxy service
     *
     * @var string
     */
    public $baseUrl;

    /**
     * Constructor
     */
    public function __construct()
    {
        // See if proxy override is enabled
        $proxyUrlOverride = config('proxy.url_override');

        // See if override settings apply
        if (! $proxyUrlOverride || empty($proxyUrlOverride)) {
            try {
                $settings = app(GeneralSettings::class);
                $proxyUrlOverride = $settings->url_override ?? null;
            } catch (\Exception $e) {
            }
        }

        // Use the override URL or default to application URL
        if ($proxyUrlOverride && filter_var($proxyUrlOverride, FILTER_VALIDATE_URL)) {
            $url = rtrim($proxyUrlOverride, '/');
        } else {
            // Use `url('')` to get request aware URL, which respects the current request's scheme and host, and is more reliable in various environments (e.g., behind proxies, load balancers)
            $url = url('');
        }

        // Set the base URL for the proxy service
        $this->baseUrl = $url;
    }

    /**
     * Get the base URL for the proxy service
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Get the proxy URL for a channel
     *
     * @param  string|int  $id
     * @param  string|null  $playlistUuid  Optional playlist UUID for context (e.g., merged playlists)
     * @param  string|null  $username  Optional username appended after signing (ignored during signature validation)
     * @return string
     */
    public function getProxyUrlForChannel($id, $playlistUuid = null, $username = null)
    {
        $params = array_filter(['id' => $id, 'uuid' => $playlistUuid]);
        $url = $this->temporarySignedRoute('m3u-proxy.channel', $params);

        // Username is appended after signing; the signed middleware ignores it during validation
        if ($username) {
            $url .= '&username='.urlencode($username);
        }

        return $url;
    }

    /**
     * Get the proxy URL for an episode
     *
     * @param  string|int  $id
     * @param  string|null  $playlistUuid  Optional playlist UUID for context (e.g., merged playlists)
     * @return string
     */
    public function getProxyUrlForEpisode($id, $playlistUuid = null)
    {
        $params = array_filter(['id' => $id, 'uuid' => $playlistUuid]);

        return $this->temporarySignedRoute('m3u-proxy.episode', $params);
    }

    /**
     * Generate a 1-hour temporary signed route URL.
     *
     * Uses a relative signature (path + query only) so that it works correctly
     * regardless of the configured base URL override or proxy environment.
     * The base URL is then prepended to produce a fully absolute URL for external clients.
     */
    private function temporarySignedRoute(string $name, array $parameters = []): string
    {
        // Generate with absolute: false so the signature covers only the relative path,
        // making it immune to scheme/host differences (e.g. reverse proxies, URL overrides).
        $relativePath = URL::temporarySignedRoute($name, now()->addHour(), $parameters, absolute: false);

        return $this->baseUrl.$relativePath;
    }
}
