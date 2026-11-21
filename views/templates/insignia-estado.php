<?php /** @var \Model\Pedido $pedido */ ?>
<?php $claseInsignia = $pedido->entregado() ? 'entregado' : $pedido->estado; ?>
<span class="insignia insignia--<?= s($claseInsignia) ?>"><?= s($pedido->etiquetaEstado()) ?></span>
