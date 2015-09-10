<?php
/**
 * AnnouncementBlocks Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AnnouncementsAppController', 'Announcements.Controller');

/**
 * AnnouncementBlocks Controller
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Announcements\Controller
 */
class AnnouncementBlocksController extends AnnouncementsAppController {

/**
 * layout
 *
 * @var array
 */
	public $layout = 'NetCommons.setting';

/**
 * use components
 *
 * @var array
 */
	public $components = array(
		'Workflow.Workflow',
		'NetCommons.Permission' => array(
			//アクセスの権限
			'allow' => array(
				'index,add,edit,delete' => 'block_editable',
			),
		),
		'Paginator',
	);

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = array(
		'Blocks.BlockForm',
		'Workflow.Workflow',
	);

/**
 * beforeFilter
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();

		//タブの設定
		$this->initTabs('block_index', 'block_settings');
	}

/**
 * index
 *
 * @return void
 */
	public function index() {
		$this->Paginator->settings = array(
			'Announcement' => array(
				'order' => array('Announcement.id' => 'desc'),
				'conditions' => $this->Announcement->getBlockConditions(array(
					'Announcement.is_latest' => true,
				)),
			)
		);

		$announcements = $this->Paginator->paginate('Announcement');
		if (! $announcements) {
			$this->view = 'Blocks.Blocks/not_found';
			return;
		}
		$this->set('announcements', $announcements);

		$this->request->data['Frame'] = Current::read('Frame');
	}

/**
 * add
 *
 * @return void
 */
	public function add() {
		$this->view = 'edit';

		$this->set('blockId', null);

		if ($this->request->isPost()) {
			//登録(POST)処理
			$data = $this->__parseRequestData();

			if ($this->Announcement->saveAnnouncement($data)) {
				$this->redirect(Current::backToIndexUrl('default_setting_action'));
			}
			$this->handleValidationError($this->Announcement->validationErrors);

		} else {
			//初期データセット
			$this->request->data = $this->Announcement->createAll();
			$this->request->data['Frame'] = Current::read('Frame');
		}
	}

/**
 * edit
 *
 * @return void
 */
	public function edit() {
		if ($this->request->isPut()) {
			$data = $this->__parseRequestData();
			unset($data['Announcement']['id']);

			if ($this->Announcement->saveAnnouncement($data)) {
				$this->redirect(Current::backToIndexUrl('default_setting_action'));
				return;
			}
			$this->handleValidationError($this->Announcement->validationErrors);

		} else {
			//初期データセット
			CurrentFrame::setBlock($this->request->params['pass'][1]);

			$this->request->data = $this->Announcement->getAnnouncement();
			$this->request->data['Frame'] = Current::read('Frame');
		}

		$comments = $this->Announcement->getCommentsByContentKey($this->request->data['Announcement']['key']);
		$this->set('comments', $comments);
	}

/**
 * delete
 *
 * @throws BadRequestException
 * @return void
 */
	public function delete() {
		if ($this->request->isDelete()) {
			if ($this->Announcement->deleteAnnouncement($this->data)) {
				$this->redirect(Current::backToIndexUrl('default_setting_action'));
			}
		}

		$this->throwBadRequest();
	}

/**
 * Parse data from request
 *
 * @return array
 */
	private function __parseRequestData() {
		$data = $this->data;
		if (! $status = $this->Workflow->parseStatus()) {
			return;
		}
		$data['Announcement']['status'] = $status;

		return $data;
	}

}
