<?php
App::uses('AppModel', 'Model');
/**
 * User Model
 *
 */
class User extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'email' => array(
			array(
				'rule' => 'notEmpty',
				'message' => 'E-mail cannot be empty'
			),
			array(
				'rule' => 'isUnique',
				'message' => 'This e-mail is already taken'
			),
			array(
				'rule' => 'email',
				'message' => 'This e-mail is invalid'
			)
		),
		'password' => array(
			array(
				'rule' => 'notEmpty',
				'message' => 'Password cannot be empty'
			),
			array(
				'rule' => array('minLength', 4),
				'message' => 'Must be at least 4 chars' 
			)
		)
	);

/*
 * Encrypt the user's password with default hashing algorithm
 *
 * @return bool
 */
	public function beforeSave() {
		$this->data['User']['password'] = AuthComponent::password($this->data['User']['password']);
		return true;
	}

}
