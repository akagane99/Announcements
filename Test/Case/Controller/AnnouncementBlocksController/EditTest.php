<?php
/**
 * AnnouncementBlocksController Test Case
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('BlocksControllerEditTest', 'Blocks.TestSuite');

/**
 * AnnouncementBlocksController Test Case
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Announcements\Test\Case\Controller
 */
class AnnouncementBlocksControllerEditTest extends BlocksControllerEditTest {

/**
 * Plugin name
 *
 * @var array
 */
	public $plugin = 'announcements';

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.announcements.announcement',
		'plugin.announcements.block_setting_for_announcement',
		'plugin.workflow.workflow_comment',
	);

/**
 * テストDataの取得
 *
 * @param bool $isEdit 編集かどうか
 * @return array
 */
	private function __getData($isEdit) {
		$frameId = '6';

		if ($isEdit) {
			$blockId = '4';
			$blockKey = 'block_3';
			$anouncementId = '4';
			$anouncementKey = 'announcement_3';
		} else {
			$blockId = null;
			$blockKey = null;
			$anouncementId = null;
			$anouncementKey = null;
		}

		$data = array(
			'save_' . WorkflowComponent::STATUS_PUBLISHED => null,
			'Frame' => array(
				'id' => $frameId
			),
			'Block' => array(
				'id' => $blockId,
				'key' => $blockKey,
				'language_id' => '2',
				'room_id' => '2',
				'plugin_key' => $this->plugin,
				'public_type' => '1',
				'from' => null,
				'to' => null,
			),
			'Announcement' => array(
				'id' => $anouncementId,
				'key' => $anouncementKey,
				'block_id' => $blockId,
				'language_id' => '2',
				'status' => null,
				'content' => 'Announcement save test'
			),
			'WorkflowComment' => array(
				'comment' => 'WorkflowComment save test'
			),
		);

		return $data;
	}

/**
 * add()アクションDataProvider
 *
 * ### 戻り値
 *  - method: リクエストメソッド（get or post or put）
 *  - data: 登録データ
 *  - validationError: バリデーションエラー
 *
 * @return void
 */
	public function dataProviderAdd() {
		$data = $this->__getData(false);

		$results = array();
		$results[0] = array('method' => 'get');
		$results[1] = array('method' => 'put');
		$results[2] = array('method' => 'post', 'data' => $data, 'validationError' => false);
		$results[3] = array('method' => 'post', 'data' => $data,
			'validationError' => array(
				'field' => 'Announcement.content',
				'value' => '',
				'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('announcements', 'Content'))
			)
		);

		return $results;
	}

/**
 * edit()アクションDataProvider
 *
 * ### 戻り値
 *  - method: リクエストメソッド（get or post or put）
 *  - data: 登録データ
 *  - validationError: バリデーションエラー
 *
 * @return void
 */
	public function dataProviderEdit() {
		$data = $this->__getData(true);

		$results = array();
		$results[0] = array('method' => 'get');
		$results[1] = array('method' => 'post');
		$results[2] = array('method' => 'put', 'data' => $data, 'validationError' => false);
		$results[3] = array('method' => 'put', 'data' => $data,
			'validationError' => array(
				'field' => 'Announcement.content',
				'value' => '',
				'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('announcements', 'Content'))
			)
		);

		return $results;
	}

/**
 * delete()アクションDataProvider
 *
 * ### 戻り値
 *  - data 削除データ
 *
 * @return void
 */
	public function dataProviderDelete() {
		$blockId = '4';

		$data = array(
			'Block' => array(
				'id' => $blockId,
				'key' => 'block_3',
			),
			'Announcement' => array(
				'key' => 'announcement_3',
			),
		);

		return array(
			array('data' => $data)
		);
	}

}
