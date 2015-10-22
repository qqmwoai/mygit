<?php
defined('IN_IA') or exit('Access Denied');
class eso_saleModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		checkauth();
		if(!$this->inContext) {
			$number = pdo_fetchcolumn( " SELECT COUNT(*) FROM ".tablename('mc_mapping_fans')." WHERE uniacid = '{$_W['uniacid']}' and follow = 1 ");
			$list = pdo_fetch("select * from ".tablename('eso_sale_share_history'). " WHERE from_user = '{$_W['openid']}' and uniacid = '{$_W['uniacid']}'");
			if (empty($list)) {
				$reply ="欢迎关注【".$_W['account']['name']."】\n你是第【".$number."】个会员";
				// $reply ="欢迎关注【".$_W['account']['name']."】\n你是第【854】个会员";
			}else {
				$shangji = pdo_fetch("select from_user from ".tablename('eso_sale_member'). " where id = '{$list['sharemid']}' and uniacid = '{$_W['uniacid']}'");
				$member = mc_fansinfo($shangji['from_user']);
				$reply ="欢迎关注【".$_W['account']['name']."】\n你是由【".$member['nickname']."】推荐的第【".$number."】个会员";
			}
		}
		return $this->respText($reply);
	}
}
?>