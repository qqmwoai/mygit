<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
} 
class Ewei_DShop_Excel {
	protected function column_str($key) {
		$array = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ');
		return $array[$key];
	} 
	protected function column($key, $columnnum = 1) {
		return $this -> column_str($key) . $columnnum;
	} 
	function export($list, $params = array()) {
		if (PHP_SAPI == 'cli') {
			die('This example should only be run from a Web Browser');
		} 
		require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel.php';
		$excel = new PHPExcel();
		$excel -> getProperties() -> setCreator("人人商城") -> setLastModifiedBy("人人商城") -> setTitle("Office 2007 XLSX Test Document") -> setSubject("Office 2007 XLSX Test Document") -> setDescription("Test document for Office 2007 XLSX, generated using PHP classes.") -> setKeywords("office 2007 openxml php") -> setCategory("report file");
		$sheet = $excel -> setActiveSheetIndex(0);
		$rownum = 1;
		foreach ($params['columns'] as $key => $column) {
			$sheet -> setCellValue($this -> column($key, $rownum), $column['title']);
			if (!empty($column['width'])) {
				$sheet -> getColumnDimension($this -> column_str($key)) -> setWidth($column['width']);
			} 
		} 
		$rownum++;
		foreach ($list as $row) {
			$len = count($row);
			for ($i = 0;$i < $len;$i++) {
				$value = $row[$params['columns'][$i]['field']];
				$sheet -> setCellValue($this -> column($i, $rownum), $value);
			} 
			$rownum++;
		} 
		$excel -> getActiveSheet() -> setTitle($params['title']);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $params['title'] . '-' . date('Y-m-d H:i', time()) . '.xlsx"');
		header('Cache-Control: max-age=0');
		$writer = PHPExcel_IOFactory :: createWriter($excel, 'Excel2007');
		$writer -> save('php://output');
		exit;
	} 
} 
