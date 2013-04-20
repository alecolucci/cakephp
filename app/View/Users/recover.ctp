<h2>Recuperação de senha</h2>
<p>Preencha o seu e-mail no campo abaixo, para que uma nova senha seja gerada para você:</p>
<?php
	echo $this->Form->create();
	echo $this->Form->input('email');
	echo $this->Form->end('Enviar');
?>