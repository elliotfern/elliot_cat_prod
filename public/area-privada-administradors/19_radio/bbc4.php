<?php

/** @var App\Infrastructure\View\ViewModel $viewModel */
?>

<div id="barraNavegacioContenidor"></div>

<h1>Ràdio online</h1>
<h3>BBC Four</h3>

<div class="player-radio">
    <div id="logo" class="logo-radio"></div>
    <div id="programa"><em>Cargando programa...</em></div>
    <div id="descripcion"></div>

    <div id="horarios" style="font-size: 0.9em; color: #555; margin-top: 8px;"></div>

    <button id="btnActualizar" class="btn btn-outline-success">Actualizar info</button>

    <audio id="audio">
        Tu navegador no soporta el audio.
    </audio>

    <div class="controls-radio d-flex gap-2">
        <button class="btn btn-outline-primary rounded-circle">▶️</button>
        <button class="btn btn-outline-secondary rounded-circle">⏸️</button>
        <button class="btn btn-outline-danger rounded-circle">🔇</button>
        <button class="btn btn-outline-success rounded-circle">🔊</button>
        <button class="btn btn-outline-success rounded-circle">🔉</button>
    </div>
</div>