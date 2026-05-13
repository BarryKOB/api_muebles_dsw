<?php

/**
 * =============================================================
 *  TEST COMPLETO DE LA API DE MUEBLES
 *  Ejecutar: php test_api.php
 *
 *  Base URL (sin /v1 final; el script añade /v1/...):
 *    variable de entorno API_MUEBLES_TEST_BASE
 *    o por defecto http://127.0.0.1:5502/api  (alineado con Habita .env API_MUEBLES_URL puerto 5502)
 *
 *  Ejemplo PowerShell:
 *    $env:API_MUEBLES_TEST_BASE="http://localhost:5502/api"; php test_api.php
 * =============================================================
 */

$base = rtrim($_ENV['API_MUEBLES_TEST_BASE'] ?? getenv('API_MUEBLES_TEST_BASE') ?: 'http://127.0.0.1:5502/api', '/');
$pass = 0;
$fail = 0;

function test($nombre, $condicion, &$pass, &$fail) {
    if ($condicion) {
        echo "  ✅ PASS — $nombre\n";
        $pass++;
    } else {
        echo "  ❌ FAIL — $nombre\n";
        $fail++;
    }
}

function get($url, $token = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $headers = ['Accept: application/json'];
    if ($token) $headers[] = "Authorization: Bearer $token";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => json_decode($body, true), 'raw' => $body];
}

function post($url, $data, $token = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    $headers = ['Accept: application/json', 'Content-Type: application/json'];
    if ($token) $headers[] = "Authorization: Bearer $token";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => json_decode($body, true)];
}

function put($url, $data, $token) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    $headers = ['Accept: application/json', 'Content-Type: application/json', "Authorization: Bearer $token"];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => json_decode($body, true)];
}

function delete($url, $token) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', "Authorization: Bearer $token"]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => json_decode($body, true)];
}

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║        TEST API MUEBLES — base: {$base}                  ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// ── 1. GENERAR TOKENS ──────────────────────────────────────────
echo "━━━ 1. GENERAR TOKENS DE PRUEBA ━━━\n";
$r = get("$base/test/registrar");
test("Ruta /test/registrar responde 200", $r['code'] == 200, $pass, $fail);
test("Devuelve token admin", !empty($r['body']['admin']), $pass, $fail);
test("Devuelve token gestor", !empty($r['body']['gestor']), $pass, $fail);
test("Devuelve token cliente", !empty($r['body']['cliente']), $pass, $fail);

$adminToken   = $r['body']['admin'];
$gestorToken  = $r['body']['gestor'];
$clienteToken = $r['body']['cliente'];
echo "  Tokens OK → Admin: " . substr($adminToken, 0, 15) . "...\n\n";

// ── 2. CATEGORÍAS (PÚBLICO) ────────────────────────────────────
echo "━━━ 2. CATEGORÍAS — RUTAS PÚBLICAS ━━━\n";

$r = get("$base/v1/categorias");
test("GET /categorias → 200", $r['code'] == 200, $pass, $fail);
test("Devuelve array 'data'", isset($r['body']['data']), $pass, $fail);
test("Hay categorías en la respuesta", count($r['body']['data']) >= 1, $pass, $fail);
test("Primera tiene 'nombre'", isset($r['body']['data'][0]['nombre']), $pass, $fail);

$r = get("$base/v1/categorias/1");
test("GET /categorias/1 → 200", $r['code'] == 200, $pass, $fail);
test("Devuelve objeto 'data' (no array)", isset($r['body']['data']['id']), $pass, $fail);
test("Incluye muebles de esa categoría", isset($r['body']['data']['muebles']), $pass, $fail);
echo "\n";

// ── 3. MUEBLES (PÚBLICO) ──────────────────────────────────────
echo "━━━ 3. MUEBLES — RUTAS PÚBLICAS ━━━\n";

