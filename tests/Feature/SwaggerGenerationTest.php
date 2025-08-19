<?php

it('generates swagger json', function () {
    // Ejecuta el comando dentro del proceso de prueba
    $this->artisan('l5-swagger:generate')->assertExitCode(0);

    $path = storage_path('api-docs/api-docs.json');
    expect(file_exists($path))->toBeTrue();

    $json = json_decode(file_get_contents($path), true);
    expect($json)->toBeArray();
    expect($json['openapi'] ?? null)->toBeString(); // "3.0.x"
    // Debe tener el securitySchemes bearerAuth
    expect($json['components']['securitySchemes']['bearerAuth'] ?? null)->not->toBeNull();
});
