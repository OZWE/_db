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


    // Page Content ============================================================================================================================================
    if (@$_REQUEST['dir']!='' && substr($_REQUEST['dir'], 0, 1)!='.') {
	    echo '<header>';
	    	echo '<nav><img src="/i/back.png" id="bBack" alt="Back" class="btn" title="Retour à l’accueil" /></nav>';
	    	echo '<nav id="controls">';
	    		echo '<img src="/i/loading.gif" id="loading" class="btn" alt="" /> ';
				echo '<img src="/i/audio_mute.png" id="bMute" class="btn" alt="MUTE" title="Activer/Couper le son" />';
				echo '<img src="/i/mode_full.png" id="bMode" class="btn" alt="Afficher/Masquer le transcript" />';
				echo '<img src="/i/play.png" id="bPlay" class="btn" alt="PLAY" />';
				echo '<img src="/i/pause.png" id="bPause" class="btn" alt="PAUSE" />';
				echo '<img src="/i/ffw.png" id="bNext" class="btn" alt="FFW" title="Suivant" />';
			echo '</nav>';
	    echo '</header>';
	    // _______________________________________________________________________________________________________________________________________
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
			$track = array(
							'snd' => $sound['id'],
							'pict' => $sound['file'],
							'pict_link' => $sound['file_link'],
							'pict_cred' => $sound['file_credits'],
							);
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
			if ($sound['chaptering']=='section') {
				echo '<h2>'.$sound['section_title'].'</h2>';
			}

			echo '<p class="'.$sound['type'].'"><a href="#'.$i.'" id="a'.$i.'" onclick="return playTrack('.$i.');">'.stripslashes($sound['text']).'</a></p>';
			$audioFiles[] = $track;
			$i++;
		}
		echo '</div>';
		echo '<div id="dia"><figure id="diaPictFrame">';
			echo '<img id="diaPict" src="" alt="" />';
			echo '<figcaption id="diaPictText"></figcaption>';
		echo '</figure><div id="links"></div></div>';
		// _____________________________________
		echo '<div style="display:none;">';
			echo '<audio id="player" preload="preload" src="/data/'.$audioFiles[0]['snd'].'" onerror="alert(\'The sound file \\\'\'+this.src+\'\\\' could not be loaded.\');" onended="endedPlay();" onloadstart="document.getElementById(\'loading\').style.display=\'inline\';" oncanplay="document.getElementById(\'loading\').style.display=\'none\';" onplay="startedPlay();"><source src="/data/'.$audioFiles[0]['snd'].'" type="audio/mp3" />HTML5 Only!</audio>';
#			echo '<audio id="preloader" preload="preload" src="/data/'.$audioFiles[1]['snd'].'"><source src="/data/'.$audioFiles[1]['snd'].'" type="audio/mp3" />HTML5 Only!</audio>';
		echo '</div>';

	    // Load and init etalk modules
	    printJS('var audioFiles = ('.json_encode($audioFiles).');');
		echo '<script type="text/javascript" src="/js/etalk.min.js"></script>';
    }
    else {
    	echo '<header>';
    		echo '<h1>eTalk</h1><h2>Open-source online talks</h2>';
		echo '</header>';

		echo '<section><nav>';
			$talks = array('' => '(sélectionnez une conférence)');
			$r_t = db_s('talks', array(), array('title' => 'ASC'));
			while ($t = db_fetch($r_t)) {
				echo '<a href="?dir='.$t['dir'].'"><figure><div class="play"></div></figure><h2>'.$t['title'].'</h2><p>'.$t['author'].' ('.datetime('d.m.Y', $t['date']).')</p></a>';
			}
		echo '</nav></section>';
    }

    echo '</body></html>';

?>