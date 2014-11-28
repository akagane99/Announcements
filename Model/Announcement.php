<?php
/**
 * Announcement Model
 *
 * @property Block $Block
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AnnouncementsAppModel', 'Announcements.Model');

/**
 * Announcement Model
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Announcements\Model
 */
class Announcement extends AnnouncementsAppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array();

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Block' => array(
			'className' => 'Blocks.Block',
			'foreignKey' => 'block_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'CreatedUser' => array(
			'className' => 'Users.UserAttributesUser',
			'foreignKey' => false,
			'conditions' => array(
				'Announcement.created_user = CreatedUser.user_id',
				'CreatedUser.key' => 'nickname'
			),
			'fields' => array('CreatedUser.key', 'CreatedUser.value'),
			'order' => ''
		)
	);

/**
 * Called during validation operations, before validation. Please note that custom
 * validation rules can be defined in $validate.
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforevalidate
 * @see Model::save()
 */
	public function beforeValidate($options = array()) {
		$this->validate = array(
			'block_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
					'allowEmpty' => false,
					'required' => true,
				)
			),
			'key' => array(
				'notEmpty' => array(
					'rule' => array('notEmpty'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				)
			),
			'status' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
					'allowEmpty' => false,
					'required' => true,
				),
				'inList' => array(
					'rule' => array('inList', NetCommonsBlockComponent::$STATUSES),
					'message' => __d('net_commons', 'Invalid request.'),
				)
			),
			'is_auto_translated' => array(
				'boolean' => array(
					'rule' => array('boolean'),
					'message' => __d('net_commons', 'Invalid request.'),
				)
			),
			'content' => array(
				'notEmpty' => array(
					'rule' => array('notEmpty'),
					'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('announcements', 'Content')),
					'required' => true,
				)
			),
		);

		return parent::beforeValidate($options);
	}

/**
 * before save
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if the operation should continue, false if it should abort
 */
	public function beforeSave($options = array()) {
		if (! isset($this->data[$this->name]['id'])) {
			$this->data[$this->name]['created_user'] = CakeSession::read('Auth.User.id');
		}
		$this->data[$this->name]['modified_user'] = CakeSession::read('Auth.User.id');
		return true;
	}

/**
 * get content data
 *
 * @param int $blockId blocks.id
 * @param bool $contentEditable true can edit the content, false not can edit the content.
 * @return array
 */
	public function getAnnouncement($blockId, $contentEditable) {
		$conditions = array(
			'block_id' => $blockId,
		);
		if (! $contentEditable) {
			$conditions['status'] = NetCommonsBlockComponent::STATUS_PUBLISHED;
		}

		$announcement = $this->find('first', array(
				'conditions' => $conditions,
				'order' => 'Announcement.id DESC',
			)
		);

		if ($contentEditable && ! $announcement) {
			$announcement = $this->create();
			$announcement['Announcement']['content'] = '';
			$announcement['Announcement']['key'] = '';
			$announcement['Announcement']['id'] = '0';
		}

		return $announcement;
	}

/**
 * save announcement
 *
 * @param array $data received post data
 * @return bool true success, false error
 * @throws InternalErrorException
 */
	public function saveAnnouncement($data) {
		//DBへの登録
		$this->setDataSource('master');
		$models = array(
			'Block' => 'Blocks.Block',
			'Comment' => 'Comments.Comment',
		);
		foreach ($models as $model => $class) {
			$this->$model = ClassRegistry::init($class);
			$this->$model->setDataSource('master');
		}

		$dataSource = $this->getDataSource();
		$dataSource->begin();

		//TODO: validationを実行

		try {
			//ブロックの登録
			$block = $this->Block->saveByFrameId($data['Frame']['id']);

			//お知らせの登録
			$announcement = $this->__saveAnnouncement($block, $data);
			if (! $announcement) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//コメントの登録
			if (! $this->__saveComment($announcement, $data)) {
				$this->validationErrors = $this->Comment->validationErrors;
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$dataSource->commit();
			return $announcement;

		} catch (Exception $ex) {
			//ロールバック
			$dataSource->rollback();
			//エラー出力
			if ($this->validationErrors) {
				return false;
			} else {
				CakeLog::error($ex);
				throw $ex;
			}
		}
	}

/**
 * save announcement
 *
 * @param array $block block data
 * @param array $data received post data
 * @return mixed object announcement, false error
 */
	private function __saveAnnouncement($block, $data) {
		//お知らせデータの取得
		$announcement = $this->getAnnouncement((int)$data['Announcement']['block_id'], true);
		if ($announcement['Announcement']['key'] === '') {
			$data['Announcement']['key'] = Security::hash('annoncement_' . microtime());
			$data['Announcement']['block_id'] = (int)$block['Block']['id'];
		}

		//お知らせの登録
		if (!isset($data['Announcement']['content'])) {
			//定義されていない場合、Noticeが発生するため、nullで初期化
			$data['Announcement']['content'] = null;
		}
		if ($data['Announcement']['content'] !== $announcement['Announcement']['content'] ||
				$data['Announcement']['status'] !== $announcement['Announcement']['status']) {

			unset($data['Announcement']['id']);
			$announcement = $this->create();
		}
		$announcement['Announcement'] = $data['Announcement'];
		return $this->save($announcement);
	}

/**
 * save comment
 *
 * @param array $announcement announcement data
 * @param array $data received post data
 * @return bool true success, false error
 */
	private function __saveComment($announcement, $data) {
		//コメントの登録(ステータス 差し戻しのみコメント必須)
		if ($announcement['Announcement']['status'] === NetCommonsBlockComponent::STATUS_DISAPPROVED ||
				$data['Comment']['comment'] !== '') {

			$data['Comment']['content_key'] = $announcement['Announcement']['key'];
			return $this->Comment->save($data['Comment']);
		}

		return true;
	}

}
