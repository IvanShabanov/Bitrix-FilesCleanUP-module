<?
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

if (!$USER->IsAdmin()) {
	return;
}

if (file_exists(__DIR__ . "/install/module.cfg.php")) {
	include(__DIR__ . "/install/module.cfg.php");
};

if (!Loader::includeModule($arModuleCfg['MODULE_ID'])) {
	return;
}

Loc::loadMessages(__FILE__);


function FileListinfile($directory, $outputfile) {
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
			if (is_file($directory.$file)) {
				file_put_contents($outputfile, $directory.$file."\n", FILE_APPEND);
			} elseif ($file != '.' and $file != '..' and is_dir($directory.$file)) {
				FileListinfile($directory.$file.'/', $outputfile);
			}
		}
	}
	closedir($handle);
}

function CreateDir($path,  $lastIsFile = false) {
	$dirs = explode('/', $path);
	if ($lastIsFile) {
		unset($dirs[count($dirs) - 1]);
	}
	$resultdir = '';
	foreach ($dirs as $dir) {
		$resultdir .= $dir;
		if ($dir != '') {
			@mkdir($resultdir);
		};
		$resultdir .= '/';
	}
}



$currentUrl = $APPLICATION->GetCurPage() . '?mid=' . urlencode($mid) . '&amp;lang=' . LANGUAGE_ID;
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$doc_root = \Bitrix\Main\Application::getDocumentRoot();
$url_module = str_replace($doc_root, '', __DIR__);
$limitPerPage = 20;
$work_file = $doc_root.'/upload/'.$arModuleCfg['MODULE_ID'].'/filelist.txt';
$uploadPath = $doc_root.'/upload/';
$basketPath = $doc_root.'/upload/'.$arModuleCfg['MODULE_ID'].'/basket/';
CreateDir($basketPath);

$tabList = array(
	array(
		'DIV' => 'edit1',
		'TAB' => Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_MAIN_TAB_SET_1'),
		'ICON' => 'ib_settings',
		'TITLE' => Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_MAIN_TAB_TITLE_SET_1')
	),

	array(
		'DIV' => 'edit_badlinks',
		'TAB' => Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_MAIN_TAB_SET_BADLINKS'),
		'ICON' => 'ib_settings',
		'TITLE' => Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_MAIN_TAB_TITLE_SET_BADLINKS')
	),

	array(
		'DIV' => 'edit_notusedfiles',
		'TAB' => Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_MAIN_TAB_SET_NOTUSEDFILES'),
		'ICON' => 'ib_settings',
		'TITLE' => Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_MAIN_TAB_TITLE_SET_NOTUSEDFILES')
	),

	array(
		'DIV' => 'edit_basket',
		'TAB' => Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_MAIN_TAB_SET_BASKET'),
		'ICON' => 'ib_settings',
		'TITLE' => Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_MAIN_TAB_TITLE_SET_BASKET')
	)
);


$tabControl = new CAdminTabControl(str_replace('.', '_', $arModuleCfg['MODULE_ID']) . '_options', $tabList);
?>
<style>
.hiddenPre pre {
	max-height: 0px;
	overflow: hidden;
	transition: all ease 0.5s;
	margin: 0;
}
.hiddenPre:hover pre {
	max-height: 1000px;
	margin: 10px 0px;
}
</style>
<script>
	function ispro_set_checkboxs(selector, checked) {
		let checkboxs = document.querySelectorAll(selector);
		checkboxs.forEach(element => {
			element.checked = checked;
		});
	}
</script>
<form method="POST" action="<?= $currentUrl; ?>"  enctype="multipart/form-data">
	<?= bitrix_sessid_post(); ?>
