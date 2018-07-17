<?php

declare(strict_types = 1);

$parameters = [];

if (($hostProto = \getenv('HOST_PROTO')) !== false) {
    $parameters['hostProto'] = $hostProto;
}
if (($hostUri = \getenv('HOST_URI')) !== false) {
    $parameters['hostUri'] = $hostUri;
}

return ['parameters' => $parameters];
