<?php

use Illuminate\Support\Facades\URL;

it('rejects unsigned channel proxy requests with 403', function () {
    $this->get('/api/m3u-proxy/channel/1')->assertStatus(403);
});

it('rejects unsigned episode proxy requests with 403', function () {
    $this->get('/api/m3u-proxy/episode/1')->assertStatus(403);
});

it('rejects unsigned channel player requests with 403', function () {
    $this->get('/api/m3u-proxy/channel/1/player')->assertStatus(403);
});

it('rejects unsigned episode player requests with 403', function () {
    $this->get('/api/m3u-proxy/episode/1/player')->assertStatus(403);
});

it('rejects expired signed channel proxy requests with 403', function () {
    $url = URL::temporarySignedRoute('m3u-proxy.channel', now()->subMinute(), ['id' => 1], absolute: false);

    $this->get($url)->assertStatus(403);
});

it('accepts valid signed channel proxy requests', function () {
    $url = URL::temporarySignedRoute('m3u-proxy.channel', now()->addHour(), ['id' => 1], absolute: false);

    // Route will 404 (no channel exists) but signature is valid — not 403
    $this->get($url)->assertStatus(404);
});

it('accepts valid signed episode proxy requests', function () {
    $url = URL::temporarySignedRoute('m3u-proxy.episode', now()->addHour(), ['id' => 1], absolute: false);

    $this->get($url)->assertStatus(404);
});

it('accepts valid signed channel player requests', function () {
    $url = URL::temporarySignedRoute('m3u-proxy.channel.player', now()->addHour(), ['id' => 1], absolute: false);

    $this->get($url)->assertStatus(404);
});

it('accepts valid signed episode player requests', function () {
    $url = URL::temporarySignedRoute('m3u-proxy.episode.player', now()->addHour(), ['id' => 1], absolute: false);

    $this->get($url)->assertStatus(404);
});

it('accepts signed channel proxy requests with extra username query param', function () {
    $url = URL::temporarySignedRoute('m3u-proxy.channel', now()->addHour(), ['id' => 1], absolute: false);
    $url .= '&username=testuser';

    // username is ignored during signature validation
    $this->get($url)->assertStatus(404);
});

it('rejects tampered signed channel proxy requests with 403', function () {
    $url = URL::temporarySignedRoute('m3u-proxy.channel', now()->addHour(), ['id' => 1], absolute: false);
    // Swap the channel id in the URL to tamper with it
    $url = str_replace('/channel/1?', '/channel/999?', $url);

    $this->get($url)->assertStatus(403);
});
