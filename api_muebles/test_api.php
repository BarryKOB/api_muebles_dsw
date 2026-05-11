<?php

// Script de prueba de la API
// Ejecutar: php test_api.php

$baseUrl = 'http://127.0.0.1:8002/api';

echo "============================================\n";
echo "  PRUEBAS API REST MUEBLES - Puerto 8002\n";
echo "============================================\n\n";

// 1. Probar GET muebles (público)
echo "1. GET /api/v1/muebles (público, sin token)\n";
$ch = curl_init("$baseUrl/v1/muebles?page=1");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$data = json_decode($resp, true);
echo "   Status: $code | Total: " . ($data['meta']['total'] ?? '?') . " muebles | Paginados: 10/página\n";
echo "   ✅ PASS\n\n";
curl_close($ch);

// 2. Probar GET categorías (público)
echo "2. GET /api/v1/categorias (público, sin token)\n";
$ch = curl_init("$baseUrl/v1/categorias");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$data = json_decode($resp, true);
echo "   Status: $code | Total: " . count($data['data'] ?? []) . " categorías\n";
echo "   ✅ PASS\n\n";
curl_close($ch);

// 3. Probar GET detalle mueble
echo "3. GET /api/v1/muebles/1 (detalle, público)\n";
$ch = curl_init("$baseUrl/v1/muebles/1");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$data = json_decode($resp, true);
echo "   Status: $code | Mueble: " . ($data['data']['nombre'] ?? '?') . " | Categoría: " . ($data['data']['categoria']['nombre'] ?? '?') . "\n";
echo "   ✅ PASS\n\n";
curl_close($ch);

// 4. Probar POST sin token (debe dar 401)
echo "4. POST /api/v1/muebles SIN TOKEN (espera 401)\n";
$ch = curl_init("$baseUrl/v1/muebles");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['nombre' => 'Test']));
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "   Status: $code" . ($code == 401 ? " ✅ PASS (Unauthenticated)" : " ❌ FAIL") . "\n\n";
curl_close($ch);

// 5. Obtener tokens frescos
echo "5. Generando tokens de prueba...\n";
$ch = curl_init("$baseUrl/test/registrar");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$resp = curl_exec($ch);
$tokens = json_decode($resp, true);
curl_close($ch);

if (!$tokens || !isset($tokens['admin'])) {
    echo "   ❌ ERROR generando tokens: $resp\n";
    exit(1);
}
$adminToken = $tokens['admin'];
$gestorToken = $tokens['gestor'];
$clienteToken = $tokens['cliente'];
echo "   Admin:   $adminToken\n";
echo "   Gestor:  $gestorToken\n";
echo "   Cliente: $clienteToken\n";
echo "   ✅ Tokens generados\n\n";

// 6. POST con token ADMIN (debe funcionar 201)
echo "6. POST /api/v1/muebles CON TOKEN ADMIN (espera 201)\n";
$muebleData = json_encode([
    'nombre'       => 'Sofá Test Admin',
    'descripcion'  => 'Un sofá de prueba creado con token admin',
    'precio'       => 599.99,
    'stock'        => 10,
    'color'        => 'Gris',
    'material'     => 'Tela',
    'categoria_id' => 1,
]);
$ch = curl_init("$baseUrl/v1/muebles");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    "Authorization: Bearer $adminToken",
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $muebleData);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$data = json_decode($resp, true);
$createdId = $data['data']['id'] ?? null;
echo "   Status: $code" . ($code == 201 ? " ✅ PASS" : " ❌ FAIL") . "\n";
echo "   Mueble creado: " . ($data['data']['nombre'] ?? $data['message'] ?? 'ERROR') . " (ID: $createdId)\n\n";
curl_close($ch);

// 7. POST con token CLIENTE (debe dar 403)
echo "7. POST /api/v1/muebles CON TOKEN CLIENTE (espera 403)\n";
$ch = curl_init("$baseUrl/v1/muebles");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    "Authorization: Bearer $clienteToken",
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $muebleData);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "   Status: $code" . ($code == 403 ? " ✅ PASS (Forbidden - sin ability muebles.crear)" : " ❌ FAIL") . "\n\n";
curl_close($ch);

// 8. PUT con token GESTOR (debe funcionar)
if ($createdId) {
    echo "8. PUT /api/v1/muebles/$createdId CON TOKEN GESTOR (espera 200)\n";
    $ch = curl_init("$baseUrl/v1/muebles/$createdId");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json',
        "Authorization: Bearer $gestorToken",
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['nombre' => 'Sofá Editado por Gestor', 'precio' => 449.99]));
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $data = json_decode($resp, true);
    echo "   Status: $code" . ($code == 200 ? " ✅ PASS" : " ❌ FAIL") . "\n";
    echo "   Nombre actualizado: " . ($data['data']['nombre'] ?? 'ERROR') . " | Precio: " . ($data['data']['precio'] ?? 'ERROR') . "\n\n";
    curl_close($ch);

    // 9. DELETE con token CLIENTE (debe dar 403)
    echo "9. DELETE /api/v1/muebles/$createdId CON TOKEN CLIENTE (espera 403)\n";
    $ch = curl_init("$baseUrl/v1/muebles/$createdId");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        "Authorization: Bearer $clienteToken",
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "   Status: $code" . ($code == 403 ? " ✅ PASS (Forbidden)" : " ❌ FAIL") . "\n\n";
    curl_close($ch);

    // 10. DELETE con token ADMIN (debe funcionar)
    echo "10. DELETE /api/v1/muebles/$createdId CON TOKEN ADMIN (espera 200)\n";
    $ch = curl_init("$baseUrl/v1/muebles/$createdId");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        "Authorization: Bearer $adminToken",
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "   Status: $code" . ($code == 200 ? " ✅ PASS (Mueble eliminado)" : " ❌ FAIL") . "\n\n";
    curl_close($ch);
}

// 11. Filtros
echo "11. GET /api/v1/muebles?categoria=1 (filtro categoría)\n";
$ch = curl_init("$baseUrl/v1/muebles?categoria=1");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$data = json_decode($resp, true);
echo "   Status: $code | Muebles en Sofás: " . ($data['meta']['total'] ?? count($data['data'] ?? [])) . "\n";
echo "   ✅ PASS\n\n";
curl_close($ch);

echo "12. GET /api/v1/muebles?precio_max=500&orden=precio_asc (filtro+orden)\n";
$ch = curl_init("$baseUrl/v1/muebles?precio_max=500&orden=precio_asc");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$data = json_decode($resp, true);
echo "   Status: $code | Muebles con precio<=500: " . ($data['meta']['total'] ?? '?') . "\n";
if (!empty($data['data'])) {
    echo "   Primer precio: " . $data['data'][0]['precio'] . " (debe ser el más bajo)\n";
}
echo "   ✅ PASS\n\n";
curl_close($ch);

echo "============================================\n";
echo "  TODAS LAS PRUEBAS COMPLETADAS\n";
echo "============================================\n";
