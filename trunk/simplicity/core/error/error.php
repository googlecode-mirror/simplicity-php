<?php
class Error extends Core {
	static public function displayExteption(Exception $e) {
		?>
		<body style='margin:30px'>
		<h1>Error</h1>
		<p><?=$e->getMessage()?> in file <?=$e->getFile()?> on <b>line <?=$e->getLine()?></b></p>
		
		<? if (file_exists($e->getFile())) { ?>
		<h2>Code</h2>
		
		<table>
		<?
			$code = file($e->getFile());
			$line = $e->getLine() - 1;
			$start = $line - 5;
			if ($start < 0) $start = 0;
			$cnt = 0;
			foreach ($code as $lne => $code) {
				if ($cnt > 10) break;
				if ($lne >= $start) {
					$background = "#efefef";
					if ($lne == $line) $background = "#fdd";
					?>
					<tr style='border-bottom:1px solid #000;background:<?=$background?>;'>
						<td style='width:30px;font-family:courier;'><?=($lne + 1)?></td>
						<td><pre style='display:inline;margin:0 20px 0 0;'><?=htmlentities(str_replace("\n","",$code))?></pre></td>
					</tr>
					<?				
					++$cnt;
				}
			}
		?>
		</table>
		
		<? } ?>
		
		<h2>Trace</h2>
		<? 
		$cnt = 0;
		foreach ($e->getTrace() as $trace) {
			if ($trace['function'] == 'exceptions_error_handler') continue;
			?>
			<div style='margin-bottom:20px;border-bottom:1px solid #ccc'>
				<p><b><?=$trace['file']?></b> on line <b><?=$trace['line']?></b></p>
				<p>
				<?
				if (!isset($trace['args']) || !is_array($trace['args'])) $trace['args'] = array();
				foreach ($trace['args'] as $k => $val) {
					if (!is_numeric($val) || !is_bool($val)) $trace['args'][$k] = "'{$val}'";
					if (is_bool($val)) $trace['args'][$k] = $val ? "true" : "false";
				}
				if (isset($trace['type']) && $trace['type']) {
					echo $trace['class'].$trace['type'].$trace['function'].'('.implode(',',$trace['args']).');';
				} else {
					echo $trace['function'].'('.implode(',',$trace['args']).');';
				}
				?>
				</p>
			</div>
			<?
			++$cnt;
		}
		die();			
	}
}
?>