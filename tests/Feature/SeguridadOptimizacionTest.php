<?php

namespace Tests\Feature;

use App\Models\Local;
use App\Models\Menu;
use App\Models\User;
use App\Support\CacheConsulta;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Semana 11 (optimización de consultas) y Seguridad en aplicaciones web (SQL Injection, OWASP).
 * Ejecutar con: php artisan test --filter=SeguridadOptimizacionTest
 */
class SeguridadOptimizacionTest extends TestCase
{
    use RefreshDatabase;

    private const AJAX = ['X-Requested-With' => 'XMLHttpRequest'];

    /** Cargas típicas de SQL Injection (de la diapositiva "SQL Injection" y las más comunes). */
    private const PAYLOADS = [
        "' OR 1=1 --",
        "'; DROP TABLE users; --",
        "%' OR '1'='1",
        "\" OR \"\"=\"",
        "x' UNION SELECT email, password FROM users --",
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // ---------- SQL Injection ----------

    public function test_buscador_de_inicio_no_es_vulnerable_a_sql_injection(): void
    {
        $usuarios = User::count();

        foreach (self::PAYLOADS as $payload) {
            $r = $this->getJson('/?precio=30&disp=0&q='.urlencode($payload), self::AJAX)->assertOk();
            // Si la entrada se pegara en el SQL, "OR 1=1" devolvería todos los menús
            $this->assertSame(0, $r->json('total'), "El payload devolvió resultados: {$payload}");
        }

        $this->assertSame($usuarios, User::count());
        $this->assertTrue(Schema::hasTable('users'), 'La tabla users debe seguir existiendo');
    }

    public function test_lista_de_locales_no_es_vulnerable_a_sql_injection(): void
    {
        foreach (self::PAYLOADS as $payload) {
            $this->get('/locales?q='.urlencode($payload))->assertOk()->assertDontSee('Comedor Inti');
        }
    }

    public function test_busqueda_de_usuarios_del_admin_no_es_vulnerable(): void
    {
        $admin = User::where('email', 'admin@compamenu.pe')->firstOrFail();

        foreach (self::PAYLOADS as $payload) {
            $this->actingAs($admin)->get('/admin/usuarios?q='.urlencode($payload))
                ->assertOk()
                ->assertDontSee('estudiante@compamenu.pe');
        }
        $this->assertTrue(Schema::hasTable('users'));
    }

    public function test_login_rechaza_payloads_de_sql_injection(): void
    {
        foreach (self::PAYLOADS as $payload) {
            // Como correo: la validación lo rechaza
            $this->post('/ingresar', ['email' => $payload, 'password' => $payload])->assertSessionHasErrors('email');
            $this->assertGuest();
        }

        // Correo con apariencia válida: la consulta usa bindings, así que "OR 1=1" no entra
        $this->post('/ingresar', ['email' => "a'OR'1'='1@compamenu.pe", 'password' => "' OR 1=1 --"])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_parametros_de_la_url_solo_aceptan_numeros(): void
    {
        $menu = Menu::firstOrFail();
        $local = Local::firstOrFail();

        $this->get("/menus/{$menu->id}")->assertOk();
        $this->get('/menus/'.rawurlencode("{$menu->id} OR 1=1"))->assertNotFound();
        $this->get('/menus/'.rawurlencode("{$menu->id}; DROP TABLE menus"))->assertNotFound();
        $this->get('/locales/'.rawurlencode("{$local->id} OR 1=1"))->assertNotFound();
        $this->get('/locales/abc')->assertNotFound();

        // El filtro por local de /resenas se convierte a entero
        $this->get('/resenas?local='.urlencode("1 OR 1=1"))->assertOk();
    }

    /**
     * Revisión estática (SAST sencillo): ninguna consulta cruda del código fuente puede construirse
     * concatenando o interpolando variables. Solo se permiten bindings con "?".
     */
    public function test_el_codigo_no_construye_sql_concatenando_variables(): void
    {
        $patron = '/(whereRaw|selectRaw|orderByRaw|havingRaw|groupByRaw|DB::raw|DB::select|DB::statement|DB::unprepared)\(\s*("[^"]*\$[^"]*"|\'[^\']*\'\s*\.\s*\$)/';
        $archivos = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(base_path('app')));

        foreach ($archivos as $archivo) {
            if ($archivo->getExtension() !== 'php') {
                continue;
            }
            $this->assertSame(
                0,
                preg_match($patron, file_get_contents($archivo->getPathname())),
                'SQL construido con variables en '.$archivo->getPathname(),
            );
        }
    }

    // ---------- Optimización de consultas ----------

    /** Cuenta las consultas SQL que ejecuta una petición GET. */
    private function consultas(string $ruta, ?User $usuario = null): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        ($usuario ? $this->actingAs($usuario) : $this)->get($ruta)->assertOk();
        $total = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $total;
    }

