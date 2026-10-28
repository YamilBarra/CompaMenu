{{--
    Reseñas de estudiantes. Ruta: GET /resenas (ResenaController@index).
    Recibe: $resenas (paginadas), $locales (para el selector), $localId (filtro) y $miResena (la reseña del
    estudiante para ese local, si existe, para editarla). El formulario solo se muestra a estudiantes.
--}}
@extends('layouts.app')
@section('titulo', 'Reseñas')
@section('descripcion', 'Opiniones de estudiantes sobre porciones, higiene y atención')

@section('contenido')
<section class="encabezado-pagina">
  <div class="encabezado-interior">
    <h1>Lo que dicen los estudiantes</h1>
    <p>Opiniones sobre porciones, higiene y atención.</p>
  </div>
</section>

<section class="seccion">
  <div class="val-contenedor">
    <div>
      <div class="filtros-linea">
        <a class="chip {{ $localId === 0 ? 'activa' : '' }}" href="{{ route('resenas.index') }}">Todos los locales</a>
        @if ($localId > 0)
          <span class="chip activa">{{ $locales->firstWhere('id', $localId)?->nombre ?? 'Local' }}</span>
        @endif
      </div>

      <div class="lista-val" id="listaVal">
        @forelse ($resenas as $resena)
          <article class="resena">
            <div class="resena-cab">
              <strong><a href="{{ route('locales.show', $resena->local) }}">{{ $resena->local->nombre }}</a></strong>
              <span class="estrellas" role="img" aria-label="{{ $resena->puntaje }} de 5">{{ str_repeat('★', $resena->puntaje) }}{{ str_repeat('☆', 5 - $resena->puntaje) }}</span>
            </div>
            <p>{{ $resena->comentario }}</p>
            @php($aspectos = collect(\App\Models\Valoracion::ASPECTOS)->filter(fn ($n, $k) => $resena->{$k}))
            @if ($aspectos->isNotEmpty())
              <div class="resena-aspectos">
                @foreach ($aspectos as $clave => $nombre)
                  <span class="etiqueta">{{ $nombre }}: {{ $resena->{$clave} }}/5</span>
                @endforeach
              </div>
            @endif
            <p class="ayuda">{{ $resena->user->name }} · {{ $resena->created_at->diffForHumans() }}
              @if (auth()->user()?->esAdmin() || auth()->id() === $resena->user_id)
                <form method="POST" action="{{ route('resenas.destroy', $resena) }}" data-confirmar="Se retirará esta reseña.">
                  @csrf @method('DELETE')
                  <button class="btn btn-borrar btn-chico" type="submit">Eliminar</button>
                </form>
              @endif
            </p>
          </article>
        @empty
          <p class="vacio">Todavía no hay reseñas. ¡Sé el primero en opinar!</p>
        @endforelse
      </div>
      <div class="paginacion">{{ $resenas->links() }}</div>
    </div>

    <!-- Formulario: solo estudiantes con sesión iniciada -->
    <div id="formulario">
      @guest
        <div class="form-val">
          <h3>Deja tu reseña</h3>
          <p class="mt-2">Para opinar necesitas una cuenta de estudiante. Así evitamos reseñas falsas.</p>
          <a class="btn btn-principal" href="{{ route('login') }}">Ingresar</a>
          <p class="auth-pie"><a href="{{ route('registro') }}">Crear cuenta de estudiante</a></p>
        </div>
      @elseif (auth()->user()->esEstudiante())
        <form class="form-val" method="POST" action="{{ route('resenas.store') }}" novalidate>
          @csrf
          <h3>{{ $miResena ? 'Edita tu reseña' : 'Deja tu reseña' }}</h3>
          <x-selector name="local_id" label="Local" :opciones="$locales->pluck('nombre', 'id')" :value="$localId ?: null" vacio="Elige un local" />
          <x-selector name="puntaje" label="Puntaje" :opciones="[5 => '5 estrellas', 4 => '4 estrellas', 3 => '3 estrellas', 2 => '2 estrellas', 1 => '1 estrella']" :value="$miResena?->puntaje ?? 5" />
          <x-area name="comentario" label="Comentario" rows="3" maxlength="150" :value="$miResena?->comentario" required />
          <p class="contador"><span id="contadorComentario">0</span>/150</p>
          <details class="mt-2">
            <summary>Calificar aspectos (opcional)</summary>
            <div class="aspectos-grid">
              @foreach (\App\Models\Valoracion::ASPECTOS as $clave => $nombre)
                <x-selector :name="$clave" :label="$nombre" :opciones="[5 => '5', 4 => '4', 3 => '3', 2 => '2', 1 => '1']" :value="$miResena?->{$clave}" vacio="—" />
              @endforeach
            </div>
          </details>
          <button class="btn btn-principal" type="submit">{{ $miResena ? 'Guardar cambios' : 'Publicar reseña' }}</button>
          <p class="ayuda mt-2">Una reseña por local: si vuelves a opinar, se actualiza la anterior.</p>
        </form>
      @else
        <div class="form-val">
          <h3>Reseñas</h3>
          <p class="mt-2">Solo los estudiantes pueden publicar reseñas.</p>
        </div>
      @endguest
    </div>
  </div>
</section>
@endsection
