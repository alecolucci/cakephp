<?php
    echo $this->Form->create();
    echo $this->Form->input('email', array('type' => 'email', 'class' => ''));
	echo $this->Form->input('password', array('type' => 'password', 'class' => ''));
    echo $this->Form->input('persist', array('type' => 'checkbox', 'label' => false, 'after' => 'Mantenha-me conectado'));
    echo $this->Form->end('Submit');
?>