    public function test_las_paginas_no_tienen_problema_n_mas_1(): void
    {
        $rutas = ['/', '/locales', '/remates', '/resenas'];
        $antes = array_map(fn ($ruta) => $this->consultas($ruta), $rutas);

        // Agregamos 12 menús más: si hubiera N+1, la cantidad de consultas crecería
        $local = Local::firstOrFail();
        foreach (range(1, 12) as $i) {
            Menu::create(['local_id' => $local->id, 'nombre' => "Menú de prueba {$i}", 'precio' => 5, 'porciones' => 3]);
        }

        foreach ($rutas as $i => $ruta) {
            $this->assertSame($antes[$i], $this->consultas($ruta), "La ruta {$ruta} ejecuta más consultas al crecer los datos (N+1)");
        }
    }

    public function test_el_resumen_del_admin_usa_cache_y_se_invalida_al_cambiar_datos(): void
    {
        $admin = User::where('email', 'admin@compamenu.pe')->firstOrFail();

        $primera = $this->consultas('/admin', $admin);
        $segunda = $this->consultas('/admin', $admin);
        $this->assertLessThan($primera, $segunda, 'La segunda visita debe salir del caché');

        // Al guardar un dato cambia la versión del caché y el resumen se recalcula
        $version = CacheConsulta::version();
        Menu::create(['local_id' => Local::firstOrFail()->id, 'nombre' => 'Menú nuevo', 'precio' => 6, 'porciones' => 2]);
        $this->assertNotSame($version, CacheConsulta::version());

        $tercera = $this->consultas('/admin', $admin);
        $this->assertGreaterThan($segunda, $tercera, 'Tras un cambio el resumen debe recalcularse (no servir datos viejos)');
    }

    public function test_existen_los_indices_de_la_migracion_de_optimizacion(): void
    {
        $this->assertTrue(Schema::hasIndex('locales', ['activo', 'distancia_campus']));
        $this->assertTrue(Schema::hasIndex('locales', ['activo', 'tipo_local']));
        $this->assertTrue(Schema::hasIndex('valoraciones', ['local_id', 'visible', 'puntaje']));
        $this->assertTrue(Schema::hasIndex('remates', ['activo', 'porciones', 'menu_id']));
        $this->assertTrue(Schema::hasIndex('platos', ['menu_id', 'orden']));
        $this->assertTrue(Schema::hasIndex('reservas', ['created_at']));
        $this->assertTrue(Schema::hasIndex('users', ['rol']));
        $this->assertTrue(Schema::hasIndex('valoraciones', ['visible', 'puntaje']));
    }

    // ---------- Cabeceras de seguridad (OWASP A05) ----------
    public function test_las_respuestas_incluyen_cabeceras_de_seguridad(): void
    {
        $respuesta = $this->get('/');

        $respuesta->assertOk();
        $respuesta->assertHeader('X-Content-Type-Options', 'nosniff');
        $respuesta->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $respuesta->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $respuesta->assertHeaderMissing('Strict-Transport-Security'); // solo se envía bajo HTTPS
    }

    public function test_hsts_solo_se_envia_bajo_https(): void
    {
        $this->get('https://localhost/')->assertHeader('Strict-Transport-Security');
    }
}
