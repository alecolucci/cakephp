<h2>Recuperação de senha</h2>
<p>Insira a chave de ativação recebida via e-mail no formulário abaixo:</p>
<?php
	echo $this->Form->create('User', array('action' => 'reset', 'class' => ''));
	echo $this->Form->input('token', array('type' => 'text', 'class' => '');
	echo $this->Form->button('Enviar', array('type' => 'submit', 'class' => 'btn btn-primary'));
	echo $this->Form->end();
?>
<p>Ou, caso o erro persista, peça uma nova chave de ativação:</p>
<?php
	echo $this->Form->create('User', array('action' => 'recover', 'class' => ''));
	echo $this->Form->input('email');
	echo $this->Form->end('Enviar');
?>