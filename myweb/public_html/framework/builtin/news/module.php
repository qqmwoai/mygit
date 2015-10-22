<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

class NewsModule extends WeModule {
	public $tablename = 'news_reply';
	public $replies = array();

	public function fieldsFormDisplay($rid = 0) {
		global $_W;
		load()->func('tpl');
		$replies = pdo_fetchall("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `displayorder` DESC", array(':rid' => $rid));
		foreach($replies as &$reply) {
			if(!empty($reply['thumb'])) {
				$reply['thumb'] = tomedia($reply['thumb']);
			}
		}
		include $this->template('display');
	}
	
	public function fieldsFormValidate($rid = 0) {
		global $_GPC, $_W;
		$this->replies = @json_decode(htmlspecialchars_decode($_GPC['replies']), true);
		if(empty($this->replies)) {
			return '必须填写有效的回复内容.';
		}
		$column = array('id', 'title', 'author', 'displayorder', 'thumb', 'description', 'content', 'url', 'incontent', 'createtime');
		foreach($this->replies as $k => &$v) {
			if(empty($v)) {
				unset($this->replies[$k]);
				continue;
			}
			if (trim($v['title']) == '') {
				return '必须填写有效的标题.';
			}
			if (trim($v['thumb']) == '') {
				return '必须填写有效的封面链接地址.';
			}
			$v['thumb'] = str_replace($_W['attachurl'], '', $v['thumb']);
			$v['content'] = htmlspecialchars_decode($v['content']);
			$v['createtime'] = TIMESTAMP;
			$v = array_elements($column, $v);
		}
		if(empty($this->replies)) {
			return '必须填写有效的回复内容.';
		}
		return '';
	}
	
	public function fieldsFormSubmit($rid = 0) {
		$sql = 'SELECT `id` FROM ' . tablename($this->tablename) . " WHERE `rid` = :rid";
		$replies = pdo_fetchall($sql, array(':rid' => $rid), 'id');
		$replyids = array_keys($replies);
		foreach($this->replies as $reply) {
			if (in_array($reply['id'], $replyids)) {
				pdo_update($this->tablename, $reply, array('id' => $reply['id']));
			} else {
				$reply['rid'] = $rid;
				pdo_insert($this->tablename, $reply);
			}
			unset($replies[$reply['id']]);
		}
		if (!empty($replies)) {
			$replies = array_keys($replies);
			$replies = implode(',', $replies);
			$sql = 'DELETE FROM '. tablename($this->tablename) . " WHERE `id` IN ({$replies})";
			pdo_query($sql);
		}
		return true;
	}
	
	public function ruleDeleted($rid = 0) {
		pdo_delete($this->tablename, array('rid' => $rid));
		return true;
	}
}