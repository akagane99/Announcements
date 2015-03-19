<?php
/**
 * Announcement Model Test Case
 *
 * @property Announcement $Announcement
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AnnouncementAppModelTest', 'Announcements.Test/Case/Model');

/**
 *Announcement Model Test Case
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Announcements\Test\Case\Model
 */
class AnnouncementTest extends AnnouncementAppModelTest {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Announcement = ClassRegistry::init('Announcements.Announcement');
		$this->Comment = ClassRegistry::init('Comments.Comment');
	}

/**
 * testGetAnnouncement method
 *
 * @return void
 */
	public function testGetAnnouncement() {
		$frameId = 1;
		$blockId = 1;
		$contentEditable = true;
		$result = $this->Announcement->getAnnouncement($frameId, $blockId, $contentEditable);

		$expected = array(
			'Announcement' => array(
				'id' => '2',
				'block_id' => $blockId,
				'status' => NetCommonsBlockComponent::STATUS_IN_DRAFT,
				'key' => 'announcement_1',
			),
		);

		$this->_assertArray(null, $expected, $result);
	}

/**
 * testGetAnnouncementByNoEditable method
 *
 * @return void
 */
	public function testGetAnnouncementByNoEditable() {
		$frameId = 1;
		$blockId = 1;
		$contentEditable = false;
		$result = $this->Announcement->getAnnouncement($frameId, $blockId, $contentEditable);

		$expected = array(
			'Announcement' => array(
				'id' => '1',
				'block_id' => $blockId,
				'key' => 'announcement_1',
				'status' => NetCommonsBlockComponent::STATUS_PUBLISHED,
			),
		);

		$this->_assertArray(null, $expected, $result);
	}

/**
 * Expect Announcement->saveAnnouncement() saves data w/ numeric block_id
 *
 * @return void
 */
	public function testSaveAnnouncement() {
		$frameId = 1;
		$blockId = 1;

		$data = array(
			'Announcement' => array(
				'block_id' => $blockId,
				'key' => 'announcement_1',
				'status' => NetCommonsBlockComponent::STATUS_IN_DRAFT,
				'content' => 'edit content',
				'is_auto_translated' => true,
				'translation_engine' => 'edit translation_engine',
			),
			'Frame' => array(
				'id' => $frameId
			),
			'Comment' => array(
				'comment' => 'edit comment',
			),
		);
		$this->Announcement->saveAnnouncement($data, false);

		$result = $this->Announcement->getAnnouncement($frameId, $blockId, true);

		$expected = array(
			'Announcement' => array(
				'id' => '4',
				'block_id' => $blockId,
				'key' => 'announcement_1',
			),
		);

		$this->_assertArray(null, $expected, $result);
	}

/**
 * Expect Announcement->saveAnnouncement() saves data w/ null block_id
 *
 * @return void
 */
	public function testSaveAnnouncementByNoBlockId() {
		$frameId = 3;
		$blockId = null;

		$data = array(
			'Announcement' => array(
				'block_id' => $blockId,
				'status' => NetCommonsBlockComponent::STATUS_IN_DRAFT,
				'content' => 'add content',
				'is_auto_translated' => true,
				'translation_engine' => 'add translation_engine',
				'key' => 'announcement_1',
			),
			'Frame' => array(
				'id' => $frameId
			),
			'Comment' => array(
				'comment' => 'add comment',
			)
		);
		$this->Announcement->saveAnnouncement($data, false);

		$blockId = 3;
		$result = $this->Announcement->getAnnouncement($frameId, $blockId, true);

		$expected = array(
			'Announcement' => array(
				'id' => '4',
				'block_id' => $blockId,
			),
		);

		$this->_assertArray(null, $expected, $result);
	}

/**
 * testSaveAnnouncement method
 *
 * @return void
 */
	public function testSaveAnnouncementStatusModify() {
		$frameId = 1;
		$blockId = 1;

		$data = array(
			'Announcement' => array(
				'block_id' => $blockId,
				'key' => 'announcement_1',
				'status' => NetCommonsBlockComponent::STATUS_APPROVED,
				'content' => 'Lorem ipsum dolor sit amet, aliquet feugiat. ' .
							'Convallis morbi fringilla gravida, ' .
							'phasellus feugiat dapibus velit nunc, ' .
							'pulvinar eget sollicitudin venenatis cum nullam, ' .
							'vivamus ut a sed, mollitia lectus. ' .
							'Nulla vestibulum massa neque ut et, id hendrerit sit, ' .
							'feugiat in taciti enim proin nibh, ' .
							'tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
				'is_auto_translated' => true,
				'translation_engine' => 'edit translation_engine',
			),
			'Frame' => array(
				'id' => $frameId
			),
			'Comment' => array(
				'comment' => '',
			)
		);
		$this->Announcement->saveAnnouncement($data);

		$result = $this->Announcement->getAnnouncement($frameId, $blockId, true);

		$expected = array(
			'Announcement' => array(
				'id' => '4',
				'block_id' => $blockId,
				'key' => 'announcement_1',
				'status' => NetCommonsBlockComponent::STATUS_APPROVED,
			)
		);

		$this->_assertArray(null, $expected, $result);
	}

}