$r = get("$base/v1/muebles");
test("GET /muebles → 200", $r['code'] == 200, $pass, $fail);
test("Devuelve array 'data'", is_array($r['body']['data']), $pass, $fail);
test("Tiene paginación 'meta'", isset($r['body']['meta']), $pass, $fail);
test("Tiene paginación 'links'", isset($r['body']['links']), $pass, $fail);
$totalMuebles = (int) ($r['body']['meta']['total'] ?? 0);
test("Hay muebles en catálogo (total > 0)", $totalMuebles > 0, $pass, $fail);
test("Página actual = 1", ($r['body']['meta']['current_page'] ?? 0) == 1, $pass, $fail);
$perPage = (int) ($r['body']['meta']['per_page'] ?? 0);
test("per_page entre 1 y 100 (paginación API)", $perPage >= 1 && $perPage <= 100, $pass, $fail);
test("Cada mueble tiene 'categoria' cargada", isset($r['body']['data'][0]['categoria']['nombre']), $pass, $fail);

$r = get("$base/v1/muebles/1");
test("GET /muebles/1 → 200", $r['code'] == 200, $pass, $fail);
test("Devuelve UN objeto (no array)", isset($r['body']['data']['id']), $pass, $fail);
test("Tiene campo 'nombre'", isset($r['body']['data']['nombre']), $pass, $fail);
test("Tiene campo 'precio'", isset($r['body']['data']['precio']), $pass, $fail);
test("Tiene campo 'stock'", isset($r['body']['data']['stock']), $pass, $fail);
test("Tiene campo 'categoria'", isset($r['body']['data']['categoria']), $pass, $fail);
test("Tiene campo 'imagenes'", array_key_exists('imagenes', $r['body']['data']), $pass, $fail);

$r = get("$base/v1/muebles/99999");
test("GET /muebles/99999 → 404 (no existe)", $r['code'] == 404, $pass, $fail);
echo "\n";

// ── 4. FILTROS Y ORDENACIÓN ───────────────────────────────────
echo "━━━ 4. FILTROS, BÚSQUEDA Y ORDENACIÓN ━━━\n";

$r = get("$base/v1/muebles?categoria=1");
test("Filtro ?categoria=1 → 200", $r['code'] == 200, $pass, $fail);
$todosCategoria1 = true;
foreach ($r['body']['data'] as $m) {
    if ($m['categoria']['id'] != 1) $todosCategoria1 = false;
}
test("Todos los resultados son categoría 1", $todosCategoria1, $pass, $fail);

$r = get("$base/v1/muebles?precio_max=300");
test("Filtro ?precio_max=300 → 200", $r['code'] == 200, $pass, $fail);
$todosBaratos = true;
foreach ($r['body']['data'] as $m) {
    if ((float)$m['precio'] > 300) $todosBaratos = false;
}
test("Todos los precios son <= 300", $todosBaratos, $pass, $fail);

$r = get("$base/v1/muebles?precio_min=1000&precio_max=2000");
test("Filtro rango precio 1000-2000 → 200", $r['code'] == 200, $pass, $fail);
$enRango = true;
foreach ($r['body']['data'] as $m) {
    if ((float)$m['precio'] < 1000 || (float)$m['precio'] > 2000) $enRango = false;
}
test("Todos los precios están entre 1000-2000", $enRango, $pass, $fail);

$r = get("$base/v1/muebles?orden=precio_asc");
test("Orden ?orden=precio_asc → 200", $r['code'] == 200, $pass, $fail);
$ordenado = true;
$prev = 0;
foreach ($r['body']['data'] as $m) {
    if ((float)$m['precio'] < $prev) $ordenado = false;
    $prev = (float)$m['precio'];
}
test("Precios están en orden ascendente", $ordenado, $pass, $fail);

$r = get("$base/v1/muebles?orden=precio_desc");
$ordenadoDesc = true;
$prev = PHP_INT_MAX;
foreach ($r['body']['data'] as $m) {
    if ((float)$m['precio'] > $prev) $ordenadoDesc = false;
    $prev = (float)$m['precio'];
}
test("Orden precio_desc funciona", $ordenadoDesc, $pass, $fail);

