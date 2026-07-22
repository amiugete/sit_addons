<?php

function geojsonFeature($geometry, $properties)
{
    return [
        'type' => 'Feature',
        'geometry' => json_decode($geometry, true),
        'properties' => $properties
    ];
}
?>