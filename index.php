<?php
	header('Vary: Accept');
	header('Content-Type: text/html; charset=utf-8');
	date_default_timezone_set('Europe/Zurich');
	include('_db.php');
	include('_formutils.php');
	echo '<!DOCTYPE HTML><html><head><title>Rites funeraires</title>';
		echo '<link rel="stylesheet" type="text/css" media="screen" href="/s/screen.css" />';
		echo '<script type="text/javascript" src="/js/jquery.min.js"></script>';
		echo '<script type="text/javascript" src="/js/jquery.color.min.js"></script>';
		echo '<script type="text/javascript" src="/js/jquery.animate-shadow-min.js"></script>';
	echo '</head><body class="viewer">';


    // Page Content ================================================================================
    echo '<header>';
    	echo '<div id="controls">';
    		echo '<img src="/i/loading.gif" id="loading" alt="" /> ';
			echo '<img src="/i/mode_full.png" id="bMode" />';
			echo '<img src="/i/play.png" id="bPlay" alt="PLAY" />';
			echo '<img src="/i/pause.png" id="bPause" alt="PAUSE" />';
			echo '<img src="/i/ffw.png" id="bNext" alt="FFW" />';
		echo '</div>';
		beginForm('get', 'index.php');
			$talks = array('' => '(sélectionnez une conférence)');
			$r_t = db_s('talks', array(), array('title' => 'ASC'));
			while ($t = db_fetch($r_t)) {
				$talks[$t['dir']] = $t['title'].', '.$t['author'].' ('.datetime('d.m.Y', $t['date']).')';
			}
			printSelectInput('', 'dir', @$_REQUEST['dir'], $talks, 'form', true);
		endForm();
    // =============================================================================================
    echo '</header>';
    if (@$_REQUEST['dir']!='' && substr($_REQUEST['dir'], 0, 1)!='.') {
    	$c_talks = db_s('talks', array('dir' => $_REQUEST['dir']));
    	$talk = db_fetch($c_talks);
    	echo '<div id="wait"><h1>'.$talk['title'].'</h1><h2>'.$talk['author'].'</h2><a href="#0" class="vidPlay">▶</a><h3>'.implode('.', array_reverse(explode('-', $talk['date']))).'</h3></div>';
    	echo '<div id="overlay">';
    		echo '<img src="/i/close.png" class="close" alt="&times;" title="Close" width="22" height="22" />';
    		echo '<iframe></iframe>';
    	echo '</div>';
	   	echo '<div id="viz">';
    	$audioFiles = array();
		$content = db_s('sounds', array('dir' => $_REQUEST['dir']), array('id' => 'ASC'));
		$i=0;
		while ($sound = mysql_fetch_assoc($content)) {
			$track = array('snd' => $sound['id']);
			$track['pict'] = $sound['file'];
			$links = '';
			$e = preg_split('/\s/',$sound['entities']);
			foreach ($e as $entity) {
				if ($entity!='') {
					$links.= '<a href="'.$entity.'" class="entity"><img src="/i/link.png" alt="" /></a>';
				}
			}
			$track['link'] = $links;
/*
			if ($sound['file']!='') {
				echo '<a href="/tmp/'.$sound['file'].'" class="pict"><img src="/i/pict.png" width="16" height="16" alt="" /></a>';
			}
			if ($sound['entities']!='') {
				$e = preg_split('/\s/',$sound['entities']);
				foreach ($e as $entity) {
					echo '<a href="'.$entity.'" class="entity" target="_blank"><img src="/i/link.png" width="16" height="16" alt="" /></a>';
				}
			}
*/
			echo '<p class="'.$sound['type'].'"><a href="#'.$i.'" id="a'.$i.'" onclick="return playTrack('.$i.');">'.stripslashes($sound['text']).'</a></p>';
			$audioFiles[] = $track;
			$i++;
		}
		echo '</div>';
		echo '<div id="dia"><div id="diaPictFrame">';
			echo '<img id="diaPict" src="" />';
		echo '</div><div id="links"></div></div>';
		// _____________________________________
		echo '<div style="display:none;">';
			echo '<audio id="player" preload="preload" src="/data/'.$audioFiles[0]['snd'].'" onerror="alert(\'The sound file \\\'\'+this.src+\'\\\' could not be loaded.\');" onended="endedPlay();" onloadstart="document.getElementById(\'loading\').style.display=\'inline\';" oncanplay="document.getElementById(\'loading\').style.display=\'none\';" onplay="startedPlay();"><source src="/data/'.$audioFiles[0]['snd'].'" type="audio/mp3" />HTML5 Only!</audio>';
#			echo '<audio id="preloader" preload="preload" src="/data/'.$audioFiles[1]['snd'].'"><source src="/data/'.$audioFiles[1]['snd'].'" type="audio/mp3" />HTML5 Only!</audio>';
		echo '</div>';
    }

    // Load and init etalk modules
    printJS('var audioFiles = ('.json_encode($audioFiles).');');
	echo '<script type="text/javascript" src="/js/etalk.min.js"></script>';

    echo '</body></html>';

?>