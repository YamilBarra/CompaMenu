<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** La página de inicio responde bien aunque todavía no haya menús. */
    public function test_la_pagina_de_inicio_responde(): void
    {
        $this->get('/')->assertOk()->assertSee('CompaMenu');
    }
}