$r = get("$base/v1/muebles?buscar=Lack");
test("Búsqueda ?buscar=Lack → 200", $r['code'] == 200, $pass, $fail);
$todosContienen = true;
foreach ($r['body']['data'] as $m) {
    if (stripos($m['nombre'], 'Lack') === false && stripos($m['descripcion'], 'Lack') === false) {
        $todosContienen = false;
    }
}
test("Todos los resultados contienen 'Lack'", $todosContienen, $pass, $fail);

$r = get("$base/v1/muebles?page=2");
test("Paginación ?page=2 → 200", $r['code'] == 200, $pass, $fail);
test("current_page = 2", ($r['body']['meta']['current_page'] ?? 0) == 2, $pass, $fail);
echo "\n";

// ── 5. SEGURIDAD — SIN TOKEN ──────────────────────────────────
echo "━━━ 5. SEGURIDAD — PETICIONES SIN TOKEN ━━━\n";

$r = post("$base/v1/muebles", ['nombre' => 'Test']);
test("POST /muebles sin token → 401", $r['code'] == 401, $pass, $fail);

$r = put("$base/v1/muebles/1", ['nombre' => 'Hack'], '');
test("PUT /muebles/1 sin token → 401", $r['code'] == 401, $pass, $fail);

$r = delete("$base/v1/muebles/1", '');
test("DELETE /muebles/1 sin token → 401", $r['code'] == 401, $pass, $fail);
echo "\n";

// ── 6. SEGURIDAD — TOKEN CLIENTE (SIN PERMISOS CRUD) ──────────
echo "━━━ 6. SEGURIDAD — TOKEN CLIENTE (sin abilities CRUD) ━━━\n";

$r = post("$base/v1/muebles", [
    'nombre' => 'Intento Cliente', 'descripcion' => 'No debería crearse',
    'precio' => 99, 'stock' => 1, 'categoria_id' => 1
], $clienteToken);
test("POST /muebles con cliente → 403 (Forbidden)", $r['code'] == 403, $pass, $fail);

$r = put("$base/v1/muebles/1", ['nombre' => 'Hack'], $clienteToken);
test("PUT /muebles/1 con cliente → 403", $r['code'] == 403, $pass, $fail);

$r = delete("$base/v1/muebles/1", $clienteToken);
test("DELETE /muebles/1 con cliente → 403", $r['code'] == 403, $pass, $fail);
echo "\n";

// ── 7. CRUD CON TOKEN ADMIN ───────────────────────────────────
echo "━━━ 7. CRUD COMPLETO — TOKEN ADMIN ━━━\n";

// CREAR
$r = post("$base/v1/muebles", [
    'nombre'       => 'Sofá Chester Test',
    'descripcion'  => 'Creado desde el test automático',
    'precio'       => 899.99,
    'stock'        => 3,
    'color'        => 'Marrón',
    'material'     => 'Cuero',
    'categoria_id' => 1,
], $adminToken);
test("POST crear mueble → 201", $r['code'] == 201, $pass, $fail);
test("Respuesta tiene 'message'", isset($r['body']['message']), $pass, $fail);
test("Mueble creado con nombre correcto", ($r['body']['data']['nombre'] ?? '') == 'Sofá Chester Test', $pass, $fail);
$nuevoId = $r['body']['data']['id'] ?? null;
echo "  Mueble creado con ID: $nuevoId\n";

// VALIDACIÓN — campos obligatorios
$r = post("$base/v1/muebles", ['nombre' => 'Incompleto'], $adminToken);
test("POST sin campos obligatorios → 422", $r['code'] == 422, $pass, $fail);
test("Error menciona 'descripcion'", isset($r['body']['errors']['descripcion']), $pass, $fail);