<?
$tabControl->Begin();
?>

	<?
	$tabControl->BeginNextTab();
	?>
	<?=BeginNote();?>
		<?= Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_NOTE', array('#MODULE_PATH#'=>$url_cur)); ?>
	<?=EndNote();?>

	<?
	$tabControl->BeginNextTab();
	?>

	<?if ($request->getPost('scanbadlinks') != '') :?>

		<tr>
			<th style="text-align: left" width="80%"  valign="top">
				<?=Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_FILE_INFO')?>
			</th>
			<th width="20%">
				<?=Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_DELETE')?>
				<br>
				<input type="checkbox" onclick="ispro_set_checkboxs('input[name^=deletefile_]', this.checked);" >
			</th>
		</tr>

		<?

		$filterFiles['MODULE_ID'] = 'iblock';

		$oRes = \Bitrix\Main\FileTable::getList([
			'order' => ['ID' => 'ASC'],
			'filter' => $filterFiles,
		]);
		$count = 0;
		while ($arFile = $oRes->fetch()) {
			$filelink = '/upload/' . $arFile['SUBDIR'] . '/' . $arFile['FILE_NAME'];
			$sPath = $doc_root . $filelink;
			if (!file_exists($sPath)) {
				if 	($request->getPost('deletefile_'.$arFile['ID']) == $arFile['ID']) {
					/*
					\Bitrix\Main\FileTable::delete($arFile['ID']);
					*/
					\CFile::Delete($arFile['ID']);
				} else {
					$count ++;
					$lastid = $arFile['ID'];
				?>
				<tr style="">
					<td style="text-align: left; border-top: 1px solid #aaa;" valign="top">
						<a href="<?=$filelink?>" target="_blank"><?=$filelink?></a>
						<span class="hiddenPre">Подробнее...
							<pre><?print_r($arFile)?></pre>
						</span>
					</td>
					<td style="text-align: center; border-top: 1px solid #aaa;"  valign="top">
						<input type="checkbox" name="deletefile_<?=$arFile['ID']?>" value="<?=$arFile['ID']?>" title="Delete">
					</td>
				</tr>
				<?
				}
			}
		}?>
		<?if ($count == 0) :?>
			<tr>
				<td colspan="2">
					<?=BeginNote();?>
					<?=Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_EMPTY_BADLINKS');?>
					<?=EndNote();?>
				</td>
			</tr>
		<?else :?>
			<tr>
				<td colspan="2">
					<input type="submit" class="adm-btn-save" name="scanbadlinks" value="<? echo Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_DELETE'); ?>">
				</td>
			</tr>
		<?endif?>
	<?endif?>
	<tr>
		<td colspan="2">
			<input type="submit" class="adm-btn-save" name="scanbadlinks" value="<? echo Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_BTN_SCAN'); ?>">
		</td>
	</tr>
	<?
	$tabControl->BeginNextTab();
	?>
	<?if ($request->getPost('scannotusedfiles') != '') {?>
		<tr>
			<td colspan="2">
				Loading...
			</td>
		</tr>
		<?
			$fileToDetete = $request->getPost('fileToDelete');
			if (is_array($fileToDetete)) {
				foreach ($fileToDetete as $fileToDel) {
					$basketName = str_replace($uploadPath, $basketPath, $fileToDel);
					CreateDir($basketName, true);
					@copy($fileToDel, $basketName);
					@unlink($fileToDel);
				}
			}
			@unlink($work_file);
			FileListinfile($uploadPath.'iblock/', $work_file);
		?>
		<script>
			BX.ready(function(){
				is_pro_filescleanup_options.SelectTab('edit_notusedfiles');
			});
			location = '<?= $currentUrl; ?>&scannotusedfiles=scanfromiblock';
		</script>
	<?} else if ($request->getQuery('scannotusedfiles') == 'scanfromiblock') {?>
		<tr>
			<td colspan="2">
				Loading...
			</td>
		</tr>
		<?
		if (file_exists($work_file)) {
			$filelist = @file($work_file);
			@unlink($work_file);
			if (is_array($filelist)) {
				$oRes = \Bitrix\Main\FileTable::getList(['order' => ['ID' => 'ASC']]);
				while (($arFile = $oRes->fetch()) && ($count < $limitPerPage)) {
					$filesInIbloks[] = $doc_root.'/upload/' . $arFile['SUBDIR'] . '/' . $arFile['FILE_NAME'];
				};
				foreach ($filelist as $file) {
					if (!in_array(trim($file), $filesInIbloks)) {
						file_put_contents($work_file, trim($file)."\n", FILE_APPEND);
					};
				};
			};
		};
		?>
		<script>
			BX.ready(function(){
				is_pro_filescleanup_options.SelectTab('edit_notusedfiles');
			});
			location = '<?= $currentUrl; ?>&scannotusedfiles=result';
		</script>

	<?} else if ($request->getQuery('scannotusedfiles') == 'result') {?>
		<script>
			BX.ready(function(){
				is_pro_filescleanup_options.SelectTab('edit_notusedfiles');
			});
		</script>
		<?
		$count = 0;
		if (file_exists($work_file)) {
			$filelist = @file($work_file);
			@unlink($work_file);
			if (is_array($filelist)) {
				?>

				<tr>
					<th style="text-align: left" width="80%"  valign="top">
						<?=Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_FILE_INFO')?>
					</th>
					<th width="20%">
						<?=Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_DELETE')?>
						<br>
						<input type="checkbox" onclick="ispro_set_checkboxs('input[name^=fileToDelete]', this.checked);" >
					</th>
				</tr>

				<?

				foreach ($filelist as $file) {
					$count ++;
					?>

					<tr>
						<td style="text-align: left; border-top: 1px solid #aaa;" valign="top">
							<?$filelink = str_replace($doc_root, '', trim($file));?>
							<a href="<?=$filelink?>" target="_blank"><?=$filelink?></a>
							<?if (in_array(mb_substr($filelink, -4), array('.jpg', 'jpeg', '.png', 'webp', '.gif', '.bmp'))) :?>
								<br>
								<img style="max-width: 200px; max-height: 100px" src="<?=$filelink?>">
							<?endif?>
						</td>
						<td style="text-align: center; border-top: 1px solid #aaa;" valign="top">
							<input type="checkbox" name="fileToDelete[]" value="<?=trim($file)?>" title="Delete">
						</td>
					</tr>

					<?
				}
				?>

				<tr>
					<td colspan="2">
						<input type="submit" class="adm-btn-save" name="scannotusedfiles" value="<? echo Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_DELETE'); ?>">
					</td>
				</tr>

				<?
			};?>

			<?
		};
		?>
		<?if ($count == 0) :?>
			<tr>
				<td colspan="2">
					<?=BeginNote();?>
					<?=Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_EMPTY_NOTUSEDFILES');?>
					<?=EndNote();?>
				</td>
			</tr>
		<?endif?>
	<?}?>

	<tr>
		<td colspan="2">
			<input type="submit" class="adm-btn-save" name="scannotusedfiles" value="<? echo Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_BTN_SCAN'); ?>">
		</td>
	</tr>
	<?
	$tabControl->BeginNextTab();
	?>
	<?if ($request->getPost('showbasket') != '') {?>
		<?

			$fileToRestore = $request->getPost('fileToRestore');
			if (is_array($fileToRestore)) {
				foreach ($fileToRestore as $fileToRest) {
					$originName = str_replace($basketPath, $uploadPath, $fileToRest);
					CreateDir($originName, true);
					@copy($fileToRest, $originName);
					@unlink($fileToRest);
				};
			};
			$fileToDeteteComplete = $request->getPost('fileToDelete');
			if (is_array($fileToDeteteComplete)) {
				foreach ($fileToDeteteComplete as $fileToDel) {
					@unlink($fileToDel);
				};
			};

			@unlink($work_file);
			FileListinfile($basketPath, $work_file);
		?>
		<?if (file_exists($work_file)) {
			$filelist = @file($work_file);
			@unlink($work_file);
			if (is_array($filelist)) {
				?>
				<tr>
					<th style="text-align: left" width="70%"  valign="top">
						<?=Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_FILE_INFO')?>
					</th>
					<th width="15%">
						<?=Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_RESTORE')?>
						<br>
						<input type="checkbox" onclick="ispro_set_checkboxs('input[name^=fileToRestore]', this.checked);" >
					</th>
					<th width="15%">
						<?=Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_DELETE')?>
						<br>
						<input type="checkbox" onclick="ispro_set_checkboxs('input[name^=fileToDeteteComplete]', this.checked);" >
					</th>
				</tr>

				<?
				foreach ($filelist as $file) {
					?>

					<tr>
						<td style="text-align: left; border-top: 1px solid #aaa" width="70%"  valign="top">
							<?$filelink = str_replace($doc_root, '', trim($file));?>
							<a href="<?=$filelink?>" target="_blank"><?=$filelink?></a>
							<?if (in_array(mb_substr($filelink, -4), array('.jpg', 'jpeg', '.png', 'webp', '.gif', '.bmp'))) :?>
								<br>
								<img style="max-width: 200px; max-height: 100px" src="<?=$filelink?>">
							<?endif?>
						</td>
						<td style="text-align: center; border-top: 1px solid #aaa;" valign="top">
							<input type="checkbox" name="fileToRestore[]" value="<?=trim($file)?>" title="Restore">
						</td>
						<td style="text-align: center; border-top: 1px solid #aaa;" valign="top">
							<input type="checkbox" name="fileToDeteteComplete[]" value="<?=trim($file)?>" title="Delete">
						</td>
					</tr>

					<?
				}
			}
		}?>


		<tr>
			<td colspan="3">
				<input type="submit" class="adm-btn-save" name="showbasket" value="<? echo Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_BTN_CONFIRM'); ?>">
			</td>
		</tr>

	<?} else {?>
		<tr>
			<td colspan="3">
				<input type="submit" class="adm-btn-save" name="showbasket" value="<? echo Loc::getMessage('ISPRO_FILESCLEANUP_OPTIONS_BTN_SHOWBASKET'); ?>">
			</td>
		</tr>
	<?}?>
	<?$tabControl->Buttons();?>
<?$tabControl->End();?>
</form>