<?php

declare(strict_types = 1);

$parameters = [];

if (($appHostProto = \getenv('APP_HOST_PROTO')) !== false) {
    $parameters['appHostProto'] = $appHostProto;
}
if (($appHostUri = \getenv('APP_HOST_URI')) !== false) {
    $parameters['appHostUri'] = $appHostUri;
}

return ['parameters' => $parameters];