// LEER el mueble recién creado
if ($nuevoId) {
    $r = get("$base/v1/muebles/$nuevoId");
    test("GET /muebles/$nuevoId → 200 (existe)", $r['code'] == 200, $pass, $fail);
    test("Nombre es 'Sofá Chester Test'", ($r['body']['data']['nombre'] ?? '') == 'Sofá Chester Test', $pass, $fail);

    // EDITAR
    $r = put("$base/v1/muebles/$nuevoId", [
        'nombre' => 'Sofá Chester Editado',
        'precio' => 749.50,
    ], $adminToken);
    test("PUT editar mueble → 200", $r['code'] == 200, $pass, $fail);
    test("Nombre actualizado", ($r['body']['data']['nombre'] ?? '') == 'Sofá Chester Editado', $pass, $fail);
    test("Precio actualizado a 749.50", (float)($r['body']['data']['precio'] ?? 0) == 749.50, $pass, $fail);

    // ELIMINAR
    $r = delete("$base/v1/muebles/$nuevoId", $adminToken);
    test("DELETE eliminar mueble → 200", $r['code'] == 200, $pass, $fail);
    test("Mensaje de confirmación", isset($r['body']['message']), $pass, $fail);

    // Verificar que ya no existe
    $r = get("$base/v1/muebles/$nuevoId");
    test("GET mueble eliminado → 404", $r['code'] == 404, $pass, $fail);
}
echo "\n";

// ── 8. CRUD CON TOKEN GESTOR ──────────────────────────────────
echo "━━━ 8. CRUD — TOKEN GESTOR ━━━\n";

$r = post("$base/v1/muebles", [
    'nombre'       => 'Mesa Gestor Test',
    'descripcion'  => 'Creado por gestor',
    'precio'       => 199.99,
    'stock'        => 10,
    'categoria_id' => 2,
], $gestorToken);
test("POST crear con gestor → 201", $r['code'] == 201, $pass, $fail);
$gestorId = $r['body']['data']['id'] ?? null;

if ($gestorId) {
    $r = put("$base/v1/muebles/$gestorId", ['precio' => 149.99], $gestorToken);
    test("PUT editar con gestor → 200", $r['code'] == 200, $pass, $fail);

    $r = delete("$base/v1/muebles/$gestorId", $gestorToken);
    test("DELETE eliminar con gestor → 200", $r['code'] == 200, $pass, $fail);
}
echo "\n";

// ── 9. CATEGORÍAS CRUD ────────────────────────────────────────
echo "━━━ 9. CATEGORÍAS — CRUD PROTEGIDO ━━━\n";

$r = post("$base/v1/categorias", ['nombre' => 'Test Cat', 'descripcion' => 'Para test'], $adminToken);
test("POST crear categoría → 201", $r['code'] == 201, $pass, $fail);
$catId = $r['body']['data']['id'] ?? null;

if ($catId) {
    $r = put("$base/v1/categorias/$catId", ['nombre' => 'Test Cat Editada'], $adminToken);
    test("PUT editar categoría → 200", $r['code'] == 200, $pass, $fail);

    $r = delete("$base/v1/categorias/$catId", $adminToken);
    test("DELETE eliminar categoría → 200", $r['code'] == 200, $pass, $fail);
}

$r = post("$base/v1/categorias", ['nombre' => 'Hack'], $clienteToken);
test("POST categoría con cliente → 403", $r['code'] == 403, $pass, $fail);
echo "\n";

// ── RESUMEN ───────────────────────────────────────────────────
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                    RESUMEN FINAL                         ║\n";
echo "╠═══════════════════════════════════════════════════════════╣\n";
$total = $pass + $fail;
echo "║  Total tests: $total                                       ║\n";
echo "║  ✅ Pasados:  $pass                                       ║\n";
echo "║  ❌ Fallidos: $fail                                         ║\n";
if ($fail == 0) {
    echo "║                                                           ║\n";
    echo "║  🎉 ¡TODOS LOS TESTS PASADOS! API 100% FUNCIONAL 🎉     ║\n";
}
echo "╚═══════════════════════════════════════════════════════════╝\n";
