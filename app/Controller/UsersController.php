<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
/**
 * Users Controller
 *
 * @property User $User
 */
class UsersController extends AppController {

/**
 * admin_index method
 *
 * @return void
 */
	public function admin_index() {
		$this->User->recursive = 0;
		$this->set('users', $this->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->User->exists($id)) {
			throw new NotFoundException(__('Invalid user'));
		}
		$options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
		$this->set('user', $this->User->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->User->create();
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		}
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->User->exists($id)) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
			$this->request->data = $this->User->find('first', $options);
		}
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$this->request->onlyAllow('post', 'delete');
		if ($this->User->delete()) {
			$this->Session->setFlash(__('User deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('User was not deleted'));
		$this->redirect(array('action' => 'index'));
	}

/**
 * Authorization methods
 *
 * Login / logout functions and allowed actions
 */

	public function login() {
		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				if ($this->request->data['User']['persist'] == '1') {
					$cookie = array();
					$cookie['email'] = $this->data['User']['email'];
					$cookie['token'] = $this->data['User']['password'];
					$this->Cookie->write('Auth.User', $cookie, true, '+2 weeks');
				}
				$this->Session->setFlash('Você foi conectado com sucesso.');
				$this->redirect($this->Auth->redirect());
			} else {
				$this->Session->setFlash('Usuário ou senha incorretos. Por favor, tente novamente.');
			}
		} else {
			$user = $this->Auth->user();
			if (empty($user)) {
				$cookie = $this->Cookie->read('Auth.User');
				// debug($cookie);
				if (!is_null($cookie)) {
					$user = $this->User->find('first', array('conditions' => array('email' => $cookie['email'], 'password' => AuthComponent::password($cookie['token']))));
					if ($this->Auth->login($user['User'])) {
						$this->Session->delete('Message.auth');
						$this->redirect($this->Auth->redirect());
					} else {
						$this->Cookie->delete('Auth.User');
					}
				}
			} else {
				$this->redirect($this->Auth->redirect());
			}
		}
	}

	public function logout() {
		$this->Session->setFlash('Você foi desconectado com sucesso.');
		$this->Cookie->delete('Auth.User');
		$this->redirect($this->Auth->logout());
		$this->Session->destroy();
	}

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('recover', 'reset'));
	}

/**
 * Generate random string
 *
 * Create a random string to use as password at hauth storage
 *
 */

	private function generateRandomString($length = null) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}

/**
 * Recover password method
 *
 * Allows the user to email themselves a password redemption token
 *
 */
	public function recover($email = null) {
		if ($this->request->is('post')) {
			$email = $this->request->data['User']['email'];
			$Token = ClassRegistry::init('Token');
			$user = $this->User->findByEmail($email);
			// If user not found, throws an alert and redirect for add action
			if (empty($user)) {
				$this->Session->setFlash('E-mail não encontrado. Por favor, faça o cadastro.');
				// $this->redirect(array('action' => 'add'));
			} else {
				// Generate a new token to user
				$token = $Token->generate(array('User' => $user['User']));
				$this->set('user', $user);
				$this->set('token', $token);
				// Sends a confirmation email to user
				$email = new CakeEmail('default');
				$email->from(array('alexandrecolucci@gmail.com' => 'CakeEmail'))
					->template('recover')
					->emailFormat('html')
					->to($user['User']['email'])
					->subject('Recuperação de senha')
					->viewVars(compact('user', 'token'))
					->send();
				// Set the successful message to user
				$this->Session->setFlash('Um e-mail foi enviado para a sua conta, por favor, siga as instruções deste e-mail.');
			}
		}		
	}

/**
 * Reset password method
 *
 * Accepts a valid token and resets the users password
 *
 */

	public function reset($token = null) {
		if ($this->request->is('post')) {
			$token = $this->request->data['User']['token'];
		}
		// Inits Token model
		$Token = ClassRegistry::init('Token');
		// Recover token information
		$result = $Token->get($token);
		if ($result) {
			// Finds the user
			$user = $this->User->findByEmail($result['User']['email']);
			$this->User->id = $user['User']['id'];
			// Generate new password
			$password = $this->generateRandomString(10);
			$this->set('password', $password);
			// Update password
			$this->User->saveField('password', $password);
			$this->set('user', $user);
			// Sends a confirmation e-mail to user
			$email = new CakeEmail('default');
			$email->from(array('alexandrecolucci@gmail.com' => 'CakeEmail'))
				->template('reset')
				->emailFormat('html')
				->to($user['User']['email'])
				->subject('Sua senha foi alterada')
				->viewVars(compact('user', 'password'))
				->send();
			// Warning messages and redirection
			$this->Session->setFlash('Sua senha foi alterada com sucesso. Um e-mail foi enviado para a sua conta, por favor, siga as instruções deste e-mail.');
			$this->redirect(array('action' => 'login'));
		} else {
			$this->Session->setFlash('Chave de ativação inválida. A chave expirou, ou o link não foi copiado de seu cliente de e-mail corretamente.');
		}
	}

}



