<?php

namespace Tests\Feature;

use App\Models\Local;
use App\Models\Menu;
use App\Models\Remate;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Valoracion;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/** Pruebas de los flujos principales de CompaMenu. Ejecutar con: php artisan test */
class CompaMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function usuario(string $correo): User
    {
        return User::where('email', $correo)->firstOrFail();
    }

    // ---------- Páginas públicas ----------

    public function test_paginas_publicas_cargan(): void
    {
        foreach (['/', '/locales', '/remates', '/resenas', '/proyecto', '/ingresar', '/registro'] as $ruta) {
            $this->get($ruta)->assertOk();
        }
        $this->get('/')->assertSee('Ají de gallina con arroz')->assertSee('Comedor Inti');
        $menu = Menu::first();
        $this->get("/menus/{$menu->id}")->assertOk()->assertSee('Qué incluye');
        $local = Local::first();
        $this->get("/locales/{$local->id}")->assertOk();
    }

    public function test_buscador_filtra_por_precio_y_devuelve_json(): void
    {
        $r = $this->getJson('/?precio=6.5&disp=1', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
        $html = $r->json('html');
        $this->assertStringContainsString('Menestrón', $html);
        $this->assertStringContainsString('Caldo de gallina', $html);
        $this->assertStringNotContainsString('Ceviche de trucha', $html);
        $this->assertStringNotContainsString('Lomo saltado', $html);

        $r = $this->getJson('/?precio=12&tipo=vegetariano&distancia=200', ['X-Requested-With' => 'XMLHttpRequest']);
        $this->assertStringContainsString('Menestrón', $r->json('html'));
        $this->assertStringNotContainsString('Crema de zapallo', $r->json('html')); // está a 220 m

        $r = $this->getJson('/?precio=12&rem=1', ['X-Requested-With' => 'XMLHttpRequest']);
        $this->assertSame(3, $r->json('total'));

        // Una búsqueda con comodines no rompe la consulta
        $this->getJson('/?q=%25_%27%22', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();
    }

    public function test_texto_se_escapa_en_pantalla(): void
    {
        $est = $this->usuario('estudiante@compamenu.pe');
        $local = Local::first();
        $this->actingAs($est)->post('/resenas', [
            'local_id' => $local->id, 'puntaje' => 4, 'comentario' => 'Muy rico <script>alert(1)</script> gracias',
        ])->assertRedirect();
        $this->get('/resenas')->assertDontSee('<script>alert(1)</script>', false);
    }

    // ---------- Registro, ingreso y roles ----------

    public function test_registro_de_estudiante_y_de_establecimiento(): void
    {
        $this->post('/registro', [
            'name' => 'Ana Quispe', 'email' => 'ana@correo.pe', 'password' => 'Clave1234', 'password_confirmation' => 'Clave1234',
            'rol' => 'estudiante',
        ])->assertRedirect('/');
        $this->assertAuthenticated();
        $ana = User::where('email', 'ana@correo.pe')->first();
        $this->assertSame('estudiante', $ana->rol);
        $this->assertNotSame('Clave1234', $ana->password); // guardada con hash

        auth()->logout();

        $this->post('/registro', [
            'name' => 'Luis Dueño', 'email' => 'luis@correo.pe', 'password' => 'Clave1234', 'password_confirmation' => 'Clave1234',
            'rol' => 'establecimiento', 'local_nombre' => 'Comedor Luis', 'tipo_local' => 'kiosko', 'distancia_campus' => 120,
        ])->assertRedirect(route('panel.menus.index'));
        $luis = User::where('email', 'luis@correo.pe')->first();
        $this->assertSame('Comedor Luis', $luis->local->nombre);
    }

    public function test_registro_valida_datos_y_no_permite_crear_administradores(): void
    {
        $this->post('/registro', ['name' => 'X', 'email' => 'malo', 'password' => '123', 'rol' => 'estudiante'])
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->post('/registro', [
            'name' => 'Hacker', 'email' => 'h@correo.pe', 'password' => 'Clave1234', 'password_confirmation' => 'Clave1234',
            'rol' => 'administrador',
        ])->assertSessionHasErrors('rol');
        $this->assertDatabaseMissing('users', ['email' => 'h@correo.pe']);

        $this->post('/registro', [
            'name' => 'Repetido', 'email' => 'estudiante@compamenu.pe', 'password' => 'Clave1234', 'password_confirmation' => 'Clave1234',
            'rol' => 'estudiante',
        ])->assertSessionHasErrors('email');
    }

    public function test_login_logout_y_destino_por_rol(): void
    {
        $this->post('/ingresar', ['email' => 'estudiante@compamenu.pe', 'password' => 'mala'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/ingresar', ['email' => 'admin@compamenu.pe', 'password' => 'Compa2026!'])->assertRedirect(route('admin.resumen'));
        $this->post('/salir')->assertRedirect(route('inicio'));
        $this->assertGuest();

        $this->post('/ingresar', ['email' => 'inti@compamenu.pe', 'password' => 'Compa2026!'])->assertRedirect(route('panel.menus.index'));
    }

    public function test_cuenta_desactivada_no_puede_ingresar(): void
    {
        $est = $this->usuario('estudiante@compamenu.pe');
        $est->update(['activo' => false]);
        $this->post('/ingresar', ['email' => $est->email, 'password' => 'Compa2026!'])->assertSessionHasErrors('email');
        $this->assertGuest();

        // Si ya tenía la sesión abierta, la próxima petición la cierra
        $this->actingAs($est)->get('/mis-reservas')->assertRedirect(route('login'));
    }

    public function test_acceso_por_rol(): void
    {
        $est = $this->usuario('estudiante@compamenu.pe');
        $dueno = $this->usuario('inti@compamenu.pe');
        $admin = $this->usuario('admin@compamenu.pe');

        $this->get('/mi-local/menus')->assertRedirect(route('login'));
        $this->get('/mis-reservas')->assertRedirect(route('login'));

        $this->actingAs($est)->get('/mi-local/menus')->assertForbidden();
        $this->actingAs($est)->get('/admin')->assertForbidden();
        $this->actingAs($dueno)->get('/admin')->assertForbidden();
        $this->actingAs($dueno)->get('/mis-reservas')->assertForbidden();
        $this->actingAs($admin)->get('/mi-local/menus')->assertForbidden();

        $this->actingAs($dueno)->get('/mi-local/menus')->assertOk()->assertSee('Ají de gallina');
        foreach (['/admin', '/admin/usuarios', '/admin/locales', '/admin/menus', '/admin/valoraciones', '/admin/reservas'] as $ruta) {
            $this->actingAs($admin)->get($ruta)->assertOk();
        }
        $this->actingAs($est)->get('/mis-reservas')->assertOk()->assertSee('DEMO01');
    }

    // ---------- Reservas ----------

    public function test_invitado_que_reserva_va_al_login(): void
    {
        $menu = Menu::first();
        $this->get("/reservar/menu/{$menu->id}")->assertRedirect(route('login'));
    }

    public function test_estudiante_reserva_y_se_descuentan_porciones(): void
    {
        $est = $this->usuario('estudiante@compamenu.pe');
        $menu = Menu::where('nombre', 'like', 'Caldo de gallina%')->first();
        $antes = $menu->porciones;

        $this->actingAs($est)->get("/reservar/menu/{$menu->id}")->assertOk()->assertSee('Confirmar reserva');

        $this->actingAs($est)->post('/reservas', [
            'menu_id' => $menu->id, 'cantidad' => 2, 'hora_recojo' => '12:30', 'nota' => 'Sin <b>ají</b>',
        ])->assertRedirect(route('reservas.mis'));

        $reserva = Reserva::where('menu_id', $menu->id)->first();
        $this->assertSame('pendiente', $reserva->estado);
        $this->assertSame('Sin ají', $reserva->nota); // etiquetas HTML eliminadas
        $this->assertSame($antes - 2, $menu->fresh()->porciones);
        $this->assertEquals(12.00, $reserva->total); // 2 x S/. 6.00

        // Cancelar devuelve las porciones
        $this->actingAs($est)->patch("/reservas/{$reserva->id}/cancelar")->assertSessionHas('exito');
        $this->assertSame('cancelada', $reserva->fresh()->estado);
        $this->assertSame($antes, $menu->fresh()->porciones);
        // y no se puede cancelar dos veces
        $this->actingAs($est)->patch("/reservas/{$reserva->id}/cancelar")->assertSessionHas('error');
        $this->assertSame($antes, $menu->fresh()->porciones);
    }

    public function test_reserva_valida_stock_horario_y_dueno(): void
    {
        $est = $this->usuario('estudiante@compamenu.pe');
        $otra = $this->usuario('lucia@compamenu.pe');
        $menu = Menu::where('nombre', 'like', 'Ceviche de trucha%')->first();
        $menu->update(['porciones' => 1]);

        $this->actingAs($est)->post('/reservas', ['menu_id' => $menu->id, 'cantidad' => 3, 'hora_recojo' => '12:00'])
            ->assertSessionHasErrors('cantidad');
        $this->actingAs($est)->post('/reservas', ['menu_id' => $menu->id, 'cantidad' => 1, 'hora_recojo' => '08:00'])
            ->assertSessionHasErrors('hora_recojo');
        $this->actingAs($est)->post('/reservas', ['menu_id' => $menu->id, 'cantidad' => 9, 'hora_recojo' => '12:00'])
            ->assertSessionHasErrors('cantidad');

        $this->actingAs($est)->post('/reservas', ['menu_id' => $menu->id, 'cantidad' => 1, 'hora_recojo' => '12:00'])->assertRedirect();
        $this->assertSame(0, $menu->fresh()->porciones);
        // Ya no quedan porciones
        $this->actingAs($otra)->post('/reservas', ['menu_id' => $menu->id, 'cantidad' => 1, 'hora_recojo' => '12:00'])
            ->assertSessionHasErrors('cantidad');
        $this->actingAs($otra)->get("/reservar/menu/{$menu->id}")->assertRedirect(route('menus.show', $menu));

        // Nadie cancela la reserva de otro
        $reserva = Reserva::where('menu_id', $menu->id)->first();
        $this->actingAs($otra)->patch("/reservas/{$reserva->id}/cancelar")->assertForbidden();
    }

    public function test_reserva_de_remate_usa_su_precio_y_su_stock(): void
    {
        $est = $this->usuario('estudiante@compamenu.pe');
        $remate = Remate::first();
        $stockMenu = $remate->menu->porciones;
        $stockRemate = $remate->porciones;

        $this->actingAs($est)->get("/reservar/remate/{$remate->id}")->assertOk();
        $this->actingAs($est)->post('/reservas', [
            'menu_id' => $remate->menu_id, 'remate_id' => $remate->id, 'cantidad' => 1, 'hora_recojo' => '15:30',
        ])->assertRedirect(route('reservas.mis'));

        $reserva = Reserva::where('remate_id', $remate->id)->first();
        $this->assertEquals($remate->precio_remate, $reserva->precio_unitario);
        $this->assertSame($stockRemate - 1, $remate->fresh()->porciones);
        $this->assertSame($stockMenu, $remate->menu->fresh()->porciones);

        // Hora fuera del rango del remate (15:00 a 17:00)
        $this->actingAs($est)->post('/reservas', [
            'menu_id' => $remate->menu_id, 'remate_id' => $remate->id, 'cantidad' => 1, 'hora_recojo' => '10:00',
        ])->assertSessionHasErrors('hora_recojo');
    }

    public function test_local_gestiona_reservas_y_rechazar_libera_porciones(): void
    {
        $dueno = $this->usuario('rosa@compamenu.pe');
        $reserva = Reserva::where('codigo', 'DEMO01')->first();
        $menu = $reserva->menu;
        $antes = $menu->porciones;

        $this->actingAs($dueno)->get('/mi-local/reservas')->assertOk()->assertSee('DEMO01');

        // Saltarse un paso no se permite
        $this->actingAs($dueno)->patch("/mi-local/reservas/{$reserva->id}/estado", ['estado' => 'completada'])->assertSessionHas('error');

        $this->actingAs($dueno)->patch("/mi-local/reservas/{$reserva->id}/estado", ['estado' => 'confirmada'])->assertSessionHas('exito');
        $this->actingAs($dueno)->patch("/mi-local/reservas/{$reserva->id}/estado", ['estado' => 'completada'])->assertSessionHas('exito');
        $this->assertSame('completada', $reserva->fresh()->estado);
        $this->assertSame($antes, $menu->fresh()->porciones);

        // Rechazar una reserva pendiente devuelve las porciones
        $est = $this->usuario('estudiante@compamenu.pe');
        $this->actingAs($est)->post('/reservas', ['menu_id' => $menu->id, 'cantidad' => 2, 'hora_recojo' => '12:00']);
        $nueva = Reserva::where('menu_id', $menu->id)->where('estado', 'pendiente')->first();
        $this->assertSame($antes - 2, $menu->fresh()->porciones);
        $this->actingAs($dueno)->patch("/mi-local/reservas/{$nueva->id}/estado", ['estado' => 'rechazada']);
        $this->assertSame($antes, $menu->fresh()->porciones);

        // Otro local no puede tocar esta reserva
        $otro = $this->usuario('inti@compamenu.pe');
        $this->actingAs($otro)->patch("/mi-local/reservas/{$reserva->id}/estado", ['estado' => 'confirmada'])->assertNotFound();
    }

    // ---------- Panel del establecimiento: CRUD de menús ----------

    public function test_crud_de_menus_con_platos_y_foto(): void
    {
        $dueno = $this->usuario('verde@compamenu.pe');
        $foto = UploadedFile::fake()->image('plato.jpg', 800, 600);

        $this->actingAs($dueno)->post('/mi-local/menus', [
            'nombre' => 'Rocoto relleno', 'precio' => 8.5, 'tipo' => 'andino', 'porciones' => 12, 'disponible' => 1,
            'hora_inicio' => '12:00', 'hora_fin' => '15:00', 'foto' => $foto,
            'platos' => [
                ['nombre' => 'Rocoto relleno', 'categoria' => 'segundo'],
                ['nombre' => '', 'categoria' => 'entrada'], // fila vacía: se ignora
                ['nombre' => 'Mazamorra', 'categoria' => 'postre'],
            ],
        ])->assertRedirect(route('panel.menus.index'))->assertSessionHasNoErrors();

        $menu = Menu::where('nombre', 'Rocoto relleno')->first();
        $this->assertSame($dueno->local->id, $menu->local_id);
        $this->assertCount(2, $menu->platos);
        $this->assertStringStartsWith('uploads/menus/', $menu->foto);
        $this->assertFileExists(public_path($menu->foto));
        $this->actingAs($dueno)->get('/mi-local/menus')->assertSee('Rocoto relleno');
        $this->actingAs($dueno)->get("/mi-local/menus/{$menu->id}/editar")->assertOk()->assertSee('Editar menú')->assertSee('Mazamorra');

        // Actualizar (y reemplazar la foto)
        $fotoVieja = $menu->foto;
        $this->actingAs($dueno)->put("/mi-local/menus/{$menu->id}", [
            'nombre' => 'Rocoto relleno especial', 'precio' => 9, 'tipo' => 'andino', 'porciones' => 5,
            'foto' => UploadedFile::fake()->image('nueva.png'),
            'platos' => [['nombre' => 'Rocoto con pastel de papa', 'categoria' => 'segundo']],
        ])->assertRedirect(route('panel.menus.index'));
        $menu->refresh();
        $this->assertSame('Rocoto relleno especial', $menu->nombre);
        $this->assertFalse((bool) $menu->disponible); // el checkbox no se marcó
        $this->assertCount(1, $menu->platos);
        $this->assertFileDoesNotExist(public_path($fotoVieja));
        $this->assertFileExists(public_path($menu->foto));

        // Alternar disponibilidad y eliminar
        $this->actingAs($dueno)->patch("/mi-local/menus/{$menu->id}/disponibilidad");
        $this->assertTrue($menu->fresh()->disponible);
        $foto = $menu->foto;
        $this->actingAs($dueno)->delete("/mi-local/menus/{$menu->id}")->assertRedirect(route('panel.menus.index'));
        $this->assertModelMissing($menu);
        $this->assertFileDoesNotExist(public_path($foto));
    }

    public function test_validaciones_del_menu(): void
    {
        $dueno = $this->usuario('verde@compamenu.pe');
        $this->actingAs($dueno)->post('/mi-local/menus', ['nombre' => 'ab', 'precio' => 99, 'tipo' => 'raro', 'porciones' => -1])
            ->assertSessionHasErrors(['nombre', 'precio', 'tipo', 'porciones']);
        $this->actingAs($dueno)->post('/mi-local/menus', [
            'nombre' => 'Menú con archivo malo', 'precio' => 7, 'tipo' => 'criollo', 'porciones' => 3,
            'foto' => UploadedFile::fake()->create('virus.php', 10, 'application/x-php'),
        ])->assertSessionHasErrors('foto');
        $this->actingAs($dueno)->post('/mi-local/menus', [
            'nombre' => 'Horario malo', 'precio' => 7, 'tipo' => 'criollo', 'porciones' => 3, 'hora_inicio' => '15:00', 'hora_fin' => '12:00',
        ])->assertSessionHasErrors('hora_fin');
    }

    public function test_un_local_no_puede_tocar_menus_de_otro(): void
    {
        $dueno = $this->usuario('verde@compamenu.pe');
        $ajeno = Menu::where('nombre', 'like', 'Lomo saltado%')->first();

        $this->actingAs($dueno)->get("/mi-local/menus/{$ajeno->id}/editar")->assertNotFound();
        $this->actingAs($dueno)->put("/mi-local/menus/{$ajeno->id}", ['nombre' => 'Hackeado', 'precio' => 1, 'tipo' => 'criollo', 'porciones' => 1])->assertNotFound();
        $this->actingAs($dueno)->delete("/mi-local/menus/{$ajeno->id}")->assertNotFound();
        $this->actingAs($dueno)->patch("/mi-local/menus/{$ajeno->id}/disponibilidad")->assertNotFound();
        $this->assertSame('Lomo saltado con papas fritas', $ajeno->fresh()->nombre);
    }

    public function test_menu_con_reservas_pendientes_no_se_elimina(): void
    {
        $dueno = $this->usuario('rosa@compamenu.pe');
        $menu = Reserva::where('codigo', 'DEMO01')->first()->menu;
        $this->actingAs($dueno)->delete("/mi-local/menus/{$menu->id}")->assertSessionHas('error');
        $this->assertModelExists($menu);
    }

    public function test_crud_de_remates(): void
    {
        $dueno = $this->usuario('juana@compamenu.pe');
        $menu = $dueno->local->menus()->first();

        // El precio de remate debe ser menor al normal
        $this->actingAs($dueno)->post('/mi-local/remates', [
            'menu_id' => $menu->id, 'precio_remate' => 9, 'porciones' => 3, 'hora_inicio' => '15:00',
        ])->assertSessionHasErrors('precio_remate');

        // No se puede rematar el menú de otro local
        $ajeno = Menu::where('nombre', 'like', 'Lomo saltado%')->first();
        $this->actingAs($dueno)->post('/mi-local/remates', [
            'menu_id' => $ajeno->id, 'precio_remate' => 3, 'porciones' => 3, 'hora_inicio' => '15:00',
        ])->assertSessionHasErrors('menu_id');

        $this->actingAs($dueno)->post('/mi-local/remates', [
            'menu_id' => $menu->id, 'precio_remate' => 4, 'porciones' => 3, 'hora_inicio' => '15:00', 'hora_fin' => '17:00', 'activo' => 1,
        ])->assertRedirect(route('panel.remates.index'));
        $remate = Remate::where('menu_id', $menu->id)->first();
        $this->get('/remates')->assertSee('Quedan 3 porciones');

        $this->actingAs($dueno)->get("/mi-local/remates/{$remate->id}/editar")->assertOk();
        $this->actingAs($dueno)->put("/mi-local/remates/{$remate->id}", [
            'menu_id' => $menu->id, 'precio_remate' => 4.5, 'porciones' => 2, 'hora_inicio' => '15:00', 'activo' => 0,
        ])->assertRedirect();
        $this->assertFalse($remate->fresh()->activo);
        $this->get('/remates')->assertDontSee('Kiosko Mama Juana');

        $this->actingAs($dueno)->delete("/mi-local/remates/{$remate->id}");
        $this->assertModelMissing($remate);
    }

    public function test_datos_del_local(): void
    {
        $dueno = $this->usuario('rosa@compamenu.pe');
        $this->actingAs($dueno)->get('/mi-local/datos')->assertOk()->assertSee('Pensión Doña Rosa');
        $this->actingAs($dueno)->put('/mi-local/datos', [
            'nombre' => 'Pensión Rosa Nueva', 'tipo_local' => 'pension', 'distancia_campus' => 90, 'horario' => '11 a 3',
        ])->assertRedirect(route('panel.local.edit'));
        $this->assertSame('Pensión Rosa Nueva', $dueno->local->fresh()->nombre);
        $this->actingAs($dueno)->put('/mi-local/datos', ['nombre' => '', 'tipo_local' => 'x', 'distancia_campus' => 'abc'])
            ->assertSessionHasErrors(['nombre', 'tipo_local', 'distancia_campus']);
    }

    // ---------- Reseñas ----------

    public function test_resenas_solo_estudiantes_una_por_local_y_se_actualiza(): void
    {
        $local = Local::where('nombre', 'Verde Sabor')->first();
        $datos = ['local_id' => $local->id, 'puntaje' => 5, 'comentario' => 'Excelente atención y sabor', 'calidad' => 5];

        $this->post('/resenas', $datos)->assertRedirect(route('login'));
        $this->actingAs($this->usuario('inti@compamenu.pe'))->post('/resenas', $datos)->assertForbidden();

        $est = $this->usuario('estudiante@compamenu.pe');
        $this->actingAs($est)->post('/resenas', $datos)->assertRedirect();
        $this->actingAs($est)->post('/resenas', ['puntaje' => 2] + $datos)->assertRedirect();
        $this->assertSame(1, Valoracion::where('local_id', $local->id)->where('user_id', $est->id)->count());
        $this->assertSame(2, Valoracion::where('user_id', $est->id)->first()->puntaje);

        $this->actingAs($est)->post('/resenas', ['puntaje' => 9, 'comentario' => 'hola'] + $datos)->assertSessionHasErrors(['puntaje', 'comentario']);
        $this->actingAs($est)->get("/resenas?local={$local->id}")->assertOk()->assertSee('Edita tu reseña');
    }

    public function test_borrar_resenas_solo_autor_o_admin(): void
    {
        $v = Valoracion::first();
        $otro = $this->usuario('marco@compamenu.pe');
        $autor = $v->user;
        $admin = $this->usuario('admin@compamenu.pe');
        if ($otro->id === $autor->id) {
            $otro = $this->usuario('andrea@compamenu.pe');
        }

        $this->actingAs($otro)->delete("/resenas/{$v->id}")->assertForbidden();
        $this->actingAs($autor)->delete("/resenas/{$v->id}")->assertSessionHas('exito');
        $this->assertModelMissing($v);

        $w = Valoracion::first();
        $this->actingAs($admin)->delete("/resenas/{$w->id}")->assertSessionHas('exito');
        $this->assertModelMissing($w);
    }

    // ---------- Administración ----------

    public function test_admin_modera_y_gestiona(): void
    {
        $admin = $this->usuario('admin@compamenu.pe');
        $est = $this->usuario('marco@compamenu.pe');
        $v = Valoracion::first();

        $this->actingAs($admin)->patch("/admin/valoraciones/{$v->id}/alternar");
        $this->assertFalse($v->fresh()->visible);
        $this->get('/resenas')->assertDontSee($v->comentario);

        $this->actingAs($admin)->patch("/admin/usuarios/{$est->id}/alternar");
        $this->assertFalse($est->fresh()->activo);
        $this->actingAs($admin)->patch("/admin/usuarios/{$admin->id}/alternar")->assertSessionHas('error');
        $this->actingAs($admin)->delete("/admin/usuarios/{$admin->id}")->assertSessionHas('error');

        $local = Local::first();
        $this->actingAs($admin)->patch("/admin/locales/{$local->id}/alternar");
        $this->get("/locales/{$local->id}")->assertNotFound();

        $this->actingAs($admin)->get('/admin/usuarios?rol=estudiante&q=lucia')->assertOk()->assertSee('Lucía');
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Estudiantes registrados');
    }

    public function test_pagina_inexistente_muestra_404_andino(): void
    {
        $this->get('/no-existe')->assertNotFound()->assertSee('No encontramos esa página');
    }
}
