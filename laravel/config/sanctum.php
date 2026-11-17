<?php

/*
| Only the keys this app overrides. Laravel merges the rest from Sanctum's
| own config (mergeConfigFrom), so stateful domains, guards, etc. keep their
| package defaults.
*/

return [

    /*
    | Personal access tokens expire after this many minutes. Previously null
    | (never), so a stolen staff token from the admin panel's localStorage or
    | a lost phone's citizen token worked forever. Default: 7 days. Expired
    | rows are deleted daily by `sanctum:prune-expired` (routes/console.php).
    */
    'expiration' => (int) env('SANCTUM_EXPIRATION', 60 * 24 * 7) ?: null,

];
