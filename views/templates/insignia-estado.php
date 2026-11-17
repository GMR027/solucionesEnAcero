<?php /** @var \Model\Pedido $pedido */ ?>
<span class="insignia insignia--<?= s($pedido->estado) ?>"><?= s($pedido->etiquetaEstado()) ?></span>